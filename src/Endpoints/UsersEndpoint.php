<?php

namespace AlanTiller\DirectusSdk\Endpoints;

class UsersEndpoint extends AbstractEndpoint
{
    /**
     * Get a list of users
     * 
     * @param mixed $data
     * @return array
     */
    public function get($data = false)
    {
        if (is_array($data)) {
            return $this->directus->makeCustomCall('/users', $data, 'GET');
        } elseif (is_integer($data) || is_string($data)) {
            return $this->directus->makeCustomCall('/users/' . $data, false, 'GET');
        } else {
            return $this->directus->makeCustomCall('/users', false, 'GET');
        }
    }

    /**
     * Create a new user
     *
     * @param array $fields
     * @return array
     */
    public function create(array $fields): array
    {
        return $this->directus->makeCustomCall('/users', $fields, 'POST');
    }

    public function update(array $fields, $id = null): array
    {
        return $this->directus->makeCustomCall('/users/' . $id, $fields, 'PATCH');
    }

    public function delete($id): array
    {
        if (is_array($id)) {
            return $this->directus->makeCustomCall('/users', $id, 'DELETE');
        } else {
            return $this->directus->makeCustomCall('/users/' . $id, false, 'DELETE');
        }
    }

    public function invite(string $email, string $role, string|bool $invite_url = false)
    {
        $data = ['email' => $email, 'role' => $role];
        if ($invite_url != false) {
            $data['invite_url'] = $invite_url;
        }
        return $this->directus->makeCustomCall('/users/invite', $data, 'POST');
    }

    public function acceptInvite(string $password, string $token)
    {
        $data = ['password' => $password, 'token' => $token];
        return $this->directus->makeCustomCall('/users/invite/accept', $data, 'POST');
    }

    public function me($filter = false)
    {
        return $this->directus->makeCustomCall('/users/me', $filter, 'GET');
    }
}