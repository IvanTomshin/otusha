import os
import psycopg2
import json
import pika
import time
import redis

r = redis.Redis(host="redis", port=6379, db=0)

for i in range(10):
    try:
        r.ping()
        print("✅ Подключено к Redis")
        break
    except redis.exceptions.ConnectionError:
        print("⏳ Redis ещё не готов, повтор через 2с...")
        time.sleep(2)
else:
    raise Exception("❌ Не удалось подключиться к Redis")




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

# Подключение к RabbitMQ с ретраями
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

channel = mq_conn.channel()
channel.queue_declare(queue="queue_dialog_history", durable=True)

# Подключение к Postgres
conn = psycopg2.connect(**db_params)

def save_to_db(message_id, from_id, to_id, message):
    with conn.cursor() as cur:
        cur.execute(
            "INSERT INTO dialogs (message_id, from_id, to_id, dt, msg) VALUES (%s, %s, %s, NOW(), %s)",
            (message_id, from_id, to_id, message)
        )
        conn.commit()

def callback(ch, method, properties, body):
    event = json.loads(body)

    if event["event"] == "message_created":
        save_to_db(event["message_id"], event["from_user_id"], event["to_user_id"], event["text"])
        r.incr(f"unread_count:{event['to_user_id']}")
        print(f"[DB] Saved message {event['message_id']} from {event['from_user_id']} to {event['to_user_id']}")
        print(f"[Redis] Unread count +1 for {event['to_user_id']}")

    elif event["event"] == "message_read":
        r.decr(f"unread_count:{event['to_user_id']}")
        print(f"[Redis] Unread count -1 for {event['to_user_id']}")

    ch.basic_ack(delivery_tag=method.delivery_tag)

channel.basic_consume(
    queue="queue_dialog_history",
    on_message_callback=callback,
    auto_ack=False  # ручное подтверждение
)

print(" [*] Сервис истории ждёт сообщений...")
channel.start_consuming()
