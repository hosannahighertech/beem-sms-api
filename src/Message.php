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

    public function __construct(string $sender, string $message, $country = null)
    {
        $this->sender = $sender;
        $this->message = $message;

        if ($country != null) {
            $this->sender = $this->getNormalizeMobile($sender, $country);
        }
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

    public function addSender(string $id, string $mobileNumber, $country = null): bool
    {
        if ($country != null) {
            $mobileNumber = $this->getNormalizeMobile($mobileNumber, $country);
            if (empty($mobileNumber)) return false;
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
