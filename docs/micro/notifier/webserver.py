import os
import select
import json
import uuid
import pika
import asyncio
import time
import websockets
import aio_pika
from aio_pika import connect_robust, IncomingMessage, RobustConnection
from aio_pika.exceptions import AMQPConnectionError

# Активные соединения: to_user_id -> websocket
active_connections = {}
exchange = None  # глобальная переменная под RabbitMQ exchange


# Считываем параметры подключения
mq_host = os.environ['MQ_HOST']
mq_user = os.environ['MQ_USER']
mq_pass = os.environ['MQ_PASS']

# Формируем AMQP URI
amqp_url = f"amqp://{mq_user}:{mq_pass}@{mq_host}/"

# Обработка входящих WebSocket-подключений
async def handler(websocket):
    try:
        # Первое сообщение — авторизация
        raw = await websocket.recv()
        data = json.loads(raw)
        from_user_id = str(data.get("from_user_id"))

        if not from_user_id:
            await websocket.send("Missing from_user_id")
            return

        print(f"✅ Подключен пользователь: {from_user_id}")
        active_connections[from_user_id] = websocket

        # Теперь слушаем все последующие сообщения от клиента
        async for raw in websocket:
            data = json.loads(raw)
            to_user_id = str(data.get("to_user_id"))
            text = data.get("text")

            if not to_user_id:
                continue

            if not text:
                msg_type="message_read"
            else:
                msg_type="message_created"

            payload = {
                "event": msg_type,
                "message_id": str(uuid.uuid4()),   # уникальный id для каждого сообщения
                "from_user_id": from_user_id,
                "to_user_id": to_user_id,
                "text": data.get("text", "")
            }

            await exchange.publish(
                aio_pika.Message(body=json.dumps(payload).encode()),
                routing_key=f"to_user.{to_user_id}"
            )

            print(f"📤 Сообщение от {from_user_id} -> {to_user_id}: {payload}")

    except websockets.exceptions.ConnectionClosed:
        print(f"❌ Отключен: {from_user_id}")
    finally:
        active_connections.pop(from_user_id, None)



# Функция с повторами подключения
async def connect_with_retries(retries=5, delay=2, backoff=2) -> RobustConnection:
    global exchange
    attempt = 0
    while attempt < retries:
        try:
            print(f"🔄 Попытка подключения к RabbitMQ (попытка {attempt + 1}/{retries})...")
            connection = await connect_robust(amqp_url)
            if not connection.is_closed:
                print("✅ Успешное подключение к RabbitMQ")
                # Канал к RabbitMQ для публикации
                channel = await connection.channel()
                exchange = await channel.declare_exchange("otus_dialog", durable=True, type="topic")
                return connection
        except AMQPConnectionError as e:
            print(f"❌ Ошибка подключения: {e}")
        attempt += 1
        await asyncio.sleep(delay)
        delay *= backoff  # увеличиваем задержку (exponential backoff)

    raise Exception("❌ Не удалось подключиться к RabbitMQ после всех попыток")







# Подписка на очередь RabbitMQ
async def consume_rabbit():
    connection = await connect_with_retries()
    channel = await connection.channel()
    print("✅ Канал создан")
    queue = await channel.declare_queue("queue_ws_server_dialog", durable=True)
    print(f"📥 Очередь подключена: {queue.name}")

    async with queue.iterator() as queue_iter:
        async for message in queue_iter:
            async with message.process():
                try:
                    payload = json.loads(message.body.decode())
                    from_user_id = str(payload.get("from_user_id"))
                    to_user_id = str(payload.get("to_user_id"))
                    if to_user_id in active_connections:
                        await active_connections[to_user_id].send(json.dumps(payload))
                        print(f"📨 Отправлено от {from_user_id} пользователю {to_user_id} : {payload}")
                    else:
                        print(f"⏳ Пользователь {to_user_id} не в сети.")
                except Exception as e:
                    print(f"⚠️ Ошибка обработки сообщения: {e}")

# Основной запуск
async def main():
    print("🚀 Запуск WebSocket-сервера на порту 8765")
    
    # await serve() чтобы получить готовый сервер
    ws_server = await websockets.serve(handler, "0.0.0.0", 8765)

    await connect_with_retries()

    # Ждём обе задачи: RabbitMQ и открытый WebSocket
    await asyncio.gather(
        consume_rabbit(),
        ws_server.wait_closed()  # жди, пока сервер не закроется
    )

if __name__ == "__main__":
    asyncio.run(main())

