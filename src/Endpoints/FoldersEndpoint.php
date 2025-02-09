<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class FoldersEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/folders', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/folders/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/folders', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/folders', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/folders/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/folders', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/folders/' . $id, false, 'DELETE');
        }
    }
}