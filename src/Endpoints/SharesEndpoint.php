<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class SharesEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/shares', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/shares/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/shares', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/shares', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/shares/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/shares', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/shares/' . $id, false, 'DELETE');
        }
    }
}