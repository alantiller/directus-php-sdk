<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class UtilitiesEndpoint extends AbstractEndpoint
{
    public function hash(string $string): array
    {
        $data = ['string' => $string];
        return $this->directus->makeCustomCall('/utils/hash', $data, 'POST');
    }
}