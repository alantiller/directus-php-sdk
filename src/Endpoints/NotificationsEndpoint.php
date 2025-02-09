<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class NotificationsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/notifications', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/notifications/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/notifications', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/notifications', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/notifications/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/notifications', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/notifications/' . $id, false, 'DELETE');
        }
    }
}