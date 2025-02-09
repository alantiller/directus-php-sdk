<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class RolesEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/roles', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/roles/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/roles', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/roles', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/roles/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/roles', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/roles/' . $id, false, 'DELETE');
        }
    }
}