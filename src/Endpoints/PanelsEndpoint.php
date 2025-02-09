<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class PanelsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/panels', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/panels/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/panels', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/panels', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/panels/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/panels', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/panels/' . $id, false, 'DELETE');
        }
    }
}