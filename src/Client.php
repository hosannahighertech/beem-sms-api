<?php

/**
 * @link http://www.hosannahighertech.co.tz/
 * @copyright Copyright (c) 2021 Hosanna Higher Technologies Co. Ltd
 * @license Apache 2.0
 */

namespace hosanna\sms\beem;

use Exception;
use GuzzleHttp\Client as HttpClient;

/**
 * A Library for sending SMS using BEEM API, see https://beem.africa
 * This class is the core part of sending SMS. It takes the messages and send them over he API
 *
 * Example:
 *
 * ```php
 * use yii\bootstrap\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal']);
 *
 * // Form field without label
 * echo $form->field($model, 'demo', [
 *     'inputOptions' => [
 *         'placeholder' => $model->getAttributeLabel('demo'),
 *     ],
 * ])->label(false);
 *
 * ```
 *
 */
class Client
{
    private HttpClient $httpClient;
    private string $error = '';

    public function __construct(string $apiKey, string $secretKey)
    {
        $auth = base64_encode("{$apiKey}:{$secretKey}");
        $this->httpClient = new HttpClient([
            // Base URI is used with relative requests
            'base_uri' => 'https://apisms.beem.africa',
            'timeout'  => 300.0, //5min
            'headers' => [
                'Authorization' => "Basic {$auth}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getLastError(): string
    {
        return $this->error;
    }

    public function send(Message $message): bool
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
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getBalance(): string|false
    {
        try {
            $response = $this->httpClient->request('GET', '/public/v1/vendors/balance');

            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

            if ($code != 200) {
                $this->error = "Code: {$code} - Reason: {$reason}";
                return false;
            }
            $body = (string)$response->getBody();
            $json = json_decode($body, true);
            if (!isset($json['data']['credit_balance'])) {
                $this->error = "Invalid response";
                return false;
            }
            return $json['data']['credit_balance'];
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
