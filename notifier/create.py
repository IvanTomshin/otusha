import os
import pika
import time

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

channel = mq_conn.channel()

# 1. Создаём exchange
channel.exchange_declare(
    exchange="otus_user_messages",
    exchange_type="topic",
    durable=True
)

# 2. Создаём очередь на сервер
channel.queue_declare(queue="queue_ws_server_1", durable=True)

# 3. Привязываем очередь к exchange
channel.queue_bind(
    exchange="otus_user_messages",
    queue="queue_ws_server_1",
    routing_key="user.*"  # получаем все user-сообщения
)