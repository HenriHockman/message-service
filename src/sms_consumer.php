#!/usr/bin/env php
<?php
require_once dirname(__DIR__) . '/conf/mq-conf.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\RabbitMQ\MessageConsumer;

$consumer = new MessageConsumer();
$consumer->listen();
