<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class RevisionsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/revisions', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/revisions/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/revisions', false, 'GET');
        }
    }
}