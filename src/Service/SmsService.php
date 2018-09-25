<?php
declare(strict_types=1);

/**
 * /src/Service/SMSService.php
 *
 * @author     ane, Antti Nevala <antti.nevala@protacon.com>
 * @copyright  2018 Protacon Solutions <http://www.protacon.com/>
 */

namespace App\Service;

use Libs\Labyrintti\GwClient;
use Libs\Labyrintti\GwClientConfig;
use Libs\Labyrintti\SmsMessage;

/**
 *  SMSService -class
 *
 * Handles sms sending
 *
 * @package App\Service;
 *
 * @author     ane, Antti Nevala <antti.nevala@protacon.com>
 * @copyright  2018 Protacon Solutions <http://www.protacon.com/>
 *
 */
class SMSService
{
    /**
     * @var GwClientConfig
     */
    protected $gwClientConfig;
    
    /**
     * SMSService constructor.
     *
     * @param string $user      Labyrintti/Link mobility user
     * @param string $password  Labyrintti/Link mobility password
     */
    public function __construct(/*string $user, string $password*/)
    {
        // $this->gwClientConfig = new GwClientConfig($user, $password);
    }
    
    /**
     * Send sms message
     *
     * @param string $sender    Sender phone number
     * @param string $receiver  Receiver phone number
     * @param string $message
     * @param string $clientApp   Application from which the message was sent from
     *
     * @throws \Exception
     * @throws \Libs\Labyrintti\AccessDeniedException
     * @throws \Libs\Labyrintti\ConnectionFailedException
     * @throws \Libs\Labyrintti\GwConfigException
     * @throws \Libs\Labyrintti\InvalidMmsObjectException
     * @throws \Libs\Labyrintti\InvalidParametersException
     * @throws \Libs\Labyrintti\MessageContentException
     */
    public function send(string $sender, string $receiver, string $message, string $clientApp): void
    {
        // $client = new GwClient($this->gwClientConfig);

        $smsMessage = new SmsMessage($this->stripPhoneNumber($receiver), $message);
        $smsMessage->setTag($clientApp);
        $smsMessage->setSender($this->stripPhoneNumber($sender));
        
        // $client->send($smsMessage);
    }
    
    /**
     * Strip spaces & other special characters from number
     * and set it to format (+)111 ... 111
     *
     * Trims numbers in parentheses if country locale provided:
     *
     *      e.g. +358 (0) 12 345 6789 -> +358123456789
     *
     * @param $phonenumber
     * @return string
     */
    private function stripPhoneNumber($phonenumber): string
    {
        $phonenumber = \trim($phonenumber);
        return preg_replace('/(?!^\+)((?!^)(\(.\))|[^0-9])/', '', $phonenumber);
    }
}