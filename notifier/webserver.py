import os
import select
import json
import pika
import asyncio
import time
import websockets
from aio_pika import connect_robust, IncomingMessage, RobustConnection
from aio_pika.exceptions import AMQPConnectionError

# Активные соединения: user_id -> websocket
active_connections = {}

# Считываем параметры подключения
mq_host = os.environ['MQ_HOST']
mq_user = os.environ['MQ_USER']
mq_pass = os.environ['MQ_PASS']

# Формируем AMQP URI
amqp_url = f"amqp://{mq_user}:{mq_pass}@{mq_host}/"

# Обработка входящих WebSocket-подключений
async def handler(websocket):
    try:
        # Клиент должен первым делом прислать свой user_id
        raw = await websocket.recv()
        data = json.loads(raw)
        user_id = str(data.get("user_id"))
        if not user_id:
            await websocket.send("Missing user_id")
            return

        print(f"✅ Подключен пользователь: {user_id}")
        active_connections[user_id] = websocket

        # Поддерживаем соединение
        while True:
            await websocket.recv()  # не даём соединению упасть

    except websockets.exceptions.ConnectionClosed:
        print(f"❌ Отключен: {user_id}")
    finally:
        active_connections.pop(user_id, None)



# Функция с повторами подключения
async def connect_with_retries(retries=5, delay=2, backoff=2) -> RobustConnection:
    attempt = 0
    while attempt < retries:
        try:
            print(f"🔄 Попытка подключения к RabbitMQ (попытка {attempt + 1}/{retries})...")
            connection = await connect_robust(amqp_url)
            if not connection.is_closed:
                print("✅ Успешное подключение к RabbitMQ")
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
    queue = await channel.declare_queue("queue_ws_server_1", durable=True)
    print(f"📥 Очередь подключена: {queue.name}")

    async with queue.iterator() as queue_iter:
        async for message in queue_iter:
            async with message.process():
                try:
                    payload = json.loads(message.body.decode())
                    user_id = str(payload.get("friend_id"))
                    if user_id in active_connections:
                        await active_connections[user_id].send(json.dumps(payload))
                        print(f"📨 Отправлено пользователю {user_id}: {payload}")
                    else:
                        print(f"⏳ Пользователь {user_id} не в сети.")
                except Exception as e:
                    print(f"⚠️ Ошибка обработки сообщения: {e}")

# Основной запуск
async def main():
    print("🚀 Запуск WebSocket-сервера на порту 8765")
    
    # await serve() чтобы получить готовый сервер
    ws_server = await websockets.serve(handler, "0.0.0.0", 8765)

    # Ждём обе задачи: RabbitMQ и открытый WebSocket
    await asyncio.gather(
        consume_rabbit(),
        ws_server.wait_closed()  # жди, пока сервер не закроется
    )

if __name__ == "__main__":
    asyncio.run(main())

