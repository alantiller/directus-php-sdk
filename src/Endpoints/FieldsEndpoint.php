<?php

namespace AlanTiller\DirectusSdk\Endpoints;

use AlanTiller\DirectusSdk\Directus;

class FieldsEndpoint extends AbstractEndpoint
{
    private string $collection;

    public function __construct(Directus $directus, string $collection)
    {
        parent::__construct($directus);
        $this->collection = $collection;
    }

    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/fields/' . $this->collection, $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/fields/' . $this->collection . '/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/fields/' . $this->collection, false, 'GET');
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/fields/' . $this->collection, $fields, 'POST');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/fields/' . $this->collection . '/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/fields/' . $this->collection, $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/fields/' . $this->collection . '/' . $id, false, 'DELETE');
        }
    }
}