## Directory path

- `app/http/testcontroller.php` 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function logTest()
    {
        \Log::channel('axiom')->info('Testing Axiom logger!');
        return 'Log sent to Axiom';
    }
}
```

- `app/logging/AxiomHandler`

```
<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Formatter\FormatterInterface;

class AxiomHandler extends AbstractProcessingHandler
{
    private $apiToken;
    private $dataset;

    public function __construct($level = Logger::DEBUG, bool $bubble = true, $apiToken = null, $dataset = null)
    {
        parent::__construct($level, $bubble);
        $this->apiToken = $apiToken;
        $this->dataset = $dataset;
    }

    private function initializeCurl(): \CurlHandle
    {
        $endpoint = "https://api.axiom.co/v1/datasets/{$this->dataset}/ingest";
        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json',
        ]);

        return $ch;
    }

    protected function write(LogRecord $record): void
    {
        $ch = $this->initializeCurl();

        $data = [
            'message' => $record->message,
            'context' => $record->context,
            'level' => $record->level->getName(),
            'channel' => $record->channel,
            'extra' => $record->extra,
        ];

        $payload = json_encode([$data]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_exec($ch);
        if (curl_errno($ch)) {
            // Optionally log the curl error to PHP error log
            error_log('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new \Monolog\Formatter\JsonFormatter();
    }
}
```

- `config/logging.php`

```php
'axiom' => [
            'driver' => 'monolog',
            'handler' => App\Logging\AxiomHandler::class,
            'level' => env('LOG_LEVEL', 'debug'),
            'with' => [
                'apiToken' => env('AXIOM_API_TOKEN'),
                'dataset' => env('AXIOM_DATASET'),
            ],
        ],
```

- `routes/web.php`

```
<?php

use App\Http\Controllers\TestController;

Route::get('/test-log', [TestController::class, 'logTest']);
```


# Installation 

In your `env` file replace `AXIOM_API_TOKEN` with your Axiom API Token with ingesting and querying permissions, replace `AXIOM_DATASET` with your dataset name.

```
LOG_CHANNEL=axiom
AXIOM_API_TOKEN=
AXIOM_DATASET=
LOG_LEVEL=debug
```

Run the application using: `php artisan serve`





## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
