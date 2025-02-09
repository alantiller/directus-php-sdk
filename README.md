# Directus PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A PHP SDK for interacting with the Directus API. This SDK provides a convenient and object-oriented way to access Directus endpoints and perform common operations.

## Table of Contents

- [Directus PHP SDK](#directus-php-sdk)
  - [Table of Contents](#table-of-contents)
  - [Features](#features)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Usage](#usage)
    - [Authentication](#authentication)
      - [API Key Authentication](#api-key-authentication)
      - [User/Password Authentication](#userpassword-authentication)
    - [Items](#items)
    - [Users](#users)
    - [Files](#files)
    - [Other Endpoints](#other-endpoints)
    - [Custom Calls](#custom-calls)
  - [Storage](#storage)
    - [Session Storage](#session-storage)
    - [Cookie Storage](#cookie-storage)
    - [Custom Storage](#custom-storage)
  - [Error Handling](#error-handling)
  - [Testing](#testing)
  - [Contributing](#contributing)
  - [License](#license)

## Features

-   Object-oriented interface for interacting with the Directus API
-   Supports all Directus endpoints (Items, Files, Users, etc.)
-   Supports multiple authentication methods (API Key, User/Password)
-   Customizable storage for authentication tokens (Session, Cookie, Custom)
-   Easy-to-use methods for common CRUD operations (Create, Read, Update, Delete)
-   Comprehensive error handling

## Requirements

-   PHP 8.0 or higher
-   Composer
-   Guzzle HTTP client (`guzzlehttp/guzzle`)

## Installation

1.  Install the SDK using Composer:

    ```bash
    composer require alantiller/directus-php-sdk
    ```

## Configuration

Before using the SDK, you need to configure it with your Directus base URL and authentication details.

1.  **Base URL:** The base URL of your Directus instance (e.g., `https://your-directus-instance.com`).
2.  **Storage:** Choose a storage mechanism for authentication tokens (Session, Cookie, or Custom).
3.  **Authentication:** Choose an authentication method and provide the necessary credentials.

## Usage

### Authentication

The SDK supports multiple authentication methods:

#### API Key Authentication

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Auth\ApiKeyAuth;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$apiKey = 'YOUR_API_KEY';

$storage = new SessionStorage('directus_'); // Optional prefix
$auth = new ApiKeyAuth($apiKey);

$directus = new Directus(
    $baseUrl,
    $storage,
    $auth
);
```

#### User/Password Authentication

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Auth\UserPasswordAuth;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$username = 'your_email@example.com';
$password = 'your_password';

$storage = new SessionStorage('directus_'); // Optional prefix
$auth = new UserPasswordAuth($baseUrl, $username, $password);

$directus = new Directus(
    $baseUrl,
    $storage,
    $auth
);

// Authenticate the user
try {
    $directus->authenticate();
} catch (\Exception $e) {
    echo "Authentication failed: " . $e->getMessage() . PHP_EOL;
}
```

### Items

The `items` endpoint allows you to manage items in a specific collection.

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$storage = new SessionStorage('directus_');

$directus = new Directus(
    $baseUrl,
    $storage
);

$collection = 'your_collection';
$items = $directus->items($collection);

// Get all items
$all_items = $items->get();
print_r($all_items);

// Get a specific item
$item = $items->get(1);
print_r($item);

// Create a new item
$new_item = $items->create(['name' => 'New Item', 'status' => 'published']);
print_r($new_item);

// Update an existing item
$updated_item = $items->update(['name' => 'Updated Item'], 1);
print_r($updated_item);

// Delete an item
$deleted_item = $items->delete(1);
print_r($deleted_item);
```

### Users

The `users` endpoint allows you to manage users in your Directus instance.

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$storage = new SessionStorage('directus_');

$directus = new Directus(
    $baseUrl,
    $storage
);

$users = $directus->users();

// Get all users
$all_users = $users->get();
print_r($all_users);

// Get a specific user
$user = $users->get('user_id');
print_r($user);

// Create a new user
$new_user = $users->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'password' => 'password123',
    'role' => 'administrator'
]);
print_r($new_user);

// Update an existing user
$updated_user = $users->update([
    'first_name' => 'Jane',
    'last_name' => 'Doe'
], 'user_id');
print_r($updated_user);

// Delete a user
$deleted_user = $users->delete('user_id');
print_r($deleted_user);
```

### Files

The `files` endpoint allows you to manage files in your Directus instance.

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$storage = new SessionStorage('directus_');

$directus = new Directus(
    $baseUrl,
    $storage
);

$files = $directus->files();

// Get all files
$all_files = $files->get();
print_r($all_files);

// Get a specific file
$file = $files->get('file_id');
print_r($file);

// Create a new file
$file_path = '/path/to/your/file.jpg';
$new_file = $files->create([
    'name' => basename($file_path),
    'tmp_name' => $file_path,
]);
print_r($new_file);

// Update an existing file
$updated_file = $files->update('file_id', ['title' => 'New Title']);
print_r($updated_file);

// Delete a file
$deleted_file = $files->delete('file_id');
print_r($deleted_file);
```

### Other Endpoints

The SDK provides access to all Directus endpoints, including:

-   `activity()`
-   `collections()`
-   `comments()`
-   `contentVersions()`
-   `dashboards()`
-   `extensions()`
-   `fields(string $collection)`
-   `flows()`
-   `folders()`
-   `notifications()`
-   `operations()`
-   `panels()`
-   `permissions()`
-   `policies()`
-   `presets()`
-   `relations()`
-   `revisions()`
-   `roles()`
-   `schema()`
-   `server()`
-   `settings()`
-   `shares()`
-   `translations()`
-   `utilities()`

Each endpoint provides methods for performing common operations, such as `get`, `create`, `update`, and `delete`. Refer to the Directus API documentation for more information on each endpoint and its available methods.

### Custom Calls

You can make custom API calls using the `makeCustomCall` method:

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$baseUrl = 'https://your-directus-instance.com';
$storage = new SessionStorage('directus_');

$directus = new Directus(
    $baseUrl,
    $storage
);

$uri = '/your/custom/endpoint';
$data = ['param1' => 'value1', 'param2' => 'value2'];
$method = 'GET';

$response = $directus->makeCustomCall($uri, $data, $method);
print_r($response);
```

## Storage

The SDK uses a `StorageInterface` to store authentication tokens. You can choose between session storage, cookie storage, or implement your own custom storage mechanism.

### Session Storage

Session storage uses PHP sessions to store authentication tokens. This is the default storage mechanism.

```php
use AlanTiller\DirectusSdk\Storage\SessionStorage;

$storage = new SessionStorage('directus_'); // Optional prefix
```

### Cookie Storage

Cookie storage uses cookies to store authentication tokens.

```php
use AlanTiller\DirectusSdk\Storage\CookieStorage;

$storage = new CookieStorage('directus_', '/'); // Optional prefix and domain
```

### Custom Storage

You can implement your own custom storage mechanism by creating a class that implements the `StorageInterface`.

```php
use AlanTiller\DirectusSdk\Storage\StorageInterface;

class MyCustomStorage implements StorageInterface
{
    public function set(string $key, $value): void
    {
        // Store the value
    }

    public function get(string $key)
    {
        // Retrieve the value
    }

    public function delete(string $key): void
    {
        // Delete the value
    }
}

$storage = new MyCustomStorage();
```

## Error Handling

The SDK throws exceptions for API errors. You can catch these exceptions and handle them appropriately.

```php
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Storage\SessionStorage;
use AlanTiller\DirectusSdk\Exceptions\DirectusException;

$baseUrl = 'https://your-directus-instance.com';
$storage = new SessionStorage('directus_');

$directus = new Directus(
    $baseUrl,
    $storage
);

try {
    $items = $directus->items('your_collection')->get();
    print_r($items);
} catch (DirectusException $e) {
    echo "API error: " . $e->getMessage() . PHP_EOL;
}
```

## Testing

The SDK includes a set of Pest PHP tests to ensure that it functions correctly. To run the tests, follow these steps:

1.  Install Pest PHP:

    ```bash
    composer require pestphp/pest --dev
    ```

2.  Run the tests:

    ```bash
    ./vendor/bin/pest
    ```

## Contributing

Contributions are welcome! Please submit a pull request with your changes.

## License

The Directus PHP SDK is licensed under the [MIT License](LICENSE).