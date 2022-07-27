<?php

namespace Mediatoolkit\Tests;

use Mediatoolkit\ActiveCampaign\Client;
use PHPUnit\Framework\TestCase;

abstract class ResourceTestCase extends TestCase
{
    /**
     * The Client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * The Resource Test Case constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = new Client(
            $_ENV['API_URL'],
            $_ENV['API_TOKEN'],
            $_ENV['EVENT_TRACKING_ACTID'],
            $_ENV['EVENT_TRACKING_KEY']
        );
    }
}
