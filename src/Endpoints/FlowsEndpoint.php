<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class FlowsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/flows', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/flows/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/flows', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/flows', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/flows/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/flows', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/flows/' . $id, false, 'DELETE');
        }
    }
}