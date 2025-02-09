<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class DashboardsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/dashboards', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/dashboards/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/dashboards', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/dashboards', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/dashboards/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/dashboards', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/dashboards/' . $id, false, 'DELETE');
        }
    }
}