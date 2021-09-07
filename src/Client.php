<?php

namespace Mediatoolkit\ActiveCampaign;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Throwable;

class Client
{

    const HEADER_AUTH_KEY = 'Api-Token';

    const LIB_USER_AGENT = 'activecampaign-v3-php/1.0';

    const API_VERSION_URL = '/api/3';

    const EVENT_TRACKING_URL = 'https://trackcmp.net/event';

    /**
     * ActiveCampaign API URL.
     * Format is https://YOUR_ACCOUNT_NAME.api-us1.com
     * @var string
     */
    protected $api_url;

    /**
     * ActiveCampaign API token
     * Get yours from developer settings.
     * @var string
     */
    protected $api_token;

    /**
     * Event Tracking ACTID
     * Get yours from Settings > Tracking > Event Tracking > Event Tracking API
     * @var string
     */
    protected $event_tracking_actid;

    /**
     * Event Tracking Key
     * Get yours from Settings > Tracking > Event Tracking > Event Key
     * @var string
     */
    protected $event_tracking_key;

    /**
     * In caso di raggiungimento della quota AC di richieste al secondo,
     * il client riproverà a lanciare la richiesta per un massimo di $retry
     * volte.
     * @var int
     */
    protected $retry_times;

    /**
     * Numero di secondi da aspettare prima di fare un retry.
     * @var float
     */
    protected $retry_delay;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \GuzzleHttp\Client
     */
    private $event_tracking_client;

    public function __construct($api_url, $api_token, $event_tracking_actid = null, $event_tracking_key = null)
    {
        $this->api_url = $api_url;
        $this->api_token = $api_token;
        $this->event_tracking_actid = $event_tracking_actid;
        $this->event_tracking_key = $event_tracking_key;

        $guzzleOptions = [
            'base_uri' => $this->api_url,
            'headers' => [
                'User-Agent' => self::LIB_USER_AGENT,
                self::HEADER_AUTH_KEY => $this->api_token,
                'Accept' => 'application/json'
            ]
        ];

        // Imposta il retry automatico
        // https://github.com/guzzle/guzzle/issues/1806#issuecomment-293931737
        if ($this->retry_times) {
            $handlerStack = HandlerStack::create(new CurlHandler());
            $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
            $guzzleOptions['handler'] = $handlerStack;
        }

        $this->client = new \GuzzleHttp\Client($guzzleOptions);

        if (!is_null($this->event_tracking_actid) && !is_null($this->event_tracking_key)) {
            $this->event_tracking_client = new \GuzzleHttp\Client([
                'base_uri' => self::EVENT_TRACKING_URL,
                'headers' => [
                    'User-Agent' => self::LIB_USER_AGENT,
                    'Accept' => 'application/json'
                ],
                'form_params' => [
                    'actid' => $this->event_tracking_actid,
                    'key' => $this->event_tracking_key
                ]
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
     * @return Mediatoolkit\ActiveCampaign\Client
     */
    public function withRetry(int $times = 10, float $delay = 0.5)
    {
        $this->retry_times = $times;
        $this->retry_delay = $delay;
        return $this;
    }

    /**
     * Data un'eccezione ritornata dal client, determina
     * se riprovare o meno a lanciare la richiesta.
     */
    // public function retryDecider() // https://github.com/guzzle/guzzle/issues/1806
    // {
    //     return function (
    //         $retries,
    //         Request $request,
    //         Response $response = null,
    //         RequestException $exception = null
    //     ) {
    //         // Limit the number of retries to 5
    //         if ($retries >= 5) {
    //             return false;
    //         }

    //         // Retry connection exceptions
    //         if ($exception instanceof ConnectException) {
    //             return true;
    //         }

    //         if ($response) {
    //             // Retry on server errors
    //             if ($response->getStatusCode() >= 500 ) {
    //                 return true;
    //             }
    //         }

    //         return false;
    //     };
    // }
    // public function retryDecider( Throwable $th )
    // {
    //     if (! $this->retry) {
    //         return false;
    //     }

    //     // La richiesta verrà rilanciata nel caso in cui l'errore
    //     // è dovuto alle troppe richieste al secondo.
    //     if ( $th->getMessage() === 'piripicchio' || $th->getCode() === 'parapocchio') {
    //         return true;
    //     }

    //     return false;
    // }

    /**
     * delay 1s 2s 3s 4s 5s
     *
     * @return Closure
     */
    // public function retryDelay() // https://github.com/guzzle/guzzle/issues/1806
    // {
    //     return function ($numberOfRetries) {
    //         return 1000 * $numberOfRetries;
    //     };
    // }
}