<?php
require_once __DIR__ . '/vendor/autoload.php'; // Подключаем библиотеку php-amqplib

// Параметры подключения к RabbitMQ
$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Объявляем очередь
$queueName = 'file_queue';
$channel->queue_declare($queueName, false, false, false, false);

// Получаем имя файла из командной строки
if ($argc < 2) {
    die("Usage: php send_message.php <file_path>\n");
}

$filePath = $argv[1];
$fileContents = file_get_contents($filePath);

// Отправляем сообщение с содержимым файла

$msg = new \PhpAmqpLib\Message\AMQPMessage($fileContents, ['reply_to' => 'response_queue']);
$channel->basic_publish($msg, '', $queueName);
// ...


echo " [x] Sent '{$filePath}'\n";

// Ожидаем ответа от другой программы
$channel->close();
$connection->close();
