<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class CommentsEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/comments', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/comments/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/comments', false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/comments', $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/comments/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/comments', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/comments/' . $id, false, 'DELETE');
        }
    }
}