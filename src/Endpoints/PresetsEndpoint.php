<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class PresetsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/presets', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/presets/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/presets', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/presets', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/presets/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/presets', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/presets/' . $id, false, 'DELETE');
        }
    }
}