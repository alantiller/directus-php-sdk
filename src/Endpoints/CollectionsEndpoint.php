<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class CollectionsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/collections', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/collections/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/collections', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/collections', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/collections/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/collections', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/collections/' . $id, false, 'DELETE');
        }
    }
}