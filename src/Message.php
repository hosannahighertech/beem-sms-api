<?php

/**
 * @link http://www.hosannahighertech.co.tz/
 * @copyright Copyright (c) 2021 Hosanna Higher Technologies Co. Ltd
 * @license Apache 2.0
 */

namespace hosanna\sms\beem;


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

    public function addSender(string $id, string $mobileNumber): bool
    {
        //TODO: Validate number and code
        $this->recipients[] = [
            'recipient_id' => $id,
            'dest_addr' => $mobileNumber,
        ];

        return true;
    }
}
