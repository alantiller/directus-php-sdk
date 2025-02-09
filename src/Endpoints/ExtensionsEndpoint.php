<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class ExtensionsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/extensions', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/extensions/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/extensions', false, 'GET');
        }
    }
}