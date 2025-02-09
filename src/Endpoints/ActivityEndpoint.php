<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class ActivityEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/activity', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/activity/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/activity', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/activity', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/activity/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/activity', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/activity/' . $id, false, 'DELETE');
        }
    }
}