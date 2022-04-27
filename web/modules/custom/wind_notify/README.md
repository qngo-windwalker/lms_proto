Mailgun example
```php
use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Mailgun;

$httpClientConfigurator = (new HttpClientConfigurator())
->setApiKey('09d7b7aa...16674872')
->setEndpoint('https://api.mailgun.net');

$mg = new Mailgun($httpClientConfigurator);
$domain = "sandbox0318e9cd...52b.mailgun.org";
$mg->messages()->send($domain, array(
    'from'=> $message['from'],
    'to'=> $message['to'],
    'subject' => 'The PHP SDK is awesome!',
    'html' => $html
  )
);
```
