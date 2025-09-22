<?php
// metrics.php

// Настройка Redis
$redisHost = 'redis';
$redisPort = 6379;
$redisQueue = 'packets';

// Метрики RED
static $requestCount = 0;
static $errorCount = 0;

// Засекаем время обработки
$startTime = microtime(true);

$requestCount++;

$queueSize = 0;
try {
    $redis = new Redis();
    $redis->connect($redisHost, $redisPort, 1.5); // таймаут 1.5s
    $queueSize = $redis->lLen($redisQueue);
} catch (Exception $e) {
    $errorCount++;
    $queueSize = 0;
}

// Считаем время выполнения
$duration = microtime(true) - $startTime;

// Отдаём в формате Prometheus
header('Content-Type: text/plain; charset=utf-8');

// Бизнес-метрика
echo "# HELP chat_queue_length Количество сообщений в очереди Redis\n";
echo "# TYPE chat_queue_length gauge\n";
echo "redis_queue_length {$queueSize}\n\n";

// RED-метрики
echo "# HELP http_requests_total Общее количество HTTP-запросов к /metrics\n";
echo "# TYPE http_requests_total counter\n";
echo "http_requests_total {$requestCount}\n\n";

echo "# HELP http_request_errors_total Количество ошибок при обработке /metrics\n";
echo "# TYPE http_request_errors_total counter\n";
echo "http_request_errors_total {$errorCount}\n\n";

echo "# HELP http_request_duration_seconds Время обработки /metrics (в секундах)\n";
echo "# TYPE http_request_duration_seconds gauge\n";
echo "http_request_duration_seconds {$duration}\n";
