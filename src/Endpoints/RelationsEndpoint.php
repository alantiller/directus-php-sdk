<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class RelationsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/relations', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/relations/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/relations', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/relations', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/relations/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/relations', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/relations/' . $id, false, 'DELETE');
        }
    }
}