# beem-sms-api
Send SMS with easy using BEEM

## Installation
You must be using composer to be able to use this library. If composer 1.x is installed, make sure you upgrade to 2.0 to avoid installation problems. Check version with `composer -V`

once setup run the command at the root of your project to install the library

`composer require hosannahighertech/beem-sms-api`

## Usage
To send SMS just create instance of the client passing Key and Secret

```php
$sms = new Client('YOUR_KEY_HERE', 'YOUR_SECRET_HERE');
```

Then create new message. First Parameter is Sender ID or Mobile number of the sender. Next parameter is text of the message.
```php
$message = new Message('SENDER_ID_OR_MOBILE_NO', 'This is my SMS text body');
```

Next add Receivers number. First parameter is your special ID (see BEEM Docs) and the other one is receiver's mobile.

```php
$message->addSender('SPECIAL_ID', '255xxxxxx');
```

Finally send the message. If failed, the method `getLastMessage()` should contain the error message

```php
$isSent = $sms->send($message);
if (!$isSent) {
    var_dump($sms->getLastError());// do something useful with error
}
```

## Contributing
Contribute by opening issues when you encounter a problem or when asking for feature. We also receive PR for enhancement or bug fixes.