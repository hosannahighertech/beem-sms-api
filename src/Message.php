<?php

/**
 * @link http://www.hosannahighertech.co.tz/
 * @copyright Copyright (c) 2021 Hosanna Higher Technologies Co. Ltd
 * @license Apache 2.0
 */

namespace hosanna\sms\beem;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class that encapsulates one message to be sent
 */
class Message
{
    private string $sender;
    private string $message;
    private array $recipients = [];

    public function __construct(string $sender, string $message)
    {
        $this->sender = $sender;
        $this->message = $message;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function generateRecipientIdFor(string $mobile): string
    {
        $salt = microtime();
        return hash('SHA256', "{$this->message}+{$this->sender}+{$salt}--{$mobile}", false);
    }

    public function addRecipient(string $mobileNumber, string $id = null,  $country = null): bool
    {
        if ($country != null) {
            $mobileNumber = $this->getNormalizeMobile($mobileNumber, $country);
            if (empty($mobileNumber)) return false;
        }

        if (empty($id)) {
            $id = $this->generateRecipientIdFor($mobileNumber);
        }

        $this->recipients[] = [
            'recipient_id' => $id,
            'dest_addr' => $mobileNumber,
        ];

        return true;
    }

    private function getNormalizeMobile(string $number, string $countryCode): string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($number, $countryCode);
        if ($phoneNumberUtil->isValidNumber($phoneNumberObject)) {
            return str_replace('+', '', $phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164));
        }
        return '';
    }
}
