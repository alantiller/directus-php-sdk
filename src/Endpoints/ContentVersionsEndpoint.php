<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class ContentVersionsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/versions', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/versions/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/versions', false, 'GET');
        }
    }
}