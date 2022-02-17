<?php

/**
 * @link http://www.hosannahighertech.co.tz/
 * @copyright Copyright (c) 2021 Hosanna Higher Technologies Co. Ltd
 * @license Apache 2.0
 */

namespace hosanna\sms\beem;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * A Library for sending SMS using BEEM API, see https://beem.africa
 * This class is the core part of sending SMS. It takes the messages and send them over he API
 *
 */
class Client
{
    private HttpClient $httpClient;
    private string $error = '';

    /***
     * @var logger Log
     */
    private $logger = null;

    public function __construct(string $apiKey, string $secretKey, string $logPath = '')
    {
        $auth = base64_encode("{$apiKey}:{$secretKey}");

        $options = [
            // Base URI is used with relative requests
            'base_uri' => 'https://apisms.beem.africa',
            'timeout'  => 300.0, //5min
            'headers' => [
                'Authorization' => "Basic {$auth}",
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($logPath)) {
            // create a log channel
            $this->logger = new Logger('sms-logger');
            $this->logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    new Logger('Logger'),
                    new MessageFormatter('{req_body} - {res_body}')
                )
            );

            $options['handler'] = $stack;
        }

        $this->httpClient = new HttpClient($options);
    }

    public function getLastError(): string
    {
        return $this->error;
    }

    public function send(Message $message): array
    {
        $body = [
            'source_addr' => $message->getSender(),
            'schedule_time' => '',
            'encoding' => '0',
            'message' => $message->getMessage(),
            'recipients' => $message->getRecipients(),
        ];

        try {
            $response = $this->httpClient->request('POST', '/v1/send', [
                'body' => json_encode($body),
            ]);

            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

            if ($code != 200) {
                $this->error = "Code: {$code} - Reason: {$reason}";
                return [];
            }

            $body = (string)$response->getBody();
            $json = json_decode($body, true);
            if (!isset($json['data'])) {
                $this->error = "Invalid response";
                return [];
            }
            return $json['data'];
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return [];
        }
    }

    public function getBalance(): string
    {
        try {
            $response = $this->httpClient->request('GET', '/public/v1/vendors/balance');

            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

            if ($code != 200) {
                $this->error = "Code: {$code} - Reason: {$reason}";
                return '';
            }
            $body = (string)$response->getBody();
            $json = json_decode($body, true);
            if (!isset($json['data']['credit_balance'])) {
                $this->error = "Invalid response";
                return '';
            }
            return $json['data']['credit_balance'];
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return '';
        }
    }

    public function checkStatus($messageId, $senderId, $country): array
    {
        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneNumberUtil->parse($senderId, $country);
            if ($phoneNumberUtil->isValidNumber($phoneNumberObject)) {
                $senderId = str_replace('+', '', $phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164));
            }

            if (empty($senderId)) return [];

            $url = "https://dlrapi.beem.africa/public/v1/delivery-reports?dest_addr={$senderId}&request_id={$messageId}";
            $response = $this->httpClient->request('GET', $url);

            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

            if ($code == 404) {
                return [
                    'dest_addr' => $senderId,
                    'status' => 'NOTFOUND',
                    'request_id' => $messageId,
                ];
            } else if ($code != 200) {
                $this->error = "Code: {$code} - Reason: {$reason}";
                return [];
            }
            $body = (string)$response->getBody();
            $json = json_decode($body, true);
            if (!isset($json['data'])) {
                $this->error = "Invalid response";
                return [];
            }
            return $json['data'];
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return [];
        }
    }
}
