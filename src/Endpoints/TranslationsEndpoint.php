<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class TranslationsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/translations', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/translations/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/translations', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/translations', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/translations/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/translations', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/translations/' . $id, false, 'DELETE');
        }
    }
}