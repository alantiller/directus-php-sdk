<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class FilesEndpoint extends AbstractEndpoint
{
    public function get($data = false)
    {
        if (is_string($data)) {
            return $this->directus->makeCustomCall('/files/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/files', false, 'GET');
        }
    }

    public function create(array $file, string $folder = null, string $storage = 'local'): array
    {
        $data = [
            'file' => $file,
            'storage' => $storage,
            'folder' => $folder,
        ];
        return $this->directus->makeCustomCall('/files', $data, 'POST_MULTIPART');
    }

    public function update(string $id, array $fields): array
    {
        return $this->directus->makeCustomCall('/files/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/files', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/files/' . $id, false, 'DELETE');
        }
    }
}