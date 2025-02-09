<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class ServerEndpoint extends AbstractEndpoint
{
    public function getInfo($data = false)
    {
        return $this->directus->makeCustomCall('/server/info', $data, 'GET');
    }

    public function getPing($data = false)
    {
        return $this->directus->makeCustomCall('/server/ping', $data, 'GET');
    }
}