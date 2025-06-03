<?php

namespace AlanTiller\DirectusSdk;

use AlanTiller\DirectusSdk\Auth\AuthInterface;
use AlanTiller\DirectusSdk\Auth\UserPasswordAuth;
use AlanTiller\DirectusSdk\Endpoints\ActivityEndpoint;
use AlanTiller\DirectusSdk\Endpoints\CollectionsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\CommentsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\ContentVersionsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\DashboardsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\ExtensionsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\FieldsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\FilesEndpoint;
use AlanTiller\DirectusSdk\Endpoints\FlowsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\FoldersEndpoint;
use AlanTiller\DirectusSdk\Endpoints\ItemsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\NotificationsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\OperationsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\PanelsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\PermissionsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\PoliciesEndpoint;
use AlanTiller\DirectusSdk\Endpoints\PresetsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\RelationsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\RevisionsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\RolesEndpoint;
use AlanTiller\DirectusSdk\Endpoints\SchemaEndpoint;
use AlanTiller\DirectusSdk\Endpoints\ServerEndpoint;
use AlanTiller\DirectusSdk\Endpoints\SettingsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\SharesEndpoint;
use AlanTiller\DirectusSdk\Storage\StorageInterface;
use AlanTiller\DirectusSdk\Endpoints\TranslationsEndpoint;
use AlanTiller\DirectusSdk\Endpoints\UsersEndpoint;
use AlanTiller\DirectusSdk\Endpoints\UtilitiesEndpoint;
use AlanTiller\DirectusSdk\Exceptions\DirectusException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Directus
{
    private string $baseUrl;
    private ?AuthInterface $auth = null;
    private ?string $accessToken = null;
    private Client $client;
    private StorageInterface $storage;
    private bool $stripHeaders;

    /**
     * Directus constructor.
     *
     * @param string $baseUrl
     * @param StorageInterface $storage
     * @param AuthInterface|null $auth
     * @param boolean $stripHeaders
     */
    public function __construct(string $baseUrl, StorageInterface $storage, ?AuthInterface $auth = null, bool $stripHeaders = true) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->storage = $storage;
        $this->auth = $auth;
        $this->stripHeaders = $stripHeaders;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Attempt to authenticate if possible
        if ($this->auth) {
            try {
                $this->authenticate();
            } catch (\Exception $e) {
                // Handle authentication failure (e.g., log the error)
                error_log('Authentication failed during Directus instantiation: ' . $e->getMessage());
            }
        }
    }

    public function authenticate(): void
    {
        if ($this->auth) {
            $this->accessToken = $this->auth->authenticate();
            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);
        }
    }

    public function refreshToken(): void
    {
        if ($this->auth instanceof UserPasswordAuth) {
            $newToken = $this->auth->refreshToken();
            if ($newToken) {
                $this->accessToken = $newToken;
                $this->client = new Client([
                    'base_uri' => $this->baseUrl,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ],
                ]);
            }
        }
    }

    private function getAccessToken()
    {
        if ($this->storage->get('directus_refresh') != null) {
            if ($this->storage->get('directus_access_expires') < time()) {
                $refresh = curl_init($this->baseUrl . '/auth/refresh');
                curl_setopt($refresh, CURLOPT_POST, 1);
                curl_setopt(
                    $refresh,
                    CURLOPT_POSTFIELDS,
                    json_encode(['refresh_token' => $this->storage->get('directus_refresh')])
                );
                curl_setopt($refresh, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($refresh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $response = curl_exec($refresh);
                $httpcode = curl_getinfo($refresh, CURLINFO_HTTP_CODE);
                curl_close($refresh);
                if ($httpcode == 200) {
                    $response = json_decode($response, true);
                    $this->storage->set('directus_refresh', $response['data']['refresh_token']);
                    $this->storage->set('directus_access', $response['data']['access_token']);
                    $expires = $response['data']['expires'] / 1000;
                    $expires = time() + $expires;
                    $this->storage->set('directus_access_expires', $expires);
                    return $response['data']['access_token'];
                } else {
                    $this->authLogout();
                    return false;
                }
            }
            return $this->storage->get('directus_access');
        } elseif ($this->accessToken) {
            return $this->accessToken;
        } else {
            return false;
        }
    }

    private function stripHeaders(array $response): array
    {
        if ($this->stripHeaders === false) {
            return $response;
        } else {
            unset($response['headers']);
            return $response;
        }
    }

    private function makeCall(string $request, mixed $data = false, string $method = 'GET', bool $bypass = false, bool $multipart = false)
    {
        $request = $this->baseUrl . $request; // add the base url to the requested uri

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (($this->accessToken != false || $this->storage->get('directus_refresh')) && $bypass == false) {
            $accessToken = $this->getAccessToken();
            if ($accessToken) {
                $headers['Authorization'] = 'Bearer ' . $accessToken;
            }
        }

        $options = [
            'headers' => $headers,
        ];

        try {
            switch ($method) {
                case 'POST':
                    $options['json'] = $data;
                    $response = $this->client->post($request, $options);
                    break;
                case 'DELETE':
                    $options['json'] = $data;
                    $response = $this->client->delete($request, $options);
                    break;
                case 'PATCH':
                    $options['json'] = $data;
                    $response = $this->client->patch($request, $options);
                    break;
                case 'POST_MULTIPART':
                    $multipartData = [];
                    foreach ($data as $key => $value) {
                        if ($key === 'file') {
                            $multipartData[] = [
                                'name' => $value['name'],
                                'contents' => fopen($value['tmp_name'], 'r'),
                                'filename' => $value['name'],
                            ];
                        } else {
                            $multipartData[] = [
                                'name' => $key,
                                'contents' => $value,
                            ];
                        }
                    }
                    $options['multipart'] = $multipartData;
                    // Remove Content-Type header for multipart requests
                    unset($options['headers']['Content-Type']);
                    $response = $this->client->post($request, $options);
                    break;
                case 'PATCH_MULTIPART':
                    $multipartData = [];
                    foreach ($data as $key => $value) {
                        if ($key === 'file') {
                            $multipartData[] = [
                                'name' => $value['name'],
                                'contents' => fopen($value['tmp_name'], 'r'),
                                'filename' => $value['name'],
                            ];
                        } else {
                            $multipartData[] = [
                                'name' => $key,
                                'contents' => $value,
                            ];
                        }
                    }
                    $options['multipart'] = $multipartData;
                    // Remove Content-Type header for multipart requests
                    unset($options['headers']['Content-Type']);
                    $response = $this->client->patch($request, $options);
                    break;
                default: // GET
                    if ($data) {
                        $options['query'] = $data;
                    }
                    $response = $this->client->get($request, $options);
            }

            return $this->processResponse($response);
        } catch (\Exception $e) {
            // Handle Guzzle exceptions (e.g., network errors, timeouts)
            throw new DirectusException('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function processResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $headers = $response->getHeaders();

        $result = [
            'data' => $data,
            'headers' => $headers,
            'status' => $statusCode,
        ];

        if ($statusCode >= 400) {
            // Handle API errors (e.g., log the error, throw an exception)
            error_log('API error: ' . $body);
            // Optionally, throw an exception with the error details
            throw new DirectusException('API error: ' . $body, $statusCode);
        }

        return $result;
    }

    public function authLogout()
    {
        $data = ['refresh_token' => $this->storage->get('directus_refresh')];
        $response = $this->makeCall('/auth/logout', $data, 'POST', true);
        if ($response['status'] === 204) {
            $this->storage->delete('directus_refresh');
            $this->storage->delete('directus_access');
            $this->storage->delete('directus_access_expires');
            header('Refresh:0');
            return true;
        } else {
            return $this->stripHeaders($response);
        }
    }

    public function authPasswordRequest(string $email, string $resetUrl = ''): array
    {
        $data = ['email' => $email];
        if ($resetUrl != '') {
            $data['reset_url'] = $resetUrl;
        }
        return $this->makeCall('/auth/password/request', $data, 'POST');
    }

    public function authPasswordReset(string $token, string $password): array
    {
        $data = ['token' => $token, 'password' => $password];
        return $this->makeCall('/auth/password/reset', $data, 'POST');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function makeCustomCall(string $uri, $data = false, string $method = 'GET'): array
    {
        return $this->makeCall($uri, $data, $method);
    }

    public function items(string $collection): ItemsEndpoint
    {
        return new ItemsEndpoint($this, $collection);
    }

    public function files(): FilesEndpoint
    {
        return new FilesEndpoint($this);
    }

    public function activity(): ActivityEndpoint
    {
        return new ActivityEndpoint($this);
    }

    public function collections(): CollectionsEndpoint
    {
        return new CollectionsEndpoint($this);
    }

    public function comments(): CommentsEndpoint
    {
        return new CommentsEndpoint($this);
    }

    public function contentVersions(): ContentVersionsEndpoint
    {
        return new ContentVersionsEndpoint($this);
    }

    public function dashboards(): DashboardsEndpoint
    {
        return new DashboardsEndpoint($this);
    }

    public function extensions(): ExtensionsEndpoint
    {
        return new ExtensionsEndpoint($this);
    }

    public function fields(string $collection): FieldsEndpoint
    {
        return new FieldsEndpoint($this, $collection);
    }

    public function flows(): FlowsEndpoint
    {
        return new FlowsEndpoint($this);
    }

    public function folders(): FoldersEndpoint
    {
        return new FoldersEndpoint($this);
    }

    public function notifications(): NotificationsEndpoint
    {
        return new NotificationsEndpoint($this);
    }

    public function operations(): OperationsEndpoint
    {
        return new OperationsEndpoint($this);
    }

    public function panels(): PanelsEndpoint
    {
        return new PanelsEndpoint($this);
    }

    public function permissions(): PermissionsEndpoint
    {
        return new PermissionsEndpoint($this);
    }

    public function policies(): PoliciesEndpoint
    {
        return new PoliciesEndpoint($this);
    }

    public function presets(): PresetsEndpoint
    {
        return new PresetsEndpoint($this);
    }

    public function relations(): RelationsEndpoint
    {
        return new RelationsEndpoint($this);
    }

    public function revisions(): RevisionsEndpoint
    {
        return new RevisionsEndpoint($this);
    }

    public function roles(): RolesEndpoint
    {
        return new RolesEndpoint($this);
    }

    public function schema(): SchemaEndpoint
    {
        return new SchemaEndpoint($this);
    }

    public function server(): ServerEndpoint
    {
        return new ServerEndpoint($this);
    }

    public function settings(): SettingsEndpoint
    {
        return new SettingsEndpoint($this);
    }

    public function shares(): SharesEndpoint
    {
        return new SharesEndpoint($this);
    }

    public function translations(): TranslationsEndpoint
    {
        return new TranslationsEndpoint($this);
    }

    public function users(): UsersEndpoint
    {
        return new UsersEndpoint($this);
    }

    public function utilities(): UtilitiesEndpoint
    {
        return new UtilitiesEndpoint($this);
    }
}
