<?php
namespace App\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Service\SmsService;

/**
 * Class MessageConsumer
 *
 * MessageConsumer listens to defined queue and processes messages from it.
 */
class MessageConsumer {

    /**
     * @var SMSService
     */
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SMSService();
    }

    /**
     * Method listens new messages from MQ exchange 'messages', 
     * which have been routed with 'sms.#'
     */
    public function listen(): void {

        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();

        $channel->exchange_declare(
            'messages',
            'topic',
            false,
            true,
            false
        );
        
        list($queueName, ,) = $channel->queue_declare("", false, false, true, false);

        $bindingKey = 'sms.#';

        $channel->queue_bind($queueName, 'messages', $bindingKey);

        echo ' [*] Waiting for messages. CTRL-C to exit.' . "\n";

        $channel->basic_consume(
            $queueName,
            '',
            false,
            true,
            false,
            false,
            array($this, 'processSmsQueueMessage')
        );

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
    

    /**
     * Callback method to process SMS-messages from queue
     *
     * @param $message
     */
    public function processSmsQueueMessage($message): void {
        // Decode json and set needed parameters for SMSService
        $decodedMsg = json_decode($message->body);

        $sender = $decodedMsg->sender;
        $receiver = $decodedMsg->receiver;
        $content = $decodedMsg->content;
        $clientApp = $decodedMsg->clientApp;

        $this->smsService->send($sender, $receiver, $content, $clientApp);

        $datetime = new \DateTime('now');
        $dtString = $datetime->format('Y-m-d H:i:s');
        $log = $dtString . " *** Sent message to " . $receiver;
        $myfile = file_put_contents('/home/vagrant/site/logs/process_sms_log.txt', $log.PHP_EOL , FILE_APPEND);
    }

}


