<?php

namespace AlanTiller\DirectusSdk\Endpoints;

use AlanTiller\DirectusSdk\Directus;
use GuzzleHttp\Client;

abstract class AbstractEndpoint
{
    protected Directus $directus;
    protected Client $client;

    public function __construct(Directus $directus)
    {
        $this->directus = $directus;
        $this->client = $this->directus->getClient();
    }
}