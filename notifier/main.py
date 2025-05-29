import os
import psycopg2
import select
import json
import pika
import asyncio
import time

db_params = {
    'dbname': os.environ['DB_NAME'],
    'user': os.environ['DB_USER'],
    'password': os.environ['DB_PASS'],
    'host': os.environ['DB_HOST']
}

mq_params = pika.ConnectionParameters(
    host=os.environ['MQ_HOST'],
    credentials=pika.PlainCredentials(
        os.environ['MQ_USER'],
        os.environ['MQ_PASS']
    )
)

retries = 5
for i in range(retries):
    try:
        mq_conn = pika.BlockingConnection(mq_params)
        print("✅ Подключено к RabbitMQ")
        break
    except pika.exceptions.AMQPConnectionError as e:
        print(f"⏳ Попытка подключения к RabbitMQ #{i+1} не удалась: {e}")
        time.sleep(3)
else:
    print("❌ Не удалось подключиться к RabbitMQ после нескольких попыток.")
    exit(1)

mq_channel = mq_conn.channel()
mq_channel.queue_declare(queue='events')

conn = psycopg2.connect(**db_params)

def listen_notifications():
    conn.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
    cursor = conn.cursor()
    cursor.execute("LISTEN post_changed;")
    print("✅ Подключение к каналу уведомлений установлено.")

    while True:
        if select.select([conn], [], [], 1) == ([], [], []):
            print("⌛ Ожидание уведомлений...")
            continue
        conn.poll()
        while conn.notifies:
            notify = conn.notifies.pop(0)
            print(f"-> Уведомление: {notify.payload}")
            try:
              payloads = json.loads(notify.payload)
              #Если payload — список объектов
              if isinstance(payloads, list):
                for item in payloads:
                    user_id = item.get("friend_id")
                    mq_channel.basic_publish(
                        exchange='otus_user_messages',
                        routing_key=f'user.{user_id}',  # динамически вставляем user_id
                        body=json.dumps(item)
                    )
                    print(f"📤 Отправлено {user_id} в MQ: {item}")
              else:
                #если это одиночный JSON
                user_id = payloads.get("friend_id")
                mq_channel.basic_publish(
                    exchange='otus_user_messages',
                    routing_key=f'user.{user_id}',  # динамически вставляем user_id
                    body=json.dumps(payloads)
                )
                print(f"📤 Отправлено в MQ: {payloads}")
            except json.JSONDecodeError as e:
              print(f"❌ Ошибка декодирования JSON: {e}")


# Асинхронная обертка
async def main():
    loop = asyncio.get_event_loop()
    loop.run_in_executor(None, listen_notifications)


# Запуск программы
asyncio.run(main())
