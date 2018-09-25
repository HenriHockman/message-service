<?php
/*
 * Copyright (c) 2016 Labyrintti Media Ltd. All rights reserved.
 */
namespace Libs\Labyrintti;
/**
 * @file lmgw-api.php
 * Labyrintti Media SMS/MMS Gateway PHP API.
 *
 * @version 2.2
 */
 
/**
 * @mainpage Introduction
 *
 * @image html gateway.png
 *
 * @section intro_gateway SMS/MMS Gateway
 * Labyrintti Media SMS/MMS Gateway provides application developers a simple
 * interface for developing SMS and MMS services and sending messages, thus
 * allowing developers to concentrate on the core application. Applications
 * communicate with SMS Gateway using the standard HTTP protocol. This allows
 * for applications to be written in almost any programming language like
 * C/C++, Java, PHP, ASP.net, Perl, etc. Usually service applications work on
 * top of a web server software that already contains the required HTTP and CGI
 * functionality.
 *
 * This document describes the data structures of the Labyrintti SMS/MMS
 * Gateway PHP API and provides simple usage examples for sending and receiving
 * messages. Labyrintti Media also provides customers with Java API for sending
 * and receiving SMS messages.
 *
 * @image latex gateway.png "Labyrintti SMS/MMS Gateway."
 * 
 * @section intro_req Requirements
 * This API is designed to work with PHP 5 (>= 5.1.0). It works with default
 * PHP setup, any non-default extensions are not required. HTTPS support
 * requires OpenSSL extension.
 */

/**
 * @page version_history Version History
 * 
 * @b 2.0 (Sep 13, 2010)
 * @li Initial release of completely new object-oriented PHP API. Not
 *   compatible with PHP API version 1
 * 
 * @b 2.1 (Apr 26, 2011)
 * @li Fixed methods GwServer::receiveSms and GwServer::receiveMms
 *   throwing a PHP notice if one of the optional parameters in the
 *   Gateway request were not set
 * @li Methods GwServer::receiveSms, GwServer::receiveMms and
 *   GwServer::receiveReport now throw an Exception if request is
 *   invalid (wrong type)
 * @li Implemented possibility to respond to Gateway without response
 *   message with methods GwServer::respondSms and GwServer::respondMms
 *   (response message can be omitted only if a separate service number
 *   with mobile-originated billing is used)
 * 
 * @b 2.1.1 (Aug 22, 2011)
 * @li Implemented method GwClient::monitor for sending availability
 *   monitoring request to Gateway. Method returns MonitorResult object
 *   which includes Gateway's response time
 * @li Implemented methods Message::getTimestamp and
 *   DeliveryReport::getTimestamp for checking date and time of message
 *   arrival at operator message center
 * @li Inherited several API specific exception classes for more
 *   advanced exception handling
 * @li Improved GwClient performance
 * 
 * @b 2.1.2 (Sep 13, 2011)
 * @li PHP backward compatibility was extended from version 5.3.0 to 5.1.0 by
 *   abandoning exception chaining
 * 
 * @b 2.1.3 (Feb 8, 2012)
 * @li Fixed method SendResult::isSent always returning true
 * 
 * @b 2.1.4 (Sep 11, 2012)
 * @li Implemented methods GwClientConfig::setConnectTimeout and
 *   GwClientConfig::getConnectTimeout for setting and getting GwClient's
 *   connect timeout
 * @li Implemented methods GwClientConfig::setSendTimeout and
 *   GwClientConfig::getSendTimeout for setting and getting GwClient's send
 *   timeout
 * 
 * @b 2.1.5 (Jul 5, 2013)
 * @li Improved support for PHP 5.4
 * 
 * @b 2.2 (Jun 1, 2016)
 * @li Support for custom message tags, Message::setTag
 */

/**
 * @page usage Usage Examples
 *
 * @section usage_sms SMS Gateway
 *
 * @subsection usage_sms_one_way 1-way SMS: Sending SMS messages to mobile phones
 * @code
 * <?php
 * include "/my/path/lmgw-api.php";
 *
 * try {
 *     $client = new GwClient(new GwClientConfig("username", "password"));
 *
 *     $results = $client->send(new SmsMessage("0401234567", "Hello World!"));
 *
 *     if ($results[0]->isSent()) {
 *         echo "SMS message sent!";
 *     } else {
 *         echo "Error sending SMS message: " . $results[0]->getDescription();
 *     }
 * } catch (GwException $e) {
 *     echo "Error sending SMS message: " . $e->getMessage();
 * }
 * ?>
 * @endcode
 *
 * @subsection usage_sms_two_way 2-way SMS: Receiving SMS messages from mobile phones
 * @code
 * <?php
 * include "/my/path/lmgw-api.php";
 *
 * try {
 *     $server = new GwServer();
 *     $message = $server->receiveSms();
 *
 *     $server->respondSms(new SmsMessage(null, "Hello World!"));
 * } catch (GwException $e) {
 *     // log exception
 * }
 * ?>
 * @endcode
 *
 * @section usage_mms MMS Gateway
 *
 * @subsection usage_mms_one_way 1-way MMS: Sending MMS messages to mobile phones
 * @code
 * <?php
 * include "/my/path/lmgw-api.php";
 *
 * try {
 *     $client = new GwClient(new GwClientConfig("username", "password"));
 *
 *     $image = new MmsObject();
 *     $image->setFileContent("/my/path/image.jpg");
 *     
 *     $message = new MmsMessage("0401234567", "Hello World!");
 *     $message->addObject($image);
 *
 *     $results = $client->send($message);
 *
 *     if ($results[0]->isSent()) {
 *         echo "MMS message sent!";
 *     } else {
 *         echo "Error sending MMS message: " . $results[0]->getDescription();
 *     }
 * } catch (GwException $e) {
 *     echo "Error sending MMS message: " . $e->getMessage();
 * }
 * ?>
 * @endcode
 *
 * @subsection usage_mms_two_way 2-way MMS: Receiving MMS messages from mobile phones
 * @code
 * <?php
 * include "/my/path/lmgw-api.php";
 *
 * try {
 *     $server = new GwServer();
 *     $message = $server->receiveMms();
 *
 *     $audio = new MmsObject();
 *     $audio->setFileContent("/my/path/audio.mp3");
 *
 *     $response = new MmsMessage(null, "What A Wonderful Hello World!");
 *     $response->addObject($audio);
 *
 *     $server->respondMms($response);
 * } catch (GwException $e) {
 *     // log exception
 * }
 * ?>
 * @endcode
 *
 * @section usage_reports Delivery Reports
 *
 * @subsection usage_report_receiving Receiving delivery reports from Gateway
 * @code
 * <?php
 * include "/my/path/lmgw-api.php";
 *
 * try {
 *     $server = new GwServer();
 *     $report = $server->receiveReport();
 *
 *     $server->respondReport();
 * } catch (GwException $e) {
 *     // log exception
 * }
 * ?>
 * @endcode
 */

/**
 * @page trouble Troubleshooting
 *
 * @section trouble_one_way 1-way message sending
 * If message sending fails, check that you have specified correct user name
 * and password in GwClientConfig class, and that you have requested Labyrintti
 * Media to open the IP address of the server where your copy of lmgw-api.php
 * resides. If unsure, verify the IP address you are using. It is a common
 * mistake to try to send messages from an unallowed IP address like a
 * broadband connection with a dynamic IP address.
 *
 * @section trouble_two_way 2-way services
 * If Labyrintti SMS/MMS Gateway cannot successfully deliver an incoming
 * message to your service, it will attempt three (3) times by default. If your
 * service receives every incoming message three times, there is probably
 * something wrong with it.
 *
 * Note also that usually your service has to send a response message,
 * otherwise end-users will not be billed. Response message can be left out
 * only if a separate service number with mobile originated billing is used;
 * consult Labyrintti Media for more information.
 */

/**
 * @page contact Contact Information
 * If you require assistance, you can contact Labyrintti Media technical support.
 *
 * @section contact_support Technical Support
 * E-mail: <a href="mailto:support@labyrintti.com">support@labyrintti.com</a><br />
 * Phone: +358 10 440 1011
 */

use Exception;

/**
 * API base exception.
 */
class GwException extends Exception {
    
    /**
     * Constructs exception with the given message and code.
     * 
     * @param string    $message    the exception message
     * @param int       $code       the exception code
     */
    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }
    
}

/**
 * Indicates that a Gateway configuration did not pass the validation.
 * 
 * @see GwClientConfig
 * @see GwServerConfig
 */
class GwConfigException extends GwException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message, 0);
    }
    
}

/**
 * Base class for GwClient message sending exceptions.
 * 
 * This can be caused by any of the following reasons:
 * <ul>
 *   <li>Gateway cannot be connected</li>
 *   <li>Gateway refuses to send the message</li>
 *   <li>some mandatory send parameters are missing</li>
 * </ul>
 * 
 * Gateway can refuse to send messages for the following reasons:
 * <ul>
 *   <li>access is denied (wrong user name/password, denied IP address,
 *     or HTTP(S) sending not allowed)</li>
 *   <li>some message parameters are illegal (too long, contain invalid
 *     characters, or denied by Gateway account settings)</li>
 * </ul>
 */
class GwClientException extends GwException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message, 0);
    }
    
}

/**
 * Indicates that message sending failed because access to the Gateway was
 * denied.
 * 
 * This can be caused by:
 * <ul>
 *   <li>wrong user name</li>
 *   <li>wrong password</li>
 *   <li>disallowed IP address (allowed addresses are configured in Gateway
 *     account settings)</li>
 *   <li>unsecure HTTP sending not allowed</li>
 *   <li>secure HTTPS sending not allowed</li>
 * </ul>
 * 
 * @see GwClient
 */
class AccessDeniedException extends GwClientException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message);
    }
    
}

/**
 * Indicates that message sending failed because Gateway could not be
 * connected.
 * 
 * This can be caused by any kind of network/transport error.
 * 
 * @see GwClient
 */
class ConnectionFailedException extends GwClientException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message);
    }
    
}

/**
 * Indicates that message sending failed because one or more of message
 * parameters were invalid.
 * 
 * This can be caused by:
 * <ul>
 *   <li>required parameter missing</li>
 *   <li>invalid combination of parameters</li>
 *   <li>too long parameter value</li>
 *   <li>parameter value with invalid characters</li>
 *   <li>parameter value denied by Gateway account settings</li>
 * </ul>
 *
 * @see GwClient
 */
class InvalidParametersException extends GwClientException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message);
    }
    
}

/**
 * @internal
 * Exception containing HTTP status code and status message.
 */
class GwHttpException extends GwException {
    
    /**
     * Constructs exception with the given message and code.
     * 
     * @param string    $message    the exception message
     * @param int       $code       the exception code (HTTP status code)
     */
    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }
    
}

/**
 * GwServer exception indicating of a server exception.
 *
 * @see GwServer
 */
class GwServerException extends GwException {

    /** @internal Default HTTP status code. */
    const DEFAULT_HTTP_STATUS_CODE = 500;
    
    /**
     * HTTP status code.
     * @var int $httpStatusCode
     */
    private $httpStatusCode;
    
    /**
     * Constructs exception with the given HTTP status code and message.
     * 
     * @param int       $httpStatusCode HTTP status code
     * @param string    $message        the exception message
     */
    public function __construct($httpStatusCode, $message) {
        parent::__construct($message, 0);
        
        if (!is_null($httpStatusCode)) {
            $this->httpStatusCode = $httpStatusCode;
        } else {
            $this->httpStatusCode = self::DEFAULT_HTTP_STATUS_CODE;
        }
    }
    
    /**
     * Returns HTTP status code.
     * 
     * @return int
     */
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }

}

/**
 * Indicates that a message did not pass the validation.
 * 
 * @see SmsMessage
 * @see MmsMessage
 */
class MessageContentException extends GwException {
    
    /**
     * Constructs exception with the given message.
     * 
     * @param string    $message    the exception message
     */
    public function __construct($message) {
        parent::__construct($message, 0);
    }
    
}

/**
 * Indicates that message sending/responding to a message failed because content
 * file of an MmsObject was not found or it had no content assigned at all.
 *
 * @see MmsObject
 */
class InvalidMmsObjectException extends MessageContentException {
    
    /**
     * Source MMS object.
     * @var MmsObject $object
     */
    private $object;
    
    /**
     * Constructs exception with the given MMS object and message.
     * 
     * @param MmsObject $source     the source MMS object
     * @param string    $message    the exception message
     */
    public function __construct($source, $message) {
        parent::__construct($message);
        $this->object = $source;
    }
    
    /**
     * Returns the source MMS object.
     * 
     * @return MmsObject
     */
    public function getObject() {
        return $this->object;
    }
    
}

/**
 * @internal
 * Chunk writer.
 */
class ChunkWriter {

    /** Carriage return line feed. */
    const CRLF          = "\r\n";

    /** Carriage return line feed length. */
    const CRLF_LENGTH   = 2;

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Writes end chunk to resource.
     *
     * @param resource $resource    resource to write to
     */
    public static function writeEndChunkToResource($resource) {
        $chunk = dechex(0) . self::CRLF . self::CRLF;
        fwrite($resource, $chunk, strlen($chunk));
    }

    /**
     * Writes multipart object chunk to resource.
     *
     * @param resource  $resource   resource to write to
     * @param string    $header     multipart header
     * @param MmsObject $object     MMS object
     */
    public static function writeMultipartObjectChunkToResource($resource, $header, $object) {
        // count header length and chunk length
        $headerLength = strlen($header);
        $chunkLength = ($headerLength + $object->getContentLength() + self::CRLF_LENGTH);

        // write chunk length line and headers to resource
        $chunkLengthLine = dechex($chunkLength) . self::CRLF;
        fwrite($resource, $chunkLengthLine, strlen($chunkLengthLine));
        fwrite($resource, $header, $headerLength);

        // write object data to resource
        if ($object->isFile()) {
            $in = fopen($object->getContent(), "rb");
            while (!feof($in)) {
                fwrite($resource, fread($in, 8192));
            }
            fclose($in);
        } else {
            fwrite($resource, $object->getContent(), $object->getContentLength());
        }

        // write multipart ending CRLF and chunk ending CRLF to resource
        fwrite($resource, self::CRLF, self::CRLF_LENGTH);
        fwrite($resource, self::CRLF, self::CRLF_LENGTH);
    }

    /**
     * Writes text chunk to resource.
     *
     * @param resource  $resource   resource to write to
     * @param string    $text       text to write
     */
    public static function writeTextChunkToResource($resource, $text) {
        $chunk = dechex(strlen($text)) . self::CRLF . $text . self::CRLF;
        fwrite($resource, $chunk, strlen($chunk));
    }

}

/**
 * @internal
 * Configuration utilities.
 */
class ConfigUtils {

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Returns availability monitoring parameter array from the given client
     * configuration.
     *
     * @param GwClientConfig    $config     client configuration
     *
     * @return array
     */
    public static function getMonitorParameters($config) {
        $params = array(
            'user'      => $config->getUser(),
            'password'  => $config->getPassword()
        );

        return array_filter($params, "strlen");
    }
    
    /**
     * Returns response parameter array from the given server configuration.
     * 
     * @param GwServerConfig    $config     server configuration
     * @param Message           $message    SmsMessage or MmsMessage
     * 
     * @return array
     */
    public static function getResponseParameters($config, $message) {
        $params = array(
            'report'    => (
                $config->getReportUrl() ?
                self::createIdentifiableReportUrl($config->getReportUrl(), $message->getId()) :
                null
            )
        );

        return array_filter($params, "strlen");
    }

    /**
     * Returns send parameter array from the given client configuration.
     *
     * @param GwClientConfig    $config     client configuration
     * @param Message           $message    SmsMessage or MmsMessage
     *
     * @return array
     */
    public static function getSendParameters($config, $message) {
        $params = array(
            'user'      => $config->getUser(),
            'password'  => $config->getPassword(),
            'report'    => (
                $config->getReportUrl() ?
                self::createIdentifiableReportUrl($config->getReportUrl(), $message->getId()) :
                null
            )
        );

        return array_filter($params, "strlen");
    }

    /**
     * Creates identifiable report URL by adding the given message ID in the
     * end of the given report URL.
     *
     * @param string $reportUrl  user defined report URL
     * @param string $messageId  message ID
     *
     * @return string
     */
    private static function createIdentifiableReportUrl($reportUrl, $messageId) {
        $delimiter = (!strpos($reportUrl, "?") ? "?" : "&");
        $reportUrl .= $delimiter . Message::REPORT_PARAM_MESSAGE_ID . "=" . $messageId;

        return $reportUrl;
    }

}

/**
 * Contains message delivery status report for one recipient.
 */
class DeliveryReport {

    /**
     * Description of the delivery error.
     * @var string $description
     */
    private $description;

    /**
     * Detailed delivery error.
     * @var int $error
     */
    private $error;

    /**
     * Unique message ID.
     * @var string $messageId
     */
    private $messageId;

    /**
     * Recipient phone number exactly in the same format it was originally specified.
     * @var string $originalRecipient
     */
    private $originalRecipient;

    /**
     * Recipient phone number.
     * @var string $recipient
     */
    private $recipient;

    /**
     * Delivery state.
     * @var string $state
     */
    private $state;
    
    /**
     * Date and time when message was received by operator message center.
     * @var string $timestamp
     */
    private $timestamp;

    /**
     * @internal
     * Constructs a delivery report.
     *
     * @param string    $recipient          formatted recipient phone number
     * @param string    $originalRecipient  unformatted recipient phone number
     * @param string    $state              delivery state
     * @param int       $error              detailed delivery error
     * @param string    $description        description of the delivery error
     * @param string    $timestamp          date and time when message was
     *                                      received by operator message center
     * @param string    $messageId          unique message id
     */
    public function __construct($recipient, $originalRecipient, $state, $error,
        $description, $timestamp, $messageId) {
        $this->recipient = $recipient;
        $this->originalRecipient = $originalRecipient;
        $this->state = $state;
        $this->error = $error;
        $this->description = $description;
        $this->timestamp = $timestamp;
        $this->messageId = $messageId;
    }

    /**
     * Returns a string representation of this delivery report.
     * 
     * @return string
     */
    public function __toString() {
        $str = $this->timestamp . " " . $this->recipient . " " . $this->state;
        if ($this->state != "OK") {
            $str .= " " . $this->error;
        }
        $str .= " " . $this->description;
        
        return $str;
    }
    
    /**
     * Returns description text of the delivery status.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Returns reason for a temporarily or permanently failed delivery.
     *
     * <p><b>Return value is one of the following:</b></p>
     * <ul>
     *   <li>@c 0 - <b>OK:</b> Successfully delivered.</li>
     *   <li>@c 1 - <b>Unknown error:</b> Error whose reason is not known or
     *     specified.</li>
     *   <li>@c 2 - <b>Invalid recipient:</b> Recipient phone number is invalid
     *     or unknown.</li>
     *   <li>@c 3 - <b>Unreachable recipient:</b> Recipient is temporarily
     *     unreachable, e.g. phone off or memory full.</li>
     *   <li>@c 4 - <b>Barred recipient:</b> Recipient is out of credits or
     *     blacklisted.</li>
     *   <li>@c 5 - <b>Subscription error:</b> Error related to recipient's
     *     service subscription.</li>
     *   <li>@c 6 - <b>Expired:</b> Message validity period has expired.</li>
     *   <li>@c 7 - <b>Routing:</b> No route to recipient or roaming not
     *     allowed.</li>
     *   <li>@c 8 - <b>Network:</b> Problem with SMS/MMS center network
     *     connection.</li>
     *   <li>@c 9 - <b>Capacity:</b> SMS/MMS center capacity temporarily
     *     exceeded.</li>
     *   <li>@c 10 - <b>Operator:</b> General error related to SMS/MMS center
     *     operation.</li>
     *   <li>@c 11 - <b>Protocol:</b> Error in message data / parameters or
     *     other protocol related error.</li>
     *   <li>@c 12 - <b>Canceled:</b> Message sending has been canceled.</li>
     * </ul>
     *
     * @return int
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Returns unique ID of the sent message.
     *
     * @return string
     */
    public function getMessageId() {
        return $this->messageId;
    }

    /**
     * Returns the recipient phone number exactly in the same format it was
     * originally specified.
     *
     * Return value is phone number in the format it was given. For example,
     * (040) 1234 567.
     * 
     * @return string
     */
    public function getOriginalRecipient() {
        return $this->originalRecipient;
    }

    /**
     * Returns the recipient phone number in international format.
     *
     * Phone number is returned in international format. For example,
     * @c +358401234567.
     *
     * @return string
     */
    public function getRecipient() {
        return $this->recipient;
    }
    
    /**
     * Returns date and time of delivery report arrival at operator message
     * center.
     * 
     * Date and time are returned in the following format: @c yyyy-mm-dd hh:mm:ss
     * 
     * @return string
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Returns true if the message was buffered to SMS or MMS center for later
     * delivery to mobile phone.
     * 
     * This can happen if the mobile phone was turned off, could not be
     * reached or its memory was full. More delivery reports for this recipient
     * will follow later.
     *
     * Return value is @c true for delayed delivery, @c false for
     * success or failure.
     *
     * @return bool
     */
    public function isDelayed() {
        if ($this->state == "WAITING") {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the message was successfully delivered to mobile phone.
     *
     * No more delivery reports for this recipient will be received.
     *
     * Return value is @c true for success, @c false for delay or
     * failure.
     *
     * @return bool
     */
    public function isDelivered() {
        if ($this->state == "OK") {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the message could not be delivered to mobile phone.
     *
     * No more delivery reports for this recipient will be received.
     *
     * Return value is @c true for failure, @c false for success or
     * delay.
     *
     * @return bool
     */
    public function isFailed() {
        if ($this->state == "ERROR") {
            return true;
        }

        return false;
    }

}

/**
 * Allows sending SMS and MMS messages to mobile phones using Labyrintti
 * SMS/MMS Gateway.
 *
 * If delivery reports of sent messages are needed, GwClientConfig::setReportUrl
 * must be called before sending the messages.
 * 
 * GwClient also supports monitoring the Gateway.
 */
class GwClient {

    /** @internal API header name. */
    const HEADER_NAME_API = "X-LabyrinttiAPI";
    /** @internal API name and version */
    const HEADER_VALUE_API = "lmgw-api-php/2.2";
    
    /** @internal HTTP status OK. */
    const HTTP_OK = 200;
    /** @internal HTTP status Bad Request. */
    const HTTP_BAD_REQUEST = 400;
    /** @internal HTTP status Unauthorized. */
    const HTTP_UNAUTHORIZED = 401;
    /** @internal HTTP status Forbidden. */
    const HTTP_FORBIDDEN = 403;
    
    /** @internal Availability monitoring URI. */
    const MONITOR_URI = "/monitor";
    /** @internal MMS send URI. */
    const MMS_URI = "/sendmms";
    /** @internal SMS send URI */
    const SMS_URI = "/sendsms";

    /**
     * Client configuration.
     * @var GwClientConfig $config
     */
    private $config;
    
    /**
     * Socket connection resource handle.
     * @var resource $socket
     */
    private $socket;
    
    /**
     * Constructs a new Gateway client.
     * 
     * @param GwClientConfig $config    Gateway client configuraion
     */
    public function __construct($config = null) {
        if (is_null($config)) {
            $config = new GwClientConfig();
        }

        $this->setConfig($config);
        $this->socket = null;
    }

    /**
     * Returns the client configuration.
     *
     * @return GwClientConfig
     */
    public function getConfig() {
        return $this->config;
    }
  
    /**
     * Sends availability monitoring request to Gateway.
     * 
     * @return MonitorResult
     * 
     * @throws GwConfigException            if config validation fails
     * @throws AccessDeniedException        if access to Gateway is denied
     * @throws ConnectionFailedException    if Gateway cannot be connected or
     *                                      if connection times out
     * @throws InvalidParametersException   if Gateway detects that some message
     *                                      parameters are invalid
     * 
     * @see MonitorResult
     */
    public function monitor() {
        // validate config
        self::validateMonitorConfig($this->config);
        
        try {
            return $this->sendMonitorRequest();
        } catch (GwHttpException $e) {
            if ($e->getCode() == self::HTTP_UNAUTHORIZED || $e->getCode() == self::HTTP_FORBIDDEN) {
                // 401 unauthorized: invalid name, password or address
                // 403 forbidden: HTTP(S) sending not allowed
                throw new AccessDeniedException($e->getMessage());
            } elseif ($e->getCode() == self::HTTP_BAD_REQUEST) {
                // 400 bad request: invalid request parameters
                throw new InvalidParametersException($e->getMessage());
            } else {
                // for example, 404 not found: invalid request URL
                throw new ConnectionFailedException($e->getMessage());
            }
        }
    }
    
    /**
     * Sends SMS or MMS message to one or more mobile phones.
     *
     * Return value is always an @c array of SendResult objects, even
     * if only one message is sent.
     * 
     * @param Message $message  SmsMessage or MmsMessage object
     *
     * @return array
     *
     * @throws AccessDeniedException        if access to Gateway is denied
     * @throws ConnectionFailedException    if Gateway cannot be connected or
     *                                      if connection times out
     * @throws InvalidParametersException   if Gateway detects that some message
     *                                      parameters are invalid
     * @throws InvalidMmsObjectException    if reading MmsObject content fails
     * @throws GwConfigException            if config validation fails
     * @throws MessageContentException      if message validation fails
     *
     * @see SendResult
     */
    public function send($message) {
        // validate config
        self::validateConfig($this->config);

        // validate message
        self::validateMessage($message);

        // send message
        try {
            if ($message instanceof SmsMessage) {
                return $this->sendSms($message);
            } elseif ($message instanceof MmsMessage) {
                // validate MmsObjects
                Validator::validateMmsMessageObjects($message->getObjects());
                
                return $this->sendMms($message);
            }
        } catch (GwHttpException $e) {
            if ($e->getCode() == self::HTTP_UNAUTHORIZED || $e->getCode() == self::HTTP_FORBIDDEN) {
                // 401 unauthorized: invalid name, password or address
                // 403 forbidden: HTTP(S) sending not allowed
                throw new AccessDeniedException($e->getMessage());
            } elseif ($e->getCode() == self::HTTP_BAD_REQUEST) {
                // 400 bad request: invalid request parameters
                throw new InvalidParametersException($e->getMessage());
            } else {
                // for example, 404 not found: invalid request URL
                throw new ConnectionFailedException($e->getMessage());
            }
        }
    }

    /**
     * Changes the client configuration.
     *
     * @param GwClientConfig $config    Gateway client configuration
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Sends the given MMS message object and returns an array of SendResult
     * objects.
     *
     * @param MmsMessage $message   MMS message object
     *
     * @return array
     *
     * @throws InvalidMmsObjectException    if the content file of a MmsObject
     *                                      is not found or
     *                                      if one of the MmsObjects has no
     *                                      content
     * @throws GwHttpException              if HTTP response status code is not
     *                                      200 OK
     * @throws ConnectionFailedException    if Gateway cannot be connected,
     *                                      if connection times out,
     *                                      if connection is refused or
     *                                      if send result line count in Gateway
     *                                      response does not match message
     *                                      recipient count
     */
    private function sendMms($message) {
        // generate random multipart boundary
        $boundary = HttpUtils::createMultipartBoundary();

        // create request headers
        $headers = self::createRequestHeaders($this->config, self::MMS_URI, array(
            'Content-Type' => "multipart/form-data; boundary=" . $boundary
        ));

        // get send parameters
        $params = array_merge(
            ConfigUtils::getSendParameters($this->config, $message),
            MessageUtils::getSendParameters($message)
        );
 
        // open connection to MMS Gateway
        $this->openSocket();

        // write request headers to socket
        fwrite($this->socket, $headers, strlen($headers));

        // write parameters to socket
        foreach ($params as $name => $value) {
            $entry = HttpUtils::createMultipartTextEntry($boundary, $name, $value);
            ChunkWriter::writeTextChunkToResource($this->socket, $entry);
        }
        
        // write objects to socket
        $objects = $message->getObjects();
        for ($i = 0; $i < sizeof($objects); $i++) {
            $header = HttpUtils::createMultipartObjectHeader($boundary, "object" . ($i + 1), $objects[$i]);
            ChunkWriter::writeMultipartObjectChunkToResource($this->socket, $header, $objects[$i]);
        }

        // write end boundary and end chunk to socket
        ChunkWriter::writeTextChunkToResource($this->socket, HttpUtils::createMultipartEndBoundary($boundary));
        ChunkWriter::writeEndChunkToResource($this->socket);

        // flush
        $this->flushSocket();

        // read response and return send results
        return self::readSendResponse($this->socket, $message);
    }

    /**
     * Sends an availability monitoring request to the Gateway and returns a
     * MonitorResult object.
     *
     * @return MonitorResult
     *
     * @throws GwHttpException              if HTTP response status code is not
     *                                      200 OK
     * @throws ConnectionFailedException    if Gateway cannot be connected,
     *                                      if connection times out or
     *                                      if connection is refused
     */
    private function sendMonitorRequest() {
        // create request headers
        $headers = self::createRequestHeaders($this->config, self::MONITOR_URI);

         // get monitor parameters
        $params = ConfigUtils::getMonitorParameters($this->config);

        // construct content
        $content = HttpUtils::createRequestQuery($params);
        
        // start response time count
        $responseTimeStart = microtime(true);

        // open connection to SMS Gateway
        $this->openSocket();

        // write request to socket
        fwrite($this->socket, $headers, strlen($headers));
        if (strlen($content)) {
            ChunkWriter::writeTextChunkToResource($this->socket, $content);
        }
        ChunkWriter::writeEndChunkToResource($this->socket);

        // flush the socket
        $this->flushSocket();
 
        // read response
        self::readMonitorResponse($this->socket);
    
        // end response time count and calculate response time in milliseconds
        $responseTimeEnd = microtime(true);
        $responseTime = (($responseTimeEnd - $responseTimeStart) * 1000);
            
        return new MonitorResult($responseTime);
    }
    
    /**
     * Sends the given SMS message object and returns an array of send result
     * objects.
     *
     * @param SmsMessage $message   SMS message object
     *
     * @return array    array of send result objects
     *
     * @throws GwHttpException              if HTTP response status code is not
     *                                      200 OK
     * @throws ConnectionFailedException    if Gateway cannot be connected,
     *                                      if connection times out,
     *                                      if connection is refused or
     *                                      if send result line count in Gateway
     *                                      response does not match message
     *                                      recipient count
     */
    private function sendSms($message) {
        // create request headers
        $headers = self::createRequestHeaders($this->config, self::SMS_URI);
        
         // get send parameters
        $params = array_merge(
            ConfigUtils::getSendParameters($this->config, $message),
            MessageUtils::getSendParameters($message)
        );

        // construct content
        $content = HttpUtils::createRequestQuery($params);

        // open connection to SMS Gateway
        $this->openSocket();

        // write request to socket
        fwrite($this->socket, $headers, strlen($headers));
        ChunkWriter::writeTextChunkToResource($this->socket, $content);
        ChunkWriter::writeEndChunkToResource($this->socket);

        // flush
        $this->flushSocket();

        // read response and return send results
        return self::readSendResponse($this->socket, $message);
    }
 
    /**
     * Creates HTTP POST request headers string.
     * 
     * @param GwClientConfig    $config     Gateway clien configuration
     * @param string            $url        request URL
     * @param array             $headers    additional headers (optional)
     * 
     * @return string   complete request headers as a string
     */
    private static function createRequestHeaders($config, $url, $headers = array()) {
        $commonHeaders = array(
            'Content-Type' => "application/x-www-form-urlencoded",
            'Host' => $config->getHost() . ":" . $config->getPort(),
            'Transfer-Encoding' => "chunked",
            self::HEADER_NAME_API => self::HEADER_VALUE_API
        );
        
        return HttpUtils::createHttpRequestHeaders("POST", $url, array_merge($commonHeaders, $headers));
    }
    
    /**
     * Opens a socket connection to Gateway.
     *
     * @throws ConnectionFailedException    if Gateway cannot be connected
     */
    private function openSocket() {
        // open socket
        $errNo = "";
        $errStr = "";
        $this->socket = \pfsockopen(
            ($this->config->isSecure() ? "ssl://" : "") . $this->config->getHost(),
            $this->config->getPort(),
            $errNo,
            $errStr, 
            $this->config->getConnectTimeout()
        );
        
        if (!$this->socket) {
            throw new ConnectionFailedException(trim($errStr));
        }
    }
    
    /**
     * Forces a write of all buffered output to the socket and sets stream
     * timeout.
     */
    private function flushSocket() {
        fflush($this->socket);
        stream_set_timeout($this->socket, $this->config->getSendTimeout());
    }
    
    /**
     * Checks if the connection in given socket has timed out.
     * 
     * @param resource  $socket socket resource handle
     * 
     * @throws ConnectionFailedException    if connection has timed out
     */
    private static function checkSendTimeout($socket) {
        $streamMetaData = stream_get_meta_data($socket);
        if ($streamMetaData['timed_out']) {
            throw new ConnectionFailedException("Connection timed out waiting for HTTP response");
        }
    }
    
    /**
     * Checks HTTP response status.
     * 
     * @param string    $statusCode     HTTP response status code
     * @param string    $reasonPhrase   HTTP response reason phrase
     * 
     * @throws GwHttpException              if HTTP response status code is
     *                                      present but not 200 OK
     * @throws ConnectionFailedException    if HTTP reponse status code is not
     *                                      present (connection is refused)
     */
    private static function checkResponseStatus($statusCode, $reasonPhrase) {
        if ($statusCode) {
            if (is_numeric($statusCode) && (int)$statusCode != self::HTTP_OK) {
                throw new GwHttpException($reasonPhrase, $statusCode);
            }
        } else {
            throw new ConnectionFailedException("Connection refused");
        }
    }
    
    /**
     * Reads and skips monitor response which should be empty anyway.
     * 
     * @param resource $socket  file pointer to socket resource
     * 
     * @throws GwHttpException              if HTTP response status code is not
     *                                      200 OK
     * @throws ConnectionFailedException    if connection times out or
     *                                      if connection is refused
     */
    private static function readMonitorResponse($socket) {
        // read response status
        $status = rtrim(fgets($socket));
        
        // read response headers and content
        $statusCode = null;
        $reasonPhrase = null;
        if ($status) {
            $statusArr = explode(" ", $status, 3);
            $statusCode = $statusArr[1];
            $reasonPhrase = $statusArr[2];

            // read response headers into an array
            $headers = array();
            while (!feof($socket)) {
                // read the next header line
                $line = rtrim(fgets($socket));

                // check for end of headers
                if (strlen($line)) {
                    $lineArr = explode(": ", $line, 2);
                    $headers[$lineArr[0]] = $lineArr[1];
                } else {
                    break;
                }
            }

            // read response content
            $length = $headers['Content-Length'];
            $content = array();
            while (!feof($socket) && $length > 0) {
                $line = fgets($socket);
                $length -= strlen($line);
                $content[] = rtrim($line);
            }
        }
        
        // check for timeout
        self::checkSendTimeout($socket);
        
        // check status
        self::checkResponseStatus($statusCode, $reasonPhrase);
    }
    
    /**
     * Reads and parses send results.
     * 
     * Response content has one line per destination, for example:
     *
     * <internat-number> OK <msg-count> <description>
     * => +358401234567 OK 2 messages accepted for sending
     *
     * <internat-number> ERROR <code> <msg-count> <description>
     * => 12345 ERROR 2 1 message failed: Too short phone number
     *
     * @param resource $socket  file pointer to socket resource
     * @param Message $message  sent message object
     * 
     * @return array
     * 
     * @throws GwHttpException              if HTTP response status code is not
     *                                      200 OK
     * @throws ConnectionFailedException    if connection times out,
     *                                      if connection is refused or
     *                                      if send result line count in Gateway
     *                                      response does not match message
     *                                      recipient count
     */
    private static function readSendResponse($socket, $message) {
        // read response status
        $status = rtrim(fgets($socket));

        // read response headers and send result lines
        $statusCode = null;
        $reasonPhrase = null;
        $recipients = $message->getRecipients();
        $results = array();
        if ($status) {
            $statusArr = explode(" ", $status, 3);
            $statusCode = $statusArr[1];
            $reasonPhrase = $statusArr[2];

            // read response headers into an array
            $headers = array();
            while (!feof($socket)) {
                // read the next header line
                $line = rtrim(fgets($socket));

                // check for end of headers
                if (strlen($line)) {
                    $lineArr = explode(": ", $line, 2);
                    $headers[$lineArr[0]] = $lineArr[1];
                } else {
                    break;
                }
            }

            // create send result array
            $length = $headers['Content-Length'];
            for ($i = 0; $i < sizeof($recipients) && !feof($socket) && $length > 0; $i++) {
                // read next result line
                $line = fgets($socket);
                $length -= strlen($line);

                // parse the result line
                $recipient = strtok($line, " ");
                $originalRecipient = $recipients[$i];
                $error = (strtok(" ") == "OK" ? 0 : intval(strtok(" ")));
                $messageCount = intval(strtok(" "));
                $description = strtok("\r\n");

                // create new send result
                $results[] = new SendResult($recipient, $originalRecipient,
                    $error, $messageCount, $description);
            }
        }
        
        // check for timeout
        self::checkSendTimeout($socket);
        
        // check status
        self::checkResponseStatus($statusCode, $reasonPhrase);
        
        // check for unmatching amount of recipients and results
        if (sizeof($results) != sizeof($recipients)) {
            if (sizeof($results) < sizeof($recipients)) {
                throw new ConnectionFailedException(
                    "Unexpected end of send result lines in HTTP response");
            } elseif (sizeof($results) > sizeof($recipients)) {
                throw new ConnectionFailedException(
                    "Too many send result lines in HTTP response");
            }
        }
        
        return $results;
    }

    /**
     * Throws an exception if the given configuration is invalid for message
     * sending.
     *
     * @param GwClientConfig $config    Gateway client configuration
     *
     * @throws GwConfigException    if some required value is null,
     *                              if some value is invalid
     */
    private static function validateConfig($config) {
        try {
            Validator::validateNonNull("GwClientConfig", $config);

            Validator::validateNonEmpty("Gateway user name", $config->getUser());
            Validator::validateNonEmpty("Gateway password", $config->getPassword());

            Validator::validateNonEmpty("Gateway host", $config->getHost());
            Validator::validatePort("Gateway port", $config->getPort());
        } catch (Exception $e) {
            throw new GwConfigException($e->getMessage());
        }
    }
    
    /**
     * Throws an exception if the given message is invalid.
     *
     * @param Message $message  SMS or MMS message object
     *
     * @throws MessageContentException  if some required value is null,
     *                                  if some value is invalid
     */
    private static function validateMessage($message) {
        try {
            Validator::validateNonNull("Message", $message);

            if (!($message instanceof Message)) {
                throw new Exception("Unsupported message type: " . get_class($message));
            }

            if (!sizeof($message->getRecipients())) {
                throw new Exception("Message has no recipients");
            }

            Validator::validateNonEmptyMessage("Message", $message);
        } catch (Exception $e) {
            throw new MessageContentException($e->getMessage());
        }
    }
    
    /**
     * Throws an exception if the given configuration is invalid for
     * availability monitoring.
     *
     * @param GwClientConfig $config    Gateway client configuration
     *
     * @throws GwConfigException    if some required value is null,
     *                              if some value is invalid
     */
    private static function validateMonitorConfig($config) {
        try {
            Validator::validateNonNull("GwClientConfig", $config);

            Validator::validateNonEmpty("Gateway host", $config->getHost());
            Validator::validatePort("Gateway port", $config->getPort());
        } catch (Exception $e) {
            throw new GwConfigException($e->getMessage());
        }
    }

}

/**
 * Configuration for GwClient SMS/MMS message sender.
 *
 * For typical uses, only setUser and setPassword are required, other
 * properties can use their default values.
 *
 * However, to use secure @c HTTPS, also setSecure has to be set (note that
 * HTTPS support must be separately purchased from Labyrintti).
 *
 * <p><b>Default values of properties:</b></p>
 * <ul>
 *   <li>@c user - not specified</li>
 *   <li>@c password - not specified</li>
 *   <li>@c host - gw.labyrintti.com</li>
 *   <li>@c port - 28080 (secure = @c false) or 28443 (secure = @c true)</li>
 *   <li>@c secure - @c false</li>
 * </ul>
 *
 * @see GwClient
 */
class GwClientConfig {

    /** @internal Default Gateway host address. */
    const DEFAULT_HOST = "gw.labyrintti.com";
    /** @internal Default HTTP port. */
    const DEFAULT_HTTP_PORT = 28080;
    /** @internal Default HTTPS port. */
    const DEFAULT_HTTPS_PORT = 28443;
    /** @internal Default connect timeout (2 minutes). */
    const DEFAULT_CONNECT_TIMEOUT = 120;
    /** @internal Default send timeout (5 minutes). */
    const DEFAULT_SEND_TIMEOUT = 300;
    
    /**
     * Gateway host address.
     * @var string $host
     */
    private $host;

    /**
     * Gateway password.
     * @var string $password
     */
    private $password;

    /**
     * Gateway host port.
     * @var int $port
     */
    private $port;

    /**
     * Delivery report URL.
     * @var string $reportUrl
     */
    private $reportUrl;

    /**
     * Secure connection.
     * @var bool $secure
     */
    private $secure;
    
    /**
     * Gateway username.
     * @var string $user
     */
    private $user;
    
    /**
     * Connect timeout in seconds.
     * @var int $connectTimeout
     */
    private $connectTimeout;
    
    /**
     * Send timeout in seconds.
     * @var int $sendTimeout
     */
    private $sendTimeout;

    /**
     * Constructs a new Gateway client configuration.
     *
     * @param string $user      Gateway username
     * @param string $password  Gateway password
     */
    public function __construct($user = null, $password = null) {
        $this->host = self::DEFAULT_HOST;
        $this->password = $password;
        $this->port = self::DEFAULT_HTTP_PORT;
        $this->reportUrl= null;
        $this->secure = false;
        $this->user = $user;
        $this->connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;
        $this->sendTimeout = self::DEFAULT_SEND_TIMEOUT;
    }

    /**
     * Returns the Gateway host address used when sending messages.
     *
     * Default is @c gw.labyrintti.com which will always resolve to a
     * functional Gateway server (IP address can vary, see Labyrintti
     * documentation for the actual IP addresses).
     *
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Returns password for the Gateway user account used when sending messages.
     *
     * @return string
     *
     * @see getUser
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Returns the Gateway TCP port used when sending messages.
     * 
     * Default is @c 28080 for unsecure HTTP sending and @c 28443 for secure
     * (HTTPS) sending.
     *
     * @return string
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Returns the URL where the delivery reports of the sent messages should be
     * sent.
     *
     * @return string
     */
    public function getReportUrl() {
        return $this->reportUrl;
    }

    /**
     * Returns Gateway user account name used when sending messages.
     *
     * @return string
     *
     * @see getPassword
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Returns timeout for connecting the Gateway, in seconds.
     * 
     * Default is 120 seconds (2 minutes).
     * 
     * @return int
     */
    public function getConnectTimeout() {
        return $this->connectTimeout;
    }

    /**
     * Returns timeout for sending a message to the Gateway, in seconds.
     * 
     * Default is 300 seconds (5 minutes).
     * 
     * @return int
     */
    public function getSendTimeout() {
        return $this->sendTimeout;
    }

    /**
     * Returns true if secure HTTPS sending is used. Note that the secure
     * sending feature must be separately purchased from Labyrintti.
     *
     * Default is @c false
     *
     * @return bool
     */
    public function isSecure() {
        return $this->secure;
    }

    /**
     * Sets the Gateway host address used when sending messages.
     *
     * Default is @c gw.labyrintti.com which will always resolve to a
     * functional Gateway server (IP address can vary, see Labyrintti
     * documentation for the actual IP addresses).
     *
     * @param string $host  Gateway host name or IP address
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Sets password for the Gateway user account used when sending messages.
     *
     * @param string $password  Gateway user account password
     *
     * @see setUser
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Sets the Gateway TCP port used when sending messages.
     *
     * Default is @c 28080 for unsecure HTTP sending and @c 28443 for secure
     * (HTTPS) sending.
     *
     * @param int $port Gateway TCP port
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * Sets the URL where the delivery reports of the sent messages should be
     * sent.
     *
     * Secure @c HTTPS URLs are acceptable.
     *
     * @param string $reportUrl report delivery URL
     */
    public function setReportUrl($reportUrl) {
        $this->reportUrl = $reportUrl;
    }

    /**
     * Enables or disables secure HTTPS sending.
     *
     * Note that the secure sending feature must be separately purchased from
     * Labyrintti.
     *
     * Default is @c false.
     *
     * @note Setting this property also changes the port if the port is currently
     * set to it's default value (28080 or 28443).
     *
     * @param bool $enabled true for @c HTTPS, false for @c HTTP
     */
    public function setSecure($enabled) {
        if ($enabled) {
            $this->port = self::DEFAULT_HTTPS_PORT;
        } else {
            $this->port = self::DEFAULT_HTTP_PORT;
        }
        
        $this->secure = $enabled;
    }

    /**
     * Sets Gateway user account name used when sending messages.
     *
     * @param string $user  Gateway user account name
     *
     * @see setPassword
     */
    public function setUser($user) {
        $this->user = $user;
    }
    
    /**
     * Sets timeout for connecting the Gateway, in seconds.
     * 
     * Default is 120 seconds (2 minutes).
     * 
     * @param int $connectTimeout timeout in seconds
     */
    public function setConnectTimeout($connectTimeout) {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Sets timeout for sending a message to the Gateway, in seconds.
     * 
     * Default is 300 seconds (5 minutes).
     * 
     * @param int $sendTimeout timeout in seconds
     */
    public function setSendTimeout($sendTimeout) {
        $this->sendTimeout = $sendTimeout;
    }

}

/**
 * Allows reception of SMS messages, MMS messages and/or delivery reports from
 * Labyrintti SMS/MMS Gateway.
 *
 * Support for <strong>delivery reports</strong> requires that the
 * GwServerConfig::setReportUrl is called so that the Gateway knows where to
 * send the delivery reports.
 */
class GwServer {
    
    /** @internal API header name. */
    const HEADER_NAME_API = "X-LabyrinttiAPI";
    /** @internal API name and version. */
    const HEADER_VALUE_API = "lmgw-api-php/2.2";
    
    /** @internal SMS message request type. */
    const REQ_TYPE_SMS      = "sms";
    /** @internal MMS message request type. */
    const REQ_TYPE_MMS      = "mms";
    /** @internal Delivery report request type. */
    const REQ_TYPE_REPORT   = "report";
    /** @internal Unknown request type. */
    const REQ_TYPE_UNKNOWN  = "unknown";

    /** @internal SMS message response content type. */
    const RESP_CONTENT_TYPE_SMS      = "text/plain";
    /** @internal MMS message response content type. */
    const RESP_CONTENT_TYPE_MMS      = "multipart/form-data";
    /** @internal Empty response content type. */
    const RESP_CONTENT_TYPE_EMPTY    = "text/plain";

    /**
     * Server configuration.
     * @var GwServerConfig $config
     */
    private $config;

    /**
     * Constructs a new instance of Gateway server
     *
     * @param GwServerConfig $config    Gateway server configuration
     */
    public function __construct($config = null) {
        if (is_null($config)) {
            $config = new GwServerConfig();
        }

        $this->setConfig($config);
    }

    /**
     * Returns the Gateway Server configuration.
     *
     * @return GwServerConfig
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Receives a MMS message from the Gateway.
     *
     * @return MmsMessage
     *
     * @throws GwServerException    if client IP address is unallowed,
     *                              if MMS message request is invalid
     */
    public function receiveMms() {
        try {
            // check client IP address
            $this->checkRemoteAddress();

            // check request type
            if (self::getRequestType() != self::REQ_TYPE_MMS) {
                throw new GwServerException(null, "Invalid MMS message request");
            }

            // create MMS message
            $mmsMessage = new MmsMessage();

            // set MMS message sender
            $sender = $_REQUEST['source'];
            $mmsMessage->setSender($sender);

            // set MMS message operator
            $operator = $_REQUEST['operator'];
            $mmsMessage->setOperator($operator);

            // add MMS message recipient
            $recipient = $_REQUEST['dest'];
            $mmsMessage->addRecipient($recipient);

            // set MMS message keyword(s)
            $keywords = Utils::stripMagicQuotes($_REQUEST['keyword']);
            $mmsMessage->setKeywords($keywords);

            // set MMS message parameters
            $parameters = explode(" ", Utils::stripMagicQuotes($_REQUEST['params']));
            $mmsMessage->setParameters($parameters);
            
            // set timestamp
            $timestamp = $_REQUEST['timestamp'];
            $mmsMessage->setTimestamp($timestamp);

            // set MMS message subject (optional)
            if (isset($_REQUEST['subject'])) {
                $subject = Utils::stripMagicQuotes($_REQUEST['subject']);
                $mmsMessage->setSubject($subject);
            }

            // read attached files and create MMS objects out of them
            $objects = MmsObjectUtils::readMmsObjectsFromRequest();

            // add created MMS objects to message
            foreach ($objects as $object) {
                // set first plain text object as message text
                if (is_null($mmsMessage->getText()) && strpos($object->getContentType(), "text/plain") !== false) {
                    $mmsMessage->setText($object->getContent());
                }

                // add MMS object to MMS message
                $mmsMessage->addObject($object);
            }

            return $mmsMessage;
        } catch (GwServerException $e) {
            // respond with error response and rethrow the exception for user
            $this->respondError($e->getHttpStatusCode(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Receives a SMS message from the Gateway.
     *
     * @return SmsMessage
     *
     * @throws GwServerException    if client IP address is unallowed,
     *                              if SMS message request is invalid
     */
    public function receiveSms() {
        try {
            // check client IP address
            $this->checkRemoteAddress();
        
            // check request type
            if (self::getRequestType() != self::REQ_TYPE_SMS) {
                throw new GwServerException(null, "Invalid SMS message request");
            }

            // create SMS message
            $smsMessage = new SmsMessage();

            // set SMS message sender
            $sender = $_REQUEST['source'];
            $smsMessage->setSender($sender);

            // set SMS message operator
            $operator = $_REQUEST['operator'];
            $smsMessage->setOperator($operator);

            // add SMS message recipient
            $recipient = $_REQUEST['dest'];
            $smsMessage->addRecipient($recipient);

            // set SMS message keyword(s)
            $keywords = Utils::stripMagicQuotes($_REQUEST['keyword']);
            $smsMessage->setKeywords($keywords);

            // set SMS message parameters
            $parameters = explode(" ", Utils::stripMagicQuotes($_REQUEST['params']));
            $smsMessage->setParameters($parameters);

            // set SMS message text
            $text = Utils::stripMagicQuotes($_REQUEST['text']);
            $smsMessage->setText($text);
            
            // set timestamp
            $timestamp = $_REQUEST['timestamp'];
            $smsMessage->setTimestamp($timestamp);

            // set SMS message header (optional)
            if (isset($_REQUEST['header'])) {
                $smsMessage->setHeader($_REQUEST['header']);
            }

            // set SMS message binary data (optional)
            if (isset($_REQUEST['binary'])) {
                $smsMessage->setData($_REQUEST['binary']);
            }

            return $smsMessage;
        } catch (GwServerException $e) {
            // respond with error response and rethrow the exception for user
            $this->respondError($e->getHttpStatusCode(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Receives a delivery report from the Gateway.
     *
     * @return DeliveryReport
     *
     * @throws GwServerException    if client IP address is unallowed,
     *                              if delivery report request is invalid
     */
    public function receiveReport() {
        try {
            // check client IP address
            $this->checkRemoteAddress();
        
            // check request type
            if (self::getRequestType() != self::REQ_TYPE_REPORT) {
                throw new GwServerException(null, "Invalid delivery report request");
            }

            // construct delivery report
            $report = new DeliveryReport(
                $_REQUEST['dest'],
                $_REQUEST['original-dest'],
                $_REQUEST['status'],
                intval($_REQUEST['code']),
                Utils::stripMagicQuotes($_REQUEST['message']),
                $_REQUEST['timestamp'],
                $_REQUEST[Message::REPORT_PARAM_MESSAGE_ID]
            );

            return $report;
        } catch (GwServerException $e) {
            // respond with error response and rethrow the exception for user
            $this->respondError($e->getHttpStatusCode(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Responds to received message with MMS message.
     *
     * The response can be omitted only if a separate service number with
     * mobile-originated billing is used (consult with Labyrintti).
     *
     * @param MmsMessage $message   MmsMessage that will be sent back to the
     *                              end-user. If null, no response message will
     *                              be sent, but notice that a response message
     *                              is usually required for end-user billing to
     *                              work!
     * 
     * @throws InvalidMmsObjectException    if one of MmsObjects content file
     *                                      is not found,
     *                                      if one of MmsObjects has no content
     * @throws MessageContentException      if response message is invalid
     * @throws GwServerException            if cannot write to output buffer
     * 
     */
    public function respondMms($message = null) {
        try {
            // send response message if specified
            if (!is_null($message)) {
                $this->respondMmsMessage($message);
            } else {
                $this->respondEmpty();
            }
        } catch (MessageContentException $e) {
            // send error response and rethrow the exception for user
            $this->respondError(500, $e->getMessage());
            throw $e;
        } catch (GwServerException $e) {
            // send error response and rethrow the exception for user
            $this->respondError($e->getHttpStatusCode(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Responds to received delivery report with 200 OK.
     */
    public function respondReport() {
        $this->respondEmpty();
    }

    /**
     * Responds to received message with SMS message.
     *
     * The response can be omitted only if a separate service number with
     * mobile-originated billing is used (consult with Labyrintti).
     *
     * @param SmsMessage $message   SmsMessage that will be sent back to the
     *                              end-user. If null, no response message will
     *                              be sent, but notice that a response message
     *                              is usually required for end-user billing to
     *                              work!
     * 
     * @throws MessageContentException      if response message is invalid
     * @throws GwServerException            if cannot write to output buffer
     */
    public function respondSms($message = null) {
        try {
            // send response message if specified
            if (!is_null($message)) {
                $this->respondSmsMessage($message);
            } else {
                $this->respondEmpty();
            }
        } catch (MessageContentException $e) {
            // send error response and rethrow the exception for user
            $this->respondError(500, $e->getMessage());
            throw $e;
        } catch (GwServerException $e) {
            // send error response and rethrow the exception for user
            $this->respondError($e->getHttpStatusCode(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Changes the Gateway Server configuration.
     *
     * @param GwServerConfig $config    Gateway Server configuration
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Checks that client IP address is allowed.
     *
     * @throws GwServerException    if client IP address is not allowed
     */
    private function checkRemoteAddress() {
        // get allowed addresses from the config
        $allowedAddresses = $this->config->getAllowedAddresses();

        // check if allowed addresses are set
        if (isset($allowedAddresses) && is_array($allowedAddresses)) {
            // get remote address
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
            
            // compare remote address to allowed addresses
            $allowed = false;
            foreach ($allowedAddresses as $allowedAddr) {
                if ($allowedAddr == $remoteAddr ||
                    (isset($allowedAddr) &&
                    strpos($remoteAddr, $allowedAddr) === 0 &&
                    substr($allowedAddr, -1) == ".")) {
                    $allowed = true;
                    break;
                }
            }

            // throw exception if remote address is not allowed
            if (!$allowed) {
                throw new GwServerException(403, "Unallowed client IP address " . $remoteAddr);
            }
        }
    }

    /**
     * Returns request type.
     * 
     * @return string
     */
    private static function getRequestType() {
        if (isset($_REQUEST['objects'])) {
            return self::REQ_TYPE_MMS;
        } elseif (isset($_REQUEST['keyword'])) {
            return self::REQ_TYPE_SMS;
        } elseif (isset($_REQUEST['status'])) {
            return self::REQ_TYPE_REPORT;
        } else {
            return self::REQ_TYPE_UNKNOWN;
        }
    }
    
    /**
     * Responds to received message with a MMS message.
     *
     * @param MmsMessage    $message    message to send as response
     *
     * @throws MessageContentException      if message validation fails
     * @throws InvalidMmsObjectException    if one of MmsObjects content file
     *                                      is not found,
     *                                      if one of MmsObjects has no content
     * @throws GwServerException            if cannot write to output buffer
     */
    private function respondMmsMessage($message) {
        // validate message and objects
        self::validateResponseMessage($message);
        Validator::validateMmsMessageObjects($message->getObjects());
        
        // open output for writing
        $output = fopen("php://output", "wb");
        if (!$output) {
            throw new GwServerException(null, "Failed to open output buffer for writing response message");
        }
        
        // get response parameters
        $params = array_merge(
            ConfigUtils::getResponseParameters($this->config, $message),
            MessageUtils::getResponseParameters($message)
        );
 
        // create random multipart boundary
        $boundary = HttpUtils::createMultipartBoundary();

        // send response headers
        self::sendResponseHeaders(array(
            "Content-Type" => self::RESP_CONTENT_TYPE_MMS . "; boundary=" . $boundary,
            "Transfer-Encoding" => "chunked"
        ));

        // write parameters to output
        foreach ($params as $name => $value) {
            $entry = HttpUtils::createMultipartTextEntry($boundary, $name, $value);
            ChunkWriter::writeTextChunkToResource($output, $entry);
        }

        // write objects to output
        $objects = $message->getObjects();
        for ($i = 0; $i < sizeof($objects); $i++) {
            $header = HttpUtils::createMultipartObjectHeader($boundary, "object" . ($i + 1), $objects[$i]);
            ChunkWriter::writeMultipartObjectChunkToResource($output, $header, $objects[$i]);
        }

        // write end boundary and end chunk to output
        ChunkWriter::writeTextChunkToResource($output, HttpUtils::createMultipartEndBoundary($boundary));
        ChunkWriter::writeEndChunkToResource($output);

        // flush
        fflush($output);

        // close output
        \fclose($output);
    }
    
    /**
     * Responds to received message with a SMS message.
     *
     * @param SmsMessage    $message    message to send as response
     *
     * @throws MessageContentException      if message validation fails
     * @throws GwServerException            if cannot write to output buffer
     */
    private function respondSmsMessage($message) {
        // validate message
        self::validateResponseMessage($message);
        
        // open output for writing
        $output = fopen("php://output", "wb");
        if (!$output) {
            throw new GwServerException(null, "Failed to open output buffer for writing response message");
        }
        
        // get response parameters
        $params = array_merge(
            ConfigUtils::getResponseParameters($this->config, $message),
            MessageUtils::getResponseParameters($message)
        );
        
        // send response headers
        self::sendResponseHeaders(array(
            "Content-Type" => self::RESP_CONTENT_TYPE_SMS,
            "Transfer-Encoding" => "chunked"
        ));

        // create SMS response content
        $content = "";
        foreach ($params as $key => $value) {
            $content .= $key . "=" . $value . "\r\n";
        }

        // write SMS response content to output
        ChunkWriter::writeTextChunkToResource($output, $content);

        // write end chunk to output
        ChunkWriter::writeEndChunkToResource($output);

        // flush
        fflush($output);

        // close output
        \fclose($output);
    }

    /**
     * Responds to received message with an empty response.
     */
    private function respondEmpty() {
        self::sendResponseHeaders(array(
            "Content-Length" => 0,
            "Content-Type" => self::RESP_CONTENT_TYPE_EMPTY
        ));
    }

    /**
     * Sends error response headers with given error code and message.
     *
     * @param int       $code       error code
     * @param string    $message    error message
     */
    private function respondError($code, $message) {
        self::sendResponseHeaders(array("Connection" => "close"), array($code, $message));
    }

    /**
     * Sends HTTP response headers to Gateway.
     * 
     * @param type $headers
     * @param type $error
     */
    private static function sendResponseHeaders($headers = array(), $error = array()) {
        $commonHeaders = array(
            self::HEADER_NAME_API => self::HEADER_VALUE_API
        );
        
        HttpUtils::sendHttpResponseHeaders(array_merge($commonHeaders, $headers), $error);
    }
    
    /**
     * Throws an exception if the given response message is invalid.
     *
     * @param Message $message  SMS or MMS message object
     *
     * @throws MessageContentException  if some required value is null,
     *                                  if some value is invalid
     */
    private static function validateResponseMessage($message) {
        try {
            Validator::validateNonNull("Response message", $message);

            if (!($message instanceof Message)) {
                throw new Exception("Unsupported response message type: " . get_class($message));
            }

            Validator::validateNonEmptyMessage("Response message", $message);
        } catch (Exception $e) {
            throw new MessageContentException($e->getMessage());
        }
    }

}

/**
 * Configuration for GwServer SMS/MMS message server.
 *
 * @see GwServer
 */
class GwServerConfig {

    /**
     * Allowed client IP addresses.
     * @var array $allowedAddresses
     */
    private $allowedAddresses = null;

    /**
     * Delivery report URL
     * @var string $reportUrl
     */
    private $reportUrl;

    /**
     * Constructs a new configuration.
     */
    public function __construct() {
        $this->reportUrl = null;
    }

    /**
     * Returns (Gateway) IP addresses allowed to make HTTP requests to the
     * server.
     *
     * If specified, this acts like a mini-firewall that only allows
     * the specified Labyrintti Gateway IP addresses to forward messages and
     * delivery reports to the server. Other clients trying to contact the
     * server will receive an error response. This prevents using e.g. a web
     * browser to simulate Labyrintti Gateway and spoof messages.
     *
     * You can find the up-to-date Gateway IP addresses in the Labyrintti
     * Gateway HTTP Interface Guide or ask Labyrintti technical support.
     *
     * Besides IP addresses, this method accepts IP address prefixes ending in
     * a dot. For example, you could specify a complete IP address like
     * "1.2.3.4", or an IP address prefix like "1.2.3." which would match all
     * IP addresses starting with 1.2.3. This could be useful e.g. with some
     * proxy setups.
     *
     * Return value is array of IP addresses and/or IP address prefixes, or
     * @c null if all addresses are allowed.
     *
     * @return array
     */
    public function getAllowedAddresses() {
        return $this->allowedAddresses;
    }

    /**
     * Returns the URL where the delivery reports of the response messages
     * should be sent.
     *
     * @return string
     */
    public function getReportUrl() {
        return $this->reportUrl;
    }

    /**
     * Sets (Gateway) IP addresses allowed to make HTTP requests to this
     * server.
     *
     * If specified, this acts like a mini-firewall that only allows
     * the specified Labyrintti Gateway IP addresses to forward messages and
     * delivery reports to this servlet. Other clients trying to contact the
     * servlet will receive an error response. This prevents using e.g. a web
     * browser to simulate Labyrintti Gateway and spoof messages.
     *
     * You can find the up-to-date Gateway IP addresses in the Labyrintti
     * Gateway HTTP Interface Guide or ask Labyrintti technical support.
     *
     * Besides IP addresses, this method accepts IP address prefixes ending in
     * a dot. For example, you could specify a complete IP address like
     * "1.2.3.4", or an IP address prefix like "1.2.3." which would match all
     * IP addresses starting with 1.2.3. This could be useful e.g. with some
     * proxy setups.
     *
     * @param array $ipAddresses    array of IP addresses and/or IP address
     *                              prefixes
     */
    public function setAllowedAddresses($ipAddresses) {
        $this->allowedAddresses = $ipAddresses;
    }

    /**
     * Sets the URL where the delivery reports of the sent messages should be
     * sent.
     *
     * Secure @c HTTPS URLs are acceptable.
     *
     * @param string $reportUrl delivery report URL
     */
    public function setReportUrl($reportUrl) {
        $this->reportUrl = $reportUrl;
    }

}

/**
 * @internal
 * HTTP utils.
 */
class HttpUtils {

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Creates HTTP/1.1 request headers from the given data.
     *
     * @param string    $method     request method
     * @param string    $url        request URL
     * @param array     $headers    header array
     *
     * @return string
     */
    public static function createHttpRequestHeaders($method, $url, $headers) {
        $buffer = strtoupper($method) . " " . $url . " HTTP/1.1\r\n";
        foreach ($headers as $name => $value) {
            $buffer .= $name . ": " . $value . "\r\n";
        }
        $buffer .= "\r\n";
        
        return $buffer;
    }
    
    /**
     * Sends HTTP/1.1 response headers.
     * 
     * @param array $headers    header array
     * @param array $error      error array
     */
    public static function sendHttpResponseHeaders($headers, $error = array()) {
        if (!empty($error)) {
            header("HTTP/1.1 " . $error[0] . " " . $error[1]);
        }
        
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }
    }

    /**
     * Creates random HTTP multipart request boundary.
     *
     * @return string
     */
    public static function createMultipartBoundary() {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $len = strlen($chars);

        $boundary = "";
        for ($i = 0; $i < 22; $i++) {
            $boundary .= $chars[rand(0, $len - 1)];
        }

        return $boundary;
    }

    /**
     * Creates HTTP multipart request end boundary from the given boundary.
     *
     * @param string $boundary  HTTP multipart request boundary
     *
     * @return string
     */
    public static function createMultipartEndBoundary($boundary) {
        return "--" . $boundary . "--\r\n";
    }

    /**
     * Creates HTTP multipart request object header from the given data.
     *
     * @param string    $boundary   HTTP multipart request boundary
     * @param string    $name       parameter name
     * @param MmsObject $object     MMS object
     *
     * @return string
     */
    public static function createMultipartObjectHeader($boundary, $name, $object) {
        return "--" . $boundary . "\r\n" .
            "Content-Disposition: form-data; name=\"" . $name . "\"; filename=\"" . $object->getFilename() . "\"\r\n" .
            "Content-Type: " . $object->getContentType() . "\r\n" .
            "Content-Length: " . $object->getContentLength() . "\r\n\r\n";
    }

    /**
     * Creates HTTP multipart request text entry from the given data.
     *
     * @param string $boundary  HTTP multipart request boundary
     * @param string $name      parameter name
     * @param string $content   parameter value
     *
     * @return string
     */
    public static function createMultipartTextEntry($boundary, $name, $content) {
        return "--" . $boundary . "\r\n" .
            "Content-Disposition: form-data; name=\"" . $name . "\"\r\n" .
            "Content-Type: text/plain\r\n" .
            "Content-Length: " . strlen($content) . "\r\n\r\n" .
            $content . "\r\n";
    }

    /**
     * Creates a HTTP query string from the given array of parameters.
     *
     * @param array $params array of parameteris in form of:
     *                      array("name1" => "value1", "name2" => "value2")
     *
     * @return string
     */
    public static function createRequestQuery($params) {
        $query = "";
        foreach ($params as $name => $value) {
            $query .= $name . "=" . urlencode($value) . "&";
        }

        return rtrim($query, "&");
    }

}

/**
 * Abstract base class for SMS and MMS messages.
 */
abstract class Message {

    /** @internal Maximum sender length. */
    const MAX_SENDER_LENGTH         = 16;

    /** @internal Message ID report parameter name. */
    const REPORT_PARAM_MESSAGE_ID   = "com_labyrintti_gw_message_id";

    /**
     * Message delivery time.
     * @var int $deliveryTime
     */
    private $deliveryTime;

    /**
     * Message error.
     * @var bool $error
     */
    private $error;

    /**
     * Message ID.
     * @var string $id
     */
    private $id;

    /**
     * Message keywords.
     * @var string $keywords
     */
    private $keywords;

    /**
     * Message operator.
     * @var string $operator
     */
    private $operator;

    /**
     * Message parameters array.
     * @var array $parameters
     */
    private $parameters;

    /**
     * Message recipients array.
     * @var array $recipients
     */
    private $recipients;

    /**
     * Message sender.
     * @var string $sender
     */
    private $sender;

    /**
     * Service name.
     * @var string $serviceName
     */
    private $serviceName;

    /**
     * Service number.
     * @var string $serviceNumber
     */
    private $serviceNumber;

    /**
     * Custom message tag.
     * @var string $tag
     */
    private $tag;
    
    /**
     * Message text.
     * @var string $text
     */
    protected $text;
    
    /**
     * Date and time when message was received by operator message center.
     * @var string $timestamp
     */
    private $timestamp;

    /**
     * Message validity perioid.
     * @var int $validityPeriod
     */
    private $validityPeriod;

    /**
     * Constructs a new message.
     * 
     * @param string $recipient message recipient
     */
    public function __construct($recipient = null) {
        $this->recipients = array();
        if (isset($recipient)) {
            $this->addRecipient($recipient);
        }
        
        $this->deliveryTime = null;
        $this->error = null;
        $this->id = uniqid(null, true);
        $this->keywords = null;
        $this->operator = null;
        $this->parameters = null;
        $this->sender = null;
        $this->serviceName = null;
        $this->serviceNumber = null;
        $this->tag = null;
        $this->text = null;
        $this->timestamp = null;
        $this->validityPeriod = null;
    }
    
    /**
     * Returns a multi-line string representation of this message. Useful for
     * debugging.
     * 
     * @return string
     */
    public function __toString() {
        $properties = array_merge(array(
            "id" => var_export($this->id, true),
            "recipients" => "[" . implode(", ", $this->recipients) . "]",
            "sender" => var_export($this->sender, true),
            "operator" => var_export($this->operator, true),
            "keywords" => var_export($this->keywords, true),
            "parameters" => (!is_null($this->parameters) ? "[" . implode(", ", $this->parameters) . "]" :
                var_export($this->parameters, true)),
            "serviceNumber" => var_export($this->serviceNumber, true),
            "serviceName" => var_export($this->serviceName, true),
            "error" => var_export((bool)$this->error, true),
            "validityPeriod" => var_export($this->validityPeriod, true),
            "deliveryTime" => var_export($this->deliveryTime, true),
            "timestamp" => var_export($this->timestamp, true),
            "tag" => var_export($this->tag, true)
        ), $this->getProperties());
        
        $str = get_class($this) . "[" . PHP_EOL;
        foreach ($properties as $key => $value) {
            $str .= "  " . $key . "=" . $value . PHP_EOL;
        }
        $str .= "]";
        
        return $str;
    }
    
    /**
     * Adds a new message recipient.
     *
     * Duplicate recipients are allowed, but the Gateway will only send to
     * the first one.
     *
     * @param string $recipient recipient phone number, in any parsable format
     *
     * @throws Exception    if recipient phone number is null
     */
    public function addRecipient($recipient) {
        if (is_null($recipient)) {
            throw new Exception("Recipient phone number cannot be null");
        }

        $this->recipients[] = $recipient;
    }

    /**
     * @internal
     * Returns message delivery time or @c null if not set.
     *
     * Return value is @c int if relative delivery time is set or @c string
     * if absolute delivery time is set.
     *
     * @return mixed
     */
    public function getDeliveryTime() {
        return $this->deliveryTime;
    }

    /**
     * Returns a globally unique id of this message, used with delivery reports.
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns one or more words from the start of the message that identified
     * the service or @c null if none.
     *
     * The returned keywords are always in upper-case.
     *
     * For incoming messages received from mobile phones, this is never
     * @c null. If incoming message has no keywords, this returns an empty
     * string.
     *
     * @return string
     */
    public function getKeywords() {
        return $this->keywords;
    }

    /**
     * Returns name of message sender's mobile operator or @c null if none.
     *
     * @return string
     */
    public function getOperator() {
        return $this->operator;
    }

    /**
     * Returns message words following the keywords or @c null if none.
     * 
     * For incoming messages received from mobile phones, this is never
     * @c null. If incoming message has no parameters, this returns an empty
     * array.
     *
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Returns first message recipient or @c null if none.
     *
     * With messages received from mobile phones, this is the only recipient,
     * and indicates the service number that received the message.
     *
     * @return string
     */
    public function getRecipient() {
        return (sizeof($this->recipients) ? $this->recipients[0] : null);
    }

    /**
     * Returns message recipients array.
     *
     * @return array
     */
    public function getRecipients() {
        return $this->recipients;
    }

    /**
     * Returns message sender phone number or name or @c null if none.
     *
     * @return string
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * @internal
     * Returns service name.
     *
     * @return string
     */
    public function getServiceName() {
        return $this->serviceName;
    }

    /**
     * @internal
     * Returns service number.
     *
     * @return string
     */
    public function getServiceNumber() {
        return $this->serviceNumber;
    }

    /**
     * Returns message text.
     */
    abstract public function getText();

    /**
     * Returns custom tag associated with this message.
     * 
     * @return string|null Custom tag, or @c null if not specified
     */
    public function getTag() {
        return $this->tag;
    }
    
    /**
     * Returns date and time of message arrival at operator message center.
     * 
     * Date and time are returned in the following format: @c yyyy-mm-dd hh:mm:ss
     * 
     * If this message was not received from a mobile phone, returns @c null.
     * 
     * @return mixed
     */
    public function getTimestamp() {
        return $this->timestamp;
    }
    
    /**
     * @internal
     * Returns message validity period or @c null if not set.
     *
     * Return value is @c int if relative validity period is set or @c string
     * if absolute validity period is set.
     *
     * @return mixed
     */
    public function getValidityPeriod() {
        return $this->validityPeriod;
    }

    /**
     * @internal
     * Returns error status of the message.
     *
     * @return bool
     */
    public function isError() {
        return $this->error;
    }

    /**
     * Sets absolute message delivery time in datetime string format.
     *
     * Message delivery can be delayed so that the message will be stored in
     * the Gateway for a specific time before sending it to mobile phones.
     *
     * @param string    $datetime   absolute delivery time in datetime string
     *                              format (yyyy-mm-dd hh:mm)
     *
     * @throws Exception    if delivery time is @c null or
     *                      if delivery time datetime format is invalid
     */
    public function setAbsoluteDeliveryTime($datetime) {
        Validator::validateNonNull("Absolute delivery time", $datetime);
        Validator::validateDatetime("Absolute delivery time", $datetime);

        $this->deliveryTime = $datetime;
    }

    /**
     * Sets absolute message validity period in datetime string format.
     *
     * If the message has not been delivered to mobile phone when the
     * validity expires, the message will be deleted and never delivered.
     *
     * @param string    $datetime   absolute validity period in datetime string
     *                              format (yyyy-mm-dd hh:mm)
     *
     * @throws Exception    if validity period is @c null or
     *                      if validity period datetime format is invalid
     */
    public function setAbsoluteValidityPeriod($datetime) {
        Validator::validateNonNull("Absolute validity period", $datetime);
        Validator::validateDatetime("Absolute validity period", $datetime);

        $this->validityPeriod = $datetime;
    }

    /**
     * Allows setting error status of response message.
     *
     * Usable only with messages sent as responses to incoming messages
     * received from mobile phones. Error messages are used when the end-user
     * makes a mistake (for example, typing error) or the service otherwise
     * fails to serve the request.
     *
     * @param bool $error   @c true if the response error status should be set
     */
    public function setError($error) {
        $this->error = $error;
    }

    /**
     * @internal
     * Sets message keywords.
     *
     * @param string $keywords  message keywords
     */
    public function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    /**
     * @internal
     * Sets message operator.
     *
     * @param string $operator  message operator
     */
    public function setOperator($operator) {
        $this->operator = $operator;
    }

    /**
     * @internal
     * Sets message words following the keywords.
     *
     * @param array $parameters message parameters
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * Sets relative message delivery time in minutes.
     *
     * Message delivery can be delayed so that the message will be stored in
     * the Gateway for a specific time before sending it to mobile phones.
     *
     * @param int $minutes  relative delivery time in minutes
     *
     * @throws Exception    if delivery time is @c null or
     *                      if delivery time <= 0
     */
    public function setRelativeDeliveryTime($minutes) {
        Validator::validateNonNull("Relative delivery time", $minutes);
        Validator::validateNonNegative("Relative delivery time", $minutes);

        $this->deliveryTime = $minutes;
    }

    /**
     * Sets relative message validity period in minutes.
     *
     * If the message has not been delivered to mobile phone when the
     * validity expires, the message will be deleted and never delivered.
     *
     * @param int $minutes  relative validity period in minutes
     *
     * @throws Exception    if validity period is @c null or
     *                      if validity period <= 0
     */
    public function setRelativeValidityPeriod($minutes) {
        Validator::validateNonNull("Relative validity period", $minutes);
        Validator::validateNonNegative("Relative validity period", $minutes);

        $this->validityPeriod = $minutes;
    }

    /**
     * Sets message sender phone number or name.
     *
     * @param string $sender    service number, or custom name with maximum of
     *                          11 characters, or custom phone number with
     *                          maximum of 16 characters, or null if default
     *                          service number should be used
     *
     * @throws Exception    if sender is longer than 16 characters
     */
    public function setSender($sender) {
        if ($sender != null && strlen($sender) > self::MAX_SENDER_LENGTH) {
            throw new Exception(
                "Message sender cannot be longer than " .
                self::MAX_SENDER_LENGTH . " characters"
            );
        }

        $this->sender = $sender;
    }

    /**
     * Sets service that will be used to send the messages.
     *
     * This associates the message to correct service in message statistics.
     * Also, this is used to identify push services that allow sending billed
     * messages.
     *
     * @param string $serviceNumber service number of the service
     * @param string $serviceName   name (keywords) of the service
     *
     * @throws Exception    if service number or name is @c null
     */
    public function setService($serviceNumber, $serviceName) {
        Validator::validateNonNull("Service number", $serviceNumber);
        Validator::validateNonNull("Service name", $serviceName);

        $this->serviceName = $serviceName;
        $this->serviceNumber = $serviceNumber;
    }

    /**
     * Sets custom tag associated with this message. Number of messages sent
     * per tag are separately reported, so tags can be used to categorize
     * messages.
     * <p>
     * Note that tag support is disabled by default and enabling it must be
     * separately agreed with Labyrintti.
     * 
     * @param string    $tag    custom tag, may be null
     */
    public function setTag($tag) {
        $this->tag = $tag;
    }
    
    /**
     * @internal
     * Sets date and time of message arrival at operator message center.
     * 
     * @param string    $timestamp  date and time of message arrival at operator
     *                              message center
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }
    
    /**
     * @internal
     * Returns message type specific properties.
     * 
     * @return array
     */
    abstract protected function getProperties();
    
    /**
     * Sets message text.
     *
     * @param string $text
     *
     * @throws Exception    is text is @c null
     */
    abstract protected function setText($text);

}

/**
 * @internal
 * Message utils.
 */
class MessageUtils {

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Returns all response parameters from the given message. Parameters with
     * null values will be filtered.
     *
     * @param Message $message  message
     *
     * @return array
     */
    public static function getResponseParameters($message) {
        // get common reponse parameters
        $params = self::getCommonResponseParams($message);

        // add type specific response paramaters
        if ($message instanceof SmsMessage) {
            $params = array_merge($params, self::getSmsResponseParams($message));
        } elseif ($message instanceof MmsMessage) {
            $params = array_merge($params, self::getMmsResponseParams($message));
        }

        return array_filter($params, "strlen");
    }

    /**
     * Returns all send parameters from the given message. Parameters with
     * null values will be filtered.
     *
     * @param Message $message  message
     *
     * @return array
     */
    public static function getSendParameters($message) {
        // get common send parameters
        $params = self::getCommonSendParams($message);

        // add type specific response paramaters
        if ($message instanceof SmsMessage) {
            $params = array_merge($params, self::getSmsSendParams($message));
        } elseif ($message instanceof MmsMessage) {
            $params = array_merge($params, self::getMmsSendParams($message));
        }

        return array_filter($params, "strlen");
    }

    /**
     * Returns common response parameters for the given message.
     *
     * @param Message $message  message
     *
     * @return array
     */
    private static function getCommonResponseParams($message) {
        return array(
            'delivery'  => $message->getDeliveryTime(),
            'error'     => Utils::getFlagParamValue($message->isError(), "yes", "no"),
            'tag'       => $message->getTag(),
            'validity'  => $message->getValidityPeriod()
        );
    }

    /**
     * Returns common send parameters for the given message.
     *
     * @param Message $message  message
     *
     * @return array
     */
    private static function getCommonSendParams($message) {
        return array(
            'delivery'  => $message->getDeliveryTime(),
            'dests'     => implode(",", $message->getRecipients()),
            'service'   => $message->getServiceName(),
            'source'    => $message->getServiceNumber(),
            'tag'       => $message->getTag(),
            'text'      => $message->getText(),
            'validity'  => $message->getValidityPeriod()
        );
    }

    /**
     * Returns MMS message specific response parameters for the given message.
     *
     * @param MmsMessage $message   MMS message
     *
     * @return array
     */
    private static function getMmsResponseParams($message) {
        return array(
            'adaptations'   => Utils::getFlagParamValue($message->getAdaptations(), "yes", "no"),
            'smil'          => Utils::getFlagParamValue($message->getAutoSmil(), "yes", "no"),
            'subject'       => $message->getSubject(),
            'text'          => $message->getText(),
            'type'          => "MMS"
        );
    }

    /**
     * Returns MMS message specific send parameters for the given message.
     *
     * @param MmsMessage $message   MMS message
     *
     * @return array
     */
    private static function getMmsSendParams($message) {
        return array(
            'adaptations'   => Utils::getFlagParamValue($message->getAdaptations(), "yes", "no"),
            'smil'          => Utils::getFlagParamValue($message->getAutoSmil(), "yes", "no"),
            'subject'       => $message->getSubject()
        );
    }

    /**
     * Returns SMS message specific response parameters for the given message.
     *
     * @param SmsMessage $message   SMS message
     *
     * @return array
     */
    private static function getSmsResponseParams($message) {
        $params = array(
            'binary'        => $message->getData(),
            'class'         => Utils::getFlagParamValue($message->isFlash(), "flash", "normal"),
            'concatenate'   => Utils::getFlagParamValue($message->isConcatenation(), "yes", "no"),
            'header'        => $message->getHeader(),
            'type'          => "SMS",
            'unicode'       => Utils::getFlagParamValue($message->isUnicode(), "yes", "no"),
            'wap-text'      => $message->getWapPushText(),
            'wap-url'       => $message->getWapPushUrl()
        );

        $text = $message->getText();
        if (isset($text)) {
            $text = str_replace("\\", "\\\\", $text);
            $text = str_replace("\r", "\\r", $text);
            $text = str_replace("\n", "\\n", $text);

            $params['text'] = $text;
        }

        return $params;
    }

    /**
     * Returns SMS message specific send parameters for the given message.
     *
     * @param SmsMessage $message   SMS message
     *
     * @return array
     */
    private static function getSmsSendParams($message) {
        return array(
            'binary'        => $message->getData(),
            'class'         => Utils::getFlagParamValue($message->isFlash(), "flash", "normal"),
            'concatenate'   => Utils::getFlagParamValue($message->isConcatenation(), "yes", "no"),
            'header'        => $message->getHeader(),
            'source-name'   => $message->getSender(),
            'unicode'       => Utils::getFlagParamValue($message->isUnicode(), "yes", "no"),
            'wap-text'      => $message->getWapPushText(),
            'wap-url'       => $message->getWapPushUrl()
        );
    }

}

/**
 * MMS message that has been received from a mobile phone or is going to be sent
 * to mobile phones.
 *
 * Maximum size of one MMS message, limited by the mobile
 * operators, is currently 300 kilobytes.
 */
class MmsMessage extends Message {

    /** @internal Maximum subject length. */
    const MAX_SUBJECT_LENGTH = 40;
    
    /** @internal Plain text content type.  */
    const TEXT_PLAIN = "text/plain";

    /**
     * Message content adaptation by operator.
     * @var bool $adaptations
     */
    private $adaptations;

    /**
     * Automatic SMIL creation.
     * @var bool $autoSmil
     */
    private $autoSmil;

    /**
     * Multimedia objects array.
     * @var array $objects
     */
    private $objects;

    /**
     * Message subject.
     * @var string $subject
     */
    private $subject;

    /**
     * Constructs a new MMS message.
     *
     * @param string $recipient message recipient
     * @param string $text      message text
     */
    public function __construct($recipient = null, $text = null) {
        parent::__construct($recipient);
        $this->adaptations = null;
        $this->autoSmil = null;
        $this->objects = array();
        $this->subject = null;
        $this->text = $text;
    }

    /**
     * Adds the given object to this MMS message.
     *
     * The object is added to end of the message.
     *
     * @param MmsObject $object MMS object to add
     *
     * @throws Exception    if object is null or
     *                      if object type is invalid
     */
    public function addObject($object) {
        Validator::validateNonNull("MmsObject", $object);

        if (!($object instanceof MmsObject)) {
            throw new Exception("Unsupported MMS object type: " . get_class($object));
        }

        $this->objects[] = $object;
    }

    /**
     * @internal
     * Returns @c true if content adaptation is enabled or @c null if not
     * specified.
     *
     * @return bool
     */
    public function getAdaptations() {
        return $this->adaptations;
    }

    /**
     * @internal
     * Returns @c true if automatic SMIL generation is enabled or @c null if not
     * specified.
     *
     * @return bool
     */
    public function getAutoSmil() {
        return $this->autoSmil;
    }

    /**
     * Returns MMS message objects.
     *
     * Return value is a MmsObject @c array.
     *
     * @return array
     */
    public function getObjects() {
        return $this->objects;
    }

    /**
     * Returns subject of the MMS message or @c null if not specified.
     *
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Returns content of the MMS message text object or @c null if not specified.
     *
     * @return string
     */
    public function getText() {
        if ($this->text != null) {
            return $this->text;
        }

        foreach ($this->objects as $object) {
            if ($object->getContentType() == self::TEXT_PLAIN) {
                return $object->getContent();
            }
        }

        return null;
    }

    /**
     * Enables or disables automatic adaptation of MMS content to better suit
     * recipient mobile phones.
     * 
     * If this property is not specified, the default value configured in the
     * Gateway account settings will be used (enabled by default).
     *
     * @param bool  $enabled    @c true if MMS content may be adapted for
     *                          receiving mobile phones
     */
    public function setAdaptations($enabled) {
        $this->adaptations = $enabled;
    }

    /**
     * Enables or disables automatic generation of SMIL file if one is not
     * available in message objects.
     *
     * SMIL (Synchronized Multimedia Integration Language) presentation file
     * tells mobile phones how to display the other files (objects) in an MMS
     * message. See http://www.w3.org/AudioVideo/ for more information.
     *
     * If this property is not specified, the default value configured in the
     * Gateway account settings will be used (enabled by default).
     *
     * @param bool $autoSmil    @c true if SMIL file should be automatically
     *                          generated when missing
     */
    public function setAutoSmil($autoSmil) {
        $this->autoSmil = $autoSmil;
    }

    /**
     * Sets the subject of the MMS message.
     *
     * Subject is not displayed on all mobile phones.
     *
     * @param string $subject   message subject, maximum of 40 characters
     *
     * @throws Exception    if subject is null,
     *                      if subject is longer than 40 characters
     */
    public function setSubject($subject) {
        Validator::validateNonNull("MmsMessage subject", $subject);

        if (strlen($subject) > self::MAX_SUBJECT_LENGTH) {
            throw new Exception(
                "MmsMessage subject cannot be longer than " .
                self::MAX_SUBJECT_LENGTH . " characters"
            );
        }

        $this->subject = $subject;
    }

    /**
     * Sets message text.
     *
     * An object containing the text is automatically created and attached to
     * the message.
     *
     * @param string $text  message text
     *
     * @throws Exception    if text is null
     */
    public function setText($text) {
        Validator::validateNonNull("MmsMessage text", $text);

        $this->text = $text;
    }

    /**
     * @internal
     * Returns MmsMessage properties for debugging.
     * 
     * @return array
     */
    protected function getProperties() {
        return array(
            "text" => var_export($this->text, true),
            "objects" => "[" . implode(", ", $this->objects) . "]",
            "subject" => var_export($this->subject, true),
            "autoSmil" => var_export((bool)$this->autoSmil, true),
            "adaptations" => var_export((bool)$this->adaptations, true)
        );
    }

}

/**
 * Represents one media file attached to an MMS message.
 *
 * At least content must be specified for every object, other properties have
 * reasonable default values.
 *
 * If the default filename (unnamed) and/or content type
 * (application/octet-stream) are used, Gateway will detect the type of the file
 * and replace filename and/or content type with better ones.
 */
class MmsObject {

    /** @internal Default filename. */
    const FILENAME = "unnamed";

    /** @internal Default text content type. */
    const CONTENT_TYPE_TEXT = "text/plain";
    /** @internal Default binary content types. */
    const CONTENT_TYPE_BINARY = "application/octet-stream";

    /** @internal Binary content. */
    const FORMAT_BINARY = "binary";
    /** @internal File content. */
    const FORMAT_FILE = "file";
    /** @internal Text content. */
    const FORMAT_TEXT = "text";

    /**
     * Media file content.
     * @var string $content
     */
    private $content;
    
    /**
     * Media file content type.
     * @var string $contentType
     */
    private $contentType;

    /**
     * Media file name.
     * @var string $filename
     */
    private $filename;

    /**
     * Media file format.
     * @var string $format
     */
    private $format;

    /**
     * Constructs a new MMS object without any content.
     */
    public function __construct() {}

    /**
     * Returns a multi-line string representation of this object. Useful for
     * debugging.
     * 
     * @return string
     */
    public function __toString() {
        $properties = array(
            "size" => (!is_null($this->getContentLength()) ? $this->getContentLength() . " bytes" : var_export($this->getContentLength(), true)),
            "filename" => var_export($this->filename, true),
            "contentType" => var_export($this->contentType, true)
        );
        
        if ($this->isText()) {
            $properties = array_merge(array("text" => var_export($this->content, true)), $properties);
        }
                
        $str = get_class($this) . "[" . PHP_EOL;
        foreach ($properties as $key => $value) {
            $str .= "  " . $key . "=" . $value . PHP_EOL;
        }
        $str .= "]";

        return $str;
    }
 
    /**
     * Returns object content or @c null if not specified.
     *
     * Return value for user defined objects is a binary byte string, filename
     * or text content string depending on used content setter method.
     *
     * For objects in a message received from the Gateway the return value is
     * always the contents of the file.
     *
     * @return mixed
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Returns object content length or @c null if content is not set.
     *
     * @return int
     */
    public function getContentLength() {
        if ($this->isBinary() || $this->isText()) {
            return strlen($this->content);
        } elseif ($this->isFile()) {
            return filesize($this->content);
        }

        return null;
    }

    /**
     * Returns MIME type of the object or @c null if not specified.
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Returns filename of the object or @c null if not specified.
     *
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }
    
    /**
     * @internal
     * Returns @c true if the object content is a binary byte string.
     *
     * @return bool
     */
    public function isBinary() {
        if ($this->format == self::FORMAT_BINARY) {
            return true;
        }

        return false;
    }

    /**
     * @internal
     * Returns @c true if the object content is a filename.
     *
     * @return bool
     */
    public function isFile() {
        if ($this->format == self::FORMAT_FILE) {
            return true;
        }

        return false;
    }
    
    /**
     * @internal
     * Returns @c true if the object content is text content string.
     *
     * @return bool
     */
    public function isText() {
        if ($this->format == self::FORMAT_TEXT) {
            return true;
        }

        return false;
    }

    /**
     * Sets object content as a binary byte string.
     * <p>
     * If filename is not specified, uses @c unnamed. If content-type is
     * not specified, uses @c application/octet-stream.
     *
     * @param string    $bytes  binary byte string
     * 
     * @throws Exception    if binary content is @c null
     */
    public function setBinaryContent($bytes) {
        Validator::validateNonNull("MmsObject binary content", $bytes);

        $this->content = $bytes;
        
        $this->format = self::FORMAT_BINARY;
        if (is_null($this->filename)) {
            $this->filename = self::FILENAME;
        }
        if (is_null($this->contentType)) {
            $this->contentType = self::CONTENT_TYPE_BINARY;
        }
    }

    /**
     * Sets object MIME type.
     *
     * @param string $contentType   object MIME type
     *
     * @throws Exception    if content type is @c null
     */
    public function setContentType($contentType) {
        Validator::validateNonNull("MmsObject content type", $contentType);

        $this->contentType = $contentType;
    }

    /**
     * Sets object content as a file by the given filename. This method allows small memory usage since it does not
     * load the entire file into memory.
     * <p>
     * If filename is not specified, given filename is used. If content-type is not specified, uses
     * @c application/octet-stream.
     *
     * @param string    $file   filename
     * 
     * @throws Exception    if filename is @c null
     */
    public function setFileContent($file) {
        Validator::validateNonNull("MmsObject file content", $file);

        $this->content = $file;
        
        $this->format = self::FORMAT_FILE;
        if (is_null($this->filename)) {
            $this->filename = basename($file);
        }
        if (is_null($this->contentType)) {
            $this->contentType = self::CONTENT_TYPE_BINARY;
        }
    }

    /**
     * Sets object filename.
     *
     * @param string $filename  object filename
     *
     * @throws Exception    if filename is null
     */
    public function setFilename($filename) {
        Validator::validateNonEmpty("MmsObject filename", $filename);

        $this->filename = $filename;
    }

    /**
     * Sets object content as a text content string.
     * <p>
     * If filename is not specified, uses @c unnamed. If content-type is
     * not specified, uses @c text/plain.
     *
     * @param string    $text   text content string
     * 
     * @throws Exception    if text content is @c null
     */
    public function setTextContent($text) {
        Validator::validateNonNull("MmsObject text content", $text);

        $this->content = $text;
        
        $this->format = self::FORMAT_TEXT;
        if (is_null($this->filename)) {
            $this->filename = self::FILENAME;
        }
        if (is_null($this->contentType)) {
            $this->contentType = self::CONTENT_TYPE_TEXT;
        }
    }

}

/**
 * @internal
 * MMS object utilities.
 */
class MmsObjectUtils {

    /**
     * Class cannot be instantiated.
     */
    private function __contruct() {}

    /**
     * Reads and returns an array of MMS objects from the $_FILES table.
     * 
     * @return array
     */
    public static function readMmsObjectsFromRequest() {
        // create empty MMS object array
        $objects = array();

        // read attached files and create MMS objects out of them
        foreach (array_keys($_FILES) as $id) {
            // check file for an upload error and skip if any
            if ($_FILES[$id]['error'] != UPLOAD_ERR_OK) {
                continue;
            }

            // create new MMS object
            $object = new MmsObject();

            // set MMS object filename
            $filename = Utils::stripMagicQuotes($_FILES[$id]['name']);
            $object->setFilename($filename);

            // set MMS object content type
            $type = Utils::stripMagicQuotes($_FILES[$id]['type']);
            $object->setContentType($type);

            // read MMS object content
            $file = fopen($_FILES[$id]['tmp_name'], "rb");
            $content = fread($file, $_FILES[$id]['size']);
            fclose($file);
            
            // detect content type and set MMS object content accordingly
            $isBinaryContent = (strpos($type, "text") === false && strpos($type, "smil") === false);
            if ($isBinaryContent) {
                $object->setBinaryContent($content);
            } else {
                $object->setTextContent($content);
            }

            // add MMS object to array
            $objects[] = $object;
        }

        return $objects;
    }

}

/**
 * Contains Labyrintti Gateway monitoring result.
 */
class MonitorResult {
    
    /**
     * Gateway response time in milliseconds.
     * @var float $responseTime
     */
    private $responseTime;
    
    /**
     * @internal
     * Constructs a monitor result object.
     * 
     * @param float $responseTime   Gateway response time in milliseconds
     */
    public function __construct($responseTime) {
        $this->responseTime = $responseTime;
    }

    /**
     * Returns the time in milliseconds it took to connect the Gateway and read
     * a response.
     * 
     * Note that usually establishing the TCP connection takes most of the
     * time. If a persistent TCP connection to the Gateway is already open from
     * an earlier monitoring or message sending request, response time is
     * expected to be clearly smaller.
     * 
     * @return float
     */
    public function getResponseTime() {
        return $this->responseTime;
    }
    
}

/**
 * Contains message sending result for one recipient.
 */
class SendResult {

    /**
     * Description of the success or failure.
     * @var string $description
     */
    private $description;

    /**
     * Error code.
     * @var int $error
     */
    private $error;

    /**
     * Message count.
     * @var int $messageCount
     */
    private $messageCount;

    /**
     * Recipient phone number exactly in the same format it was originally specified.
     * @var string $originalRecipient
     */
    private $originalRecipient;

    /**
     * Recipient phone number.
     * @var string $recipient
     */
    private $recipient;

    /**
     * @internal
     * Constructs a send result object.
     *
     * @param string    $recipient          formatted recipient phone number
     * @param string    $originalRecipient  unformatted recipient phone number
     * @param int       $error              send error code, @c 0 if success
     * @param int       $messageCount       number of messages sent
     * @param string    $description        description of the success or failure
     */
    public function __construct($recipient, $originalRecipient, $error,
        $messageCount, $description) {
        $this->recipient = $recipient;
        $this->originalRecipient = $originalRecipient;
        $this->error = $error;
        $this->messageCount = $messageCount;
        $this->description = $description;
    }

    /**
     * Returns description text of the send result.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Returns reason for a failed message sending.
     *
     * <p><b>Return value is one of the following:</b></p>
     * <ul>
     *   <li>@c 0 - <b>Success:</b> No error has occurred.</li>
     *   <li>@c 1 - <b>Unknown error:</b> Error whose reason is not known or
     *     specified.</li>
     *   <li>@c 2 - <b>Invalid recipient:</b> Recipient phone number syntax is
     *     invalid.</li>
     *   <li>@c 3 - <b>Duplicate recipient:</b> The same recipient phone number
     *     has been specified multiple times.</li>
     *   <li>@c 4 - <b>Unallowed recipient:</b> Recipient phone number is not
     *     allowed in user account configuration.</li>
     *   <li>@c 5 - <b>Routing error:</b> There is no operator route that
     *     supports sending to the recipient phone number.</li>
     * </ul>
     *
     * @return int
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Returns the precise number of messages actually sent.
     *
     * SmsMessage instances containing long text content can require multiple
     * real SMS messages.
     *
     * @return int
     */
    public function getMessageCount() {
        return $this->messageCount;
    }

    /**
     * Returns the recipient phone number exactly in the same format it was
     * originally specified.
     *
     * Return value is phone number in the format it was given. For example,
     * (040) 1234 567.
     *
     * @return string
     */
    public function getOriginalRecipient() {
        return $this->originalRecipient;
    }

    /**
     * Returns the recipient phone number in international format.
     *
     * For invalid phone numbers that the Gateway could not format, this will
     * be in the original non-modified form.
     *
     * @return string
     */
    public function getRecipient() {
        return $this->recipient;
    }

    /**
     * Returns true if the message sending failed.
     *
     * @return bool
     */
    public function isFailed() {
        if ($this->error !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the message was successfully sent.
     *
     * @return bool
     */
    public function isSent() {
        if ($this->error === 0) {
            return true;
        }

        return false;
    }

}

/**
 * SMS message that has been received from a mobile phone or is going to be
 * sent to mobile phones.
 *
 * Note that one SmsMessage instance can actually consist of multiple real
 * SMS messages if the message data does not fit into one SMS message. Maximum
 * data size for a single SMS message is 140 bytes, or 160 characters using the
 * default 7-bit GSM alphabet.
 *
 * <h3>How many characters fit in one SMS message?</h3>

 * One SMS message has space for 140 bytes. With the default 7-bit GSM
 * alphabet, this normally means 160 characters (140*8/7). But some special
 * characters require 14 bits (2 characters):
 *
 * <ul>
 *   <li>form feed (0x0C)</li>
 *   <li>caret, ^ (0x5E)</li>
 *   <li>opening brace, { (0x7B)</li>
 *   <li>closing brace, } (0x7D)</li>
 *   <li>backslash, \ (0x5C)</li>
 *   <li>opening bracket, [ (0x5B)</li>
 *   <li>closing bracket, ] (0x5D)</li>
 *   <li>tilde, ~ (0x7E)</li>
 *   <li>vertical bar, | (0x7C)</li>
 *   <li>Euro symbol, \&euro; (0x20AC UTF-8, 0xA4 ISO-8859-15)</li>
 * </ul>
 *
 * When using Unicode, SMS always uses UTF-16, meaning a maximum of 70
 * characters (140/2) per SMS message.
 *
 * <h3>How many characters fit in one concatenated (long) message?</h3>
 *
 * If message text does not fit into one SMS message, multiple SMS messages
 * will be used, each charged separately.
 *
 * When message text requires two or more SMS messages, a special
 * concatenation header reserves 6 bytes from each SMS message, leaving 134
 * bytes for message text. With the default 7-bit GSM alphabet, this normally
 * means 153 characters (134*8/7). Note however, that some special characters
 * will require the space of two normal characters (see "How many characters fit
 * in one SMS message?").
 *
 * With concatenated Unicode (UTF-16) messages, there is space for 67
 * characters (134/2) per SMS message.
 */
class SmsMessage extends Message {

    /** @internal Maximum header length. */
    const MAX_HEADER_LENGTH = 140;

    /**
     * Message concatenation.
     * @var bool $concatenation
     */
    private $concatenation;

    /**
     * Message binary data. Either this or 'text' can be specified, not both.
     * @var string $data
     */
    private $data;

    /**
     * Message flash.
     * @var bool $flash
     */
    private $flash;

    /**
     * Message user data header.
     * @var string $header
     */
    private $header;

    /**
     * Message unicode.
     * @var bool $unicode
     */
    private $unicode;

    /**
     * WAP Push description text.
     * @var string $wapPushText
     */
    private $wapPushText;

    /**
     * WAP Push SI link.
     * @var string $wapPushUrl
     */
    private $wapPushUrl;

    /**
     * Constructs a new SMS message.
     *
     * @param string $recipient recipient phone number
     * @param string $text      message text
     */
    public function __construct($recipient = null, $text = null) {
        parent::__construct($recipient);
        $this->concatenation = null;
        $this->data = null;
        $this->flash = null;
        $this->header = null;
        $this->text = $text;
        $this->unicode = null;
        $this->wapPushText = null;
        $this->wapPushUrl = null;
    }

    /**
     * Returns message data as binary hexadecimal ASCII string or @c null if not
     * specified.
     *
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Returns message data header as binary hexadecimal ASCII string @c null if
     * not specified.
     *
     * @return string
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * Returns message text or @c null if not specified.
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @internal
     * Returns WAP Push description text or @c null if not specified.
     *
     * @return string
     */
    public function getWapPushText() {
        return $this->wapPushText;
    }

    /**
     * @internal
     * Returns WAP Push SI link or @c null if not specified.
     *
     * @return string
     */
    public function getWapPushUrl() {
        return $this->wapPushUrl;
    }

    /**
     * Returns @c true if the message contains binary data.
     *
     * Binary data is retrieved using getData.
     *
     * @return bool
     */
    public function isBinary() {
        return isset($this->data);
    }

    /**
     * @internal
     * Return @c true if message concatenation is enabled.
     *
     * @return bool
     */
    public function isConcatenation() {
        return $this->concatenation;
    }

    /**
     * Returns @c true if the message is an instant message.
     *
     * Instant messages are displayed automatically by mobile phones after
     * receiving them.
     *
     * Default is @c false (normal message).
     *
     * @return bool
     */
    public function isFlash() {
        return $this->flash;
    }

    /**
     * Returns @c true if the message contains text using UTF-16 character encoding.
     *
     * Any text is retrieved using getText.
     *
     * @return bool
     */
    public function isUnicode() {
        return $this->unicode;
    }

    /**
     * Enables or disables message concatenation.
     *
     * If @c true, multiple SMS messages are concatenated so that they are
     * seen as one long message in receiving mobile phones. Also, concatenation
     * is required with logos, ring tones and similar that use more than one
     * SMS message. Concatenation is performed by adding a special concatenation
     * data header in front of normal data header and data.
     *
     * If this property is not specified, the default value configured in the
     * Gateway account settings will be used (enabled by default).
     *
     * @param bool $concatenation   @c true if multiple SMS messages are seen as
     *                              one long message in mobile phones, @c false
     *                              if seen as separate messages
     */
    public function setConcatenation($concatenation) {
        $this->concatenation = $concatenation;
    }

    /**
     * Sets binary message data.
     *
     * Used for sending logos, ring tones and similar. This allows 140 bytes
     * per message, but if the message contains more than 140 bytes and
     * concatenation is enabled, concatenation user data header takes some
     * space from the start of each SMS message.
     *
     * @param string $data  message data coded as binary hexadecimal ASCII
     *                      string, not limited to 140 bytes
     *
     * @throws Exception    if data is null
     */
    public function setData($data) {
        Validator::validateNonNull("SmsMessage data", $data);

        $this->data = $data;
        $this->text = null;
        $this->wapPushText = null;
        $this->wapPushUrl = null;
    }

    /**
     * Enables or disables instant message.
     *
     * Instant messages are displayed automatically by mobile phones after
     * receiving them.
     *
     * @param bool $enabled @c true if instant message, @c false if normal message
     */
    public function setFlash($enabled) {
        $this->flash = $enabled;
    }

    /**
     * Sets message user data header.
     *
     * User data header is used with logos, ring tones and similar. User data
     * header uses the same space as message data and thus reduces the amount of
     * data allowed in one SMS message. The same header is used with every
     * message part.
     *
     * Note that the Gateway can automatically generate concatenation header
     * if enabled with setConcatenation.
     *
     * @param string $header    message data header as binary hexadecimal ASCII
     *                          string
     *
     * @throws Exception    if header is @c null,
     *                      if header is too long
     */
    public function setHeader($header) {
        Validator::validateNonNull("SmsMessage header", $header);

        if (strlen($header) > (self::MAX_HEADER_LENGTH)) {
            throw new Exception(
                "SmsMessage header cannot be longer than " .
                self::MAX_HEADER_LENGTH . " characters"
            );
        }

        $this->header = $header;
        $this->wapPushText = null;
        $this->wapPushUrl = null;
    }

    /**
     * Sets message data using the default 7-bit GSM alphabet.
     *
     * This allows a maximum of 160 characters per message, but if the
     * message contains more than 160 characters and concatenation is enabled,
     * concatenation user data header takes some space from the start of each
     * SMS message.
     *
     * @param string $text  message data in plain text, not limited to 160
     *                      characters
     *
     * @throws Exception    if text is null
     */
    public function setText($text) {
        Validator::validateNonNull("SmsMessage text", $text);
        
        $this->text = $text;
        $this->data = null;
        $this->wapPushText = null;
        $this->wapPushUrl = null;
    }

    /**
     * Sets message to use the UTF-16 (former UCS-2) character encoding.
     *
     * With UTF-16, it is possible to use any known language in the SMS message.
     * UTF-16 allows 70 characters per message, but if the message contains more
     * than 70 characters and concatenation is enabled, concatenation user data
     * header takes some space from the start of each SMS message.
     *
     * @param bool $enabled     @c true for UTF-16, @c false for default 7-bit
     *                          GSM alphabet
     */
    public function setUnicode($enabled) {
        $this->unicode = $enabled;
    }

    /**
     * Sets WAP Push SI link and description text.
     *
     * WAP Push is a special SMS message that can be used to deliver URLs to
     * mobile phones. If this method is used, no other text/binary data or
     * header can be specified.
     *
     * This also enables @link #setConcatenation concatenation@endlink.
     *
     * @param string $url           absolute URL for phone browser
     * @param string $description   optional link description, may be @c null
     *
     * @throws Exception    if WAP Push URL is null
     */
    public function setWapPush($url, $description = null) {
        Validator::validateNonNull("WAP Push URL", $url);

        $this->wapPushUrl = $url;
        $this->wapPushText = $description;
        $this->concatenation = true;
        $this->data = null;
        $this->header = null;
        $this->text = null;
        $this->unicode = false;
    }
    
    /**
     * @internal
     * Returns SmsMessage properties for debugging.
     * 
     * @return array
     */
    protected function getProperties() {
        return array(
            "text" => var_export($this->text, true),
            "data" => var_export($this->data, true),
            "wapPushUrl" => var_export($this->wapPushUrl, true),
            "wapPushText" => var_export($this->wapPushText, true),
            "unicode" => var_export((bool)$this->unicode, true),
            "header" => var_export($this->header, true),
            "flash" => var_export((bool)$this->flash, true),
            "concatenation" => var_export((bool)$this->concatenation, true)
        );
    }

}

/**
 * @internal
 * General utils.
 */
class Utils {

    /**
     * Returns string value of the given flag parameter or null if not set.
     *
     * @param mixed     $param  flag parameter
     * @param string    $tVal   true value
     * @param string    $fVal   false value
     *
     * @return mixed
     */
    public static function getFlagParamValue($param, $tVal, $fVal) {
        return (!is_null($param) ? ($param ? $tVal : $fVal) : null);
    }

    /**
     * Strips slashes from the given string if the magic_quotes_gpc is set.
     *
     * @param string    $str    string to strip slashes from
     *
     * @return string
     */
    public static function stripMagicQuotes($str) {
        return (get_magic_quotes_gpc() ? stripslashes($str) : $str);
    }

}

/**
 * @internal
 * Validator.
 */
class Validator {

    /**
     * Throws an exception if the given MmsObject array contains an invalid
     * MmsObject.
     * 
     * @param array $objects    MmsObject array
     * 
     * @throws InvalidMmsObjectException    if one of MmsObjects content file
     *                                      is not found,
     *                                      if one of MmsObjects has no content
     */
    public static function validateMmsMessageObjects($objects) {
        foreach ($objects as $object) {
            if ($object->isFile()) {
                if (!file_exists($object->getContent())) {
                    throw new InvalidMmsObjectException(
                        $object,
                        "MmsObject content file not found"
                    );
                }
            } elseif (!($object->isText() || $object->isBinary())) {
                throw new InvalidMmsObjectException(
                    $object,
                    "MmsObject has no content"
                );
            }
        }
    }
    
    /**
     * Throws an exception if the given value is @c null or empty.
     *
     * @param string $name  name of the value
     * @param string $value value to validate
     *
     * @throws Exception    if value is null or empty
     */
    public static function validateNonEmpty($name, $value) {
        self::validateNonNull($name, $value);
        if (!strlen($value)) {
            throw new Exception($name . " cannot be empty");
        }
    }

    /**
     * Throws an exception if the given message doesn't have content.
     *
     * @param string    $name       name of the message
     * @param Message   $message    SmsMessage or MmsMessage to validate
     *
     * @throws Exception    if message doesn't have content
     */
    public static function validateNonEmptyMessage($name, $message) {
        if ($message instanceof SmsMessage) {
            if (is_null($message->getText()) && is_null($message->getData()) &&
                is_null($message->getWapPushUrl())) {
                throw new Exception($name . " has no content");
            }
        } elseif ($message instanceof MmsMessage) {
            if (is_null($message->getText()) && !sizeof($message->getObjects())) {
                throw new Exception($name . " has no content");
            }
        }
    }

    /**
     * Throws an exception if the given value is negative.
     *
     * @param string $name  name of the value
     * @param string $value value to validate
     *
     * @throws Exception    if value is negative
     */
    public static function validateNonNegative($name, $value) {
        if ($value <= 0) {
            throw new Exception($name . " cannot be negative or zero");
        }
    }

    /**
     * Throws an exception if the given value is @c null.
     *
     * @param string $name  name of the value
     * @param string $value value to validate
     *
     * @throws Exception    if value is null
     */
    public static function validateNonNull($name, $value) {
        if (is_null($value)) {
            throw new Exception($name . " cannot be null");
        }
    }

    /**
     * Throws an exception if the given port is out of range.
     *
     * @param string $name  name of the port
     * @param string $value port to validate
     *
     * @throws Exception    if port is out of range
     */
    public static function validatePort($name, $port) {
        if ($port < 0 || $port > 65535) {
            throw new Exception($name . " must be between 0 and 65535");
        }
    }

    /**
     * Throws an exception if the datetime format is invalid.
     *
     * @param string    $name       name of the time value
     * @param mixed     $datetime   datetime string to validate
     *
     * @throws Exception    if the datetime format is invalid
     */
    public static function validateDatetime($name, $datetime) {
        $dtPattern = "/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$/";
        if (!preg_match($dtPattern, $datetime)) {
            throw new Exception(
                $name . " format is invalid: " . $datetime .
                " (should be yyyy-mm-dd hh:mm)"
            );
        }
    }

}
?>