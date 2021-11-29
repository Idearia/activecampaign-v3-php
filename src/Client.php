<?php

namespace Mediatoolkit\ActiveCampaign;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleLogMiddleware\LogMiddleware;
use Exception;

class Client
{

    public const HEADER_AUTH_KEY = 'Api-Token';

    public const LIB_USER_AGENT = 'activecampaign-v3-php/1.0';

    public const API_VERSION_URL = '/api/3';

    public const EVENT_TRACKING_URL = 'https://trackcmp.net/event';

    /**
     * ActiveCampaign API URL.
     * Format is https://YOUR_ACCOUNT_NAME.api-us1.com
     *
     * @var string
     */
    protected $api_url;

    /**
     * ActiveCampaign API token
     * Get yours from developer settings.
     *
     * @var string
     */
    protected $api_token;

    /**
     * Event Tracking ACTID
     * Get yours from Settings > Tracking > Event Tracking > Event Tracking API
     *
     * @var string
     */
    protected $event_tracking_actid;

    /**
     * Event Tracking Key
     * Get yours from Settings > Tracking > Event Tracking > Event Key
     *
     * @var string
     */
    protected $event_tracking_key;

    /**
     * In caso di raggiungimento della quota AC di richieste al secondo,
     * il client riproverà a lanciare la richiesta per un massimo di $retry
     * volte.
     *
     * @var int
     */
    protected $retry_times;

    /**
     * Numero di secondi da aspettare prima di fare un retry.
     *
     * @var float
     */
    protected $retry_delay;

    /**
     * Le opzioni passate al client di Guzzle
     *
     * @see https://docs.guzzlephp.org/en/stable/request-options.html
     * @var array
     */
    protected $options;

    /** @var \GuzzleHttp\Client */
    private $client;

    /** @var \GuzzleHttp\Client */
    private $event_tracking_client;

    public function __construct($api_url, $api_token, $event_tracking_actid = null, $event_tracking_key = null)
    {
        $this->api_url = $api_url;
        $this->api_token = $api_token;
        $this->event_tracking_actid = $event_tracking_actid;
        $this->event_tracking_key = $event_tracking_key;
        $this->options = [
            'base_uri' => $this->api_url,
            'headers' => [
                'User-Agent' => self::LIB_USER_AGENT,
                self::HEADER_AUTH_KEY => $this->api_token,
                'Accept' => 'application/json',
            ],
            'handler' => HandlerStack::create(new CurlHandler),
        ];

        // Client per l'event tracking
        if (! is_null($this->event_tracking_actid) && ! is_null($this->event_tracking_key)) {
            $this->event_tracking_client = new \GuzzleHttp\Client([
                'base_uri' => self::EVENT_TRACKING_URL,
                'headers' => [
                    'User-Agent' => self::LIB_USER_AGENT,
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'actid' => $this->event_tracking_actid,
                    'key' => $this->event_tracking_key,
                ],
            ]);
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function getEventTrackingClient()
    {
        if (is_null($this->event_tracking_actid)) {
            return null;
        }
        return $this->event_tracking_client;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * @return string|null
     */
    public function getEventTrackingActid()
    {
        return $this->event_tracking_actid;
    }

    /**
     * Attiva la modalità retry del client
     *
     * @see https://github.com/guzzle/guzzle/issues/1806#issuecomment-293931737
     * @return Mediatoolkit\ActiveCampaign\Client
     */
    public function withMiddleware($middleware)
    {
        $handlerStack = $this->getHandlerStack();
        $handlerStack->push($middleware);
        $this->options['handler'] = $handlerStack;

        return $this;
    }

    /**
     * Attiva la modalità retry del client
     *
     * @see https://github.com/guzzle/guzzle/issues/1806#issuecomment-293931737
     * @return Mediatoolkit\ActiveCampaign\Client
     */
    public function withRetry(int $retry_times = 10, float $retry_delay = 0.5)
    {
        return $this->withMiddleware(Middleware::retry(
            $this->retryDecider($retry_times),
            $this->retryDelay($retry_delay)
        ));
    }

    /**
     * Attiva la modalità di logging del client,
     * per loggare tutte le richieste e risposte
     *
     * TODO: filepath: storage_path('logs' . DIRECTORY_SEPARATOR . 'guzzle-' . date('Y-m-d') . '.log');
     * TODO: valutiamo se creare la nostra strategia di logging
     *
     * @see https://github.com/gmponos/guzzle-log-middleware#handlers
     *
     * @see https://github.com/gmponos/guzzle-log-middleware
     * @return Mediatoolkit\ActiveCampaign\Client
     */
    public function withLog($logger)
    {
        return $this->withMiddleware(new LogMiddleware($logger));
    }

    /**
     * Crea il client di Guzzle con le opzioni passate
     */
    public function create()
    {
        $this->client = new \GuzzleHttp\Client($this->options);

        return $this;
    }

    /**
     * Ritorna lo handlerStack del client Guzzle
     */
    protected function getHandlerStack()
    {
        return $this->options['handler'];
    }

    /**
     * Data un'eccezione ritornata dal client, determina
     * se riprovare o meno a lanciare la richiesta.
     */
    public function retryDecider(int $retry_times)
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            Exception $exception = null
        ) use ($retry_times) {
            // Esegue una richiesta un certo numero di volte al massimo
            // i.e. al massimo $retry_times volte
            if ($retries >= $retry_times) {
                return false;
            }

            // Riesegue la richiesta in caso di ConnectException
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($exception instanceof RequestException) {
                // Riesegue la richiesta in caso di `cURL error`
                if (str_contains($exception->getMessage(), 'cURL error')) {
                    return true;
                }
            }

            if ($response) {
                $code = $response->getStatusCode();
                
                // Riprova la richiesta se c'è un errore da parte del server
                if ($code >= 500) {
                    return true;
                }
                // Riprova la richiesta in caso di Forbidden
                // Speriamo di poterlo togliere, ma purtroppo dobbiamo metterlo
                if ($code === 403) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * delay 1s 2s 3s 4s 5s
     *
     * @return callable
     */
    public function retryDelay(float $retry_delay)
    {
        return function ($numberOfRetries) use ($retry_delay) {
            return 1000 * $retry_delay * $numberOfRetries;
        };
    }
}
