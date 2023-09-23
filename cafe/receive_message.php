<?php
require_once __DIR__ . '/vendor/autoload.php'; // Подключаем библиотеку php-amqplib

// Параметры подключения к RabbitMQ
$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Объявляем очередь
$queueName = 'file_queue';
$channel->queue_declare($queueName, false, false, false, false);

echo " Для выхода Ctrl+C\n";

// ...
$callback = function ($msg) {
    $fileContents = $msg->body;
    $fileName = 'received_file.txt';

    // Записываем содержимое в файл
    file_put_contents($fileName, $fileContents);

    echo " Сообщение получено и сохранено в '{$fileName}'\n";

    // Отправляем ответ
    $responseMsg = new \PhpAmqpLib\Message\AMQPMessage('Success');
    $msg->delivery_info['channel']->basic_publish($responseMsg, '', $msg->get('reply_to'));

    // Выводим содержимое файла в консоль
    $fileContents = file_get_contents($fileName);
    echo "File contents: \n";
    echo $fileContents;
};

$channel->basic_consume($queueName, '', false, true, false, false, $callback);
// ...


while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
