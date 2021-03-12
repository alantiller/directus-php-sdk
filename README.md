<p align="center"><img width="400" alt="Logo" src="https://cdn.slations.co.uk/images/Slations-Logo.svg"></p>

<br>

# Directus PHP SDK

The Slations unofficial PHP SDK for Directus 9 - I am working on tidying up these docs but it's 1:30AM and I need some sleep... or coffee so please bare with me :)


# Documentation

## Installation

To install the Directus PHP SDK is super simple and easy. All you need to do is download the latest release from the releases page and place the PHP file in your 

## Usage
```
<?php 

include 'Directus.php'; // Include the SDK Class

$directus = new Directus(); // Create a new Directus SDK instance

// Setup the config for the Directus SDK
$directus->config([
  "base_url" => "https://url.to.your.directus.install.io/",
  "auth_storage" => "_SESSION" // This can be set to _SESSION or if you want to store it as a cookie you can also use _COOKIE
]);

```


## Config Options

### Get / Set API URL
```
"base_url" => "https://url.to.your.directus.install.io/"
```

### Setup the Auth Storage method
```
"auth_storage" => "_SESSION"
```

`_SESSION` - This stores the three Auth variables in a PHP Session on the server-side this is the most secure method but requires you to define a session `session_start();`

`_COOKIE` - This stores the three Auth variables in Cookies on the client-side this can be useful if you need to retreve the access token from JS using cookies.


## Global

### Getting the API URL
```
$directus->base_url;
```


## Items

### Create a Single Item
```
$directus->create_items('articles', array(
  "status" => "draft",
  "title" => "example",
  "slug" => "example"
));
```

### Read All Items
```
$directus->get_items('articles');
```

### Read By Query (TODO: Not yet completed)
```
$directus->get_items('articles', array(
  search => "",
  filter => array(
    "date_published" => array(
      "_gte" => "$NOW"
    )
  )
));
```

### Read By Primary Key(s)
```
// Single
$directus->get_items('articles', 15);

// Multiple (TODO: Not yet completed)
$directus->get_items('articles', array(15, 42));
```

### Update a Single Item
```
$directus->update_items('articles', array("title" => "example_new"), 15);
```

### Delete an Item(s)
```
// One
$directus->delete_items('articles', 15);

// Multiple
$directus->delete_items('articles', array(15, 42));
```


## Auth

### Get / Set Token
Setting a static auth token, if someone authenticates their authentication will override this auth token!
```
$directus->auth_token('abc.def.ghi');

$directus->auth_token;
```

### Login
When you submit a login request it will either respone with en error "errors" or it will just return true. If it has returned try then the login was successful and the Auth has been stored using the method defined in the config (_SESSION by default). The login with AutoRefresh so you will not need to worry about the token expiring.
```
$directus->auth_user('demo@slations.co.uk', 'Pa33w0rd');
```

### Refresh

By default the SDK will auto refresh every fifteen minuites so the token is never expired. We will add the ability to turn off autoRefresh in the future.

### Logout
```
$directus->auth_logout();
```

### Request a Password Reset
The second value is optional if you want to sent a custom return URL for the reset email.
```
$directus->auth_password_request('demo@slations.co.uk', 'https://example.com/comfirm');
```

### Reset a Password
Note: the token passed in the first parameter is sent in an email to the user when using `auth_password_request`
```
$directus->auth_password_reset('the.id.passed.from.the.email', 'The1rN3wPa33W0rd');
```