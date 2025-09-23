```mermaid
sequenceDiagram
    participant User
    participant MessagingService as Messaging Service
    participant RabbitMQ
    participant CountersService as Counters Service
    participant Redis
    participant ReconciliationJob as Reconciliation Job

    User->>MessagingService: Отправка нового сообщения
    MessagingService->>DB: Сохранить сообщение (status=unread)
    MessagingService->>RabbitMQ: Публикация message.created
    RabbitMQ->>CountersService: Доставка события
    CountersService->>Redis: INCR unread_count:{user_id}

    User->>MessagingService: Пометить сообщение как прочитанное
    MessagingService->>DB: Обновить статус message=read
    MessagingService->>RabbitMQ: Публикация message.read
    RabbitMQ->>CountersService: Доставка события
    CountersService->>Redis: DECR unread_count:{user_id}

    note over CountersService, Redis: Возможны рассинхронизации

    ReconciliationJob->>DB: Считать актуальное число непрочитанных
    ReconciliationJob->>Redis: Сравнить с Redis
    alt Redis != DB
        ReconciliationJob->>Redis: Исправить значение
    end
