<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class PoliciesEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/policies', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/policies/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/policies', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/policies', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/policies/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/policies', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/policies/' . $id, false, 'DELETE');
        }
    }
}