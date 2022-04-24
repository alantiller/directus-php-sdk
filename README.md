# Directus PHP SDK

> The Directus PHP SDK provides an intuitive interface for the Directus 9 API 
> from within a PHP-powered project. (https://github.com/directus/directus)

## Installation

```
composer require alantiller/directus-php-sdk
```

## Usage

```php
include 'vendor/autoload.php';

$directus = new \AlanTiller\DirectusSdk\Directus("https://admin.directus.io");
```

## Reference

### Constructor

This is the starting point to use the SDK. You need to create an instance and invoke methods from it. In most cases a
single instance is sufficient, but in case you need more, you need to define
the `storage_prefix` option.

> Please note that from v2.0.0 onwards the option to store values as cookies has 
> been depricated. This is for security purpouses. If you need the tokens stored 
> as cookies please set them maunally.

```php 
include 'vendor/autoload.php';

$directus = new \AlanTiller\DirectusSdk\Directus($base_url, $storage_prefix);
```

- `base_url` [required] _String_ - A string pointing to your Directus instance. E.g. `https://admin.directus.io`

- `storage_prefix` [optional] _String_ - ToDo

## Auth

Defines how authentication is handled by the SDK.

### Get current token

```php
$token = $directus->auth()->token;
```

### Login

#### With credentials

```php
$directus->auth()->login([
	'email' => 'admin@example.com',
	'password' => 'd1r3ctu5'
]);
```

#### With static tokens

```php
$directus->auth()->static('static_token');
```

### Logout

```php
$directus->auth()->logout();
```

## Items

You can get an instance of the item handler by providing the collection to the
`items` function. The following examples will use the `Article` type.

```php
include 'vendor/autoload.php';

$directus = new \AlanTiller\DirectusSdk\Directus('http://admin.directus.io');

$articles = $directus->items('articles');
```
 
### Create Single Item

```php
$articles->create([
    'title' => 'My New Article'
]);
```

### Create Multiple Items

```js
await articles.createMany([
	{
		title: 'My First Article',
	},
	{
		title: 'My Second Article',
	},
]);
```

### Read By Query

```js
await articles.readByQuery({
	search: 'Directus',
	filter: {
		date_published: {
			_gte: '$NOW',
		},
	},
});
```

### Read All

```js
await articles.readByQuery({
	// By default API limits results to 100.
	// With -1, it will return all results, but it may lead to performance degradation
	// for large result sets.
	limit: -1,
});
```

### Read Single Item

```js
await articles.readOne(15);
```

Supports optional query:

```js
await articles.readOne(15, {
	fields: ['title'],
});
```

### Read Multiple Items

```js
await articles.readMany([15, 16, 17]);
```

Supports optional query:

```js
await articles.readMany([15, 16, 17], {
	fields: ['title'],
});
```

### Update Single Item

```js
await articles.updateOne(15, {
	title: 'This articles now has a different title',
});
```

Supports optional query:

```js
await articles.updateOne(
	42,
	{
		title: 'This articles now has a similar title',
	},
	{
		fields: ['title'],
	}
);
```

### Update Multiple Items

```js
await articles.updateMany([15, 42], {
	title: 'Both articles now have the same title',
});
```

Supports optional query:

```js
await articles.updateMany(
	[15, 42],
	{
		title: 'Both articles now have the same title',
	},
	{
		fields: ['title'],
	}
);
```

### Delete

```js
// One
await articles.deleteOne(15);

// Multiple
await articles.deleteMany([15, 42]);
```

### Request Parameter Overrides

To override any of the axios request parameters, provide an additional parameter with a `requestOptions` object:

```js
await articles.createOne(
	{ title: 'example' },
	{ fields: ['id'] },
	{
		requestOptions: {
			headers: {
				'X-My-Custom-Header': 'example',
			},
		},
	}
);
```