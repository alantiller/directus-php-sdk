<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class SettingsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/settings', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/settings/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/settings', false, 'GET');
        }
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/settings/' . $id, $fields, 'PATCH');
    }
}