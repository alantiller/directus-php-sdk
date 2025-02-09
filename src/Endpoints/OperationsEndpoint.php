<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class OperationsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/operations', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/operations/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/operations', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/operations', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/operations/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/operations', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/operations/' . $id, false, 'DELETE');
        }
    }
}