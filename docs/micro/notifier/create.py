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

# создаём exchange
channel.exchange_declare(
    exchange="otus_dialog",
    exchange_type="topic",
    durable=True
)

# очередь для WS-сервера
channel.queue_declare(queue="queue_ws_server_dialog", durable=True)
channel.queue_bind(exchange="otus_dialog", queue="queue_ws_server_dialog", routing_key="to_user.*")

# очередь для сервиса истории
channel.queue_declare(queue="queue_dialog_history", durable=True)
channel.queue_bind(exchange="otus_dialog", queue="queue_dialog_history", routing_key="to_user.*")
