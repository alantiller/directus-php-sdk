<?php

namespace AlanTiller\DirectusSdk\Endpoints;

use AlanTiller\DirectusSdk\Directus;

class ItemsEndpoint extends AbstractEndpoint
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
            return $this->directus->makeCustomCall(
                '/items/' . $this->collection,
                $data,
                'GET'
            );
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall(
                '/items/' . $this->collection . '/' . $data,
                false,
                'GET'
            );
        } else {
            return $this->directus->makeCustomCall(
                '/items/' . $this->collection,
                false,
                'GET'
            );
        }
    }

    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall(
            '/items/' . $this->collection,
            $fields,
            'POST'
        );
    }

    public function update(array $fields, $id = null): array
    {
        $uri = '/items/' . $this->collection;
        if ($id != null) {
            $uri .= '/' . $id;
        }
        return $this->directus->makeCustomCall($uri, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        $uri = '/items/' . $this->collection;
        if (is_array($id)) {
            return $this->directus->makeCustomCall($uri, $id, 'DELETE');
        } else {
            $uri .= '/' . $id;
            return $this->directus->makeCustomCall($uri, false, 'DELETE');
        }
    }
}