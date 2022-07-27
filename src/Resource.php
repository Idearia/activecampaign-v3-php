<?php

namespace Mediatoolkit\ActiveCampaign;

class Resource
{
    /**
     * The client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * Resource constructor.
     *
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }
}
