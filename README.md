<p align="center"><img width="300" alt="Logo" src="https://cdn.slations.co.uk/images/Slations-Logo.svg"></p>

<br>

# Directus PHP SDK

The Slations unofficial PHP SDK for Directus 9


# Documentation

## Installation (Composer)

We're now on packagist!!! Making installation even easier, all you have to do is include our package `slations/directus-php-sdk` in your `composer.json` file `^1.0` for the version and then run composer update.

Or you can run `composer require slations/directus-php-sdk` and that will do all of the above for you :)

## Installation (Manual)

To install the Directus PHP SDK is super simple and easy. All you need to do is download the latest release from the releases page and place the PHP file in your 

## Usage

We've simplified the way you create a newinstance of the SDK, now you set the config settings in the construct rather than another function. You can still define the other variables as before in the construct as below.

```
<?php 

include 'Directus.php'; // Include the SDK Class - MANUAL

include 'vendor/autoload.php'; // Include the composer autoload - COMPOSER

$directus = new Slations\DirectusSdk\Directus("https://url.to.your.directus.install.io/");

```

Personally I perfer to set the namespace when creating a new instance as I think it is cleaner but it's down to personal preference.


## Config Options

### Setting the options

There are currently four config options a user can set, these need to be set in the order below. Options with a start by the title are required the rest are optional.

```
$directus = new Slations\DirectusSdk\Directus(option1*, option2, option3, option4);
```

### Option 1* | Set API URL

You set the root URL to your directus installation, this field is required for the SDK to work.


### Option 2 | Set Auth Storage

The default is currently `_SESSION` but can be changed the the following options to fit the end users needs.

`_SESSION` - This stores the three Auth variables in a PHP Session on the server-side this is the most secure method but requires you to define a session `session_start();`

`_COOKIE` - This stores the three Auth variables in Cookies on the client-side but will still store and use PHP Session on the server-side due to limitations in PHP Cookie support. This can be useful if you need to retreve the access token from JS using cookies.


### Option 3 | Set Auth Domain

This one is only used if option 2 has been set to `_COOKIE`. This sets the root auth domain of the cookie. By default it is `/` so it applies to the whole site but can be set to a folder if you only want the user to be authenticated in the subdirectory.


### Option 4 | Set Strip Headers

This option is mainly for development which is why it has been put at the end but the request passed back to the user can contain the headers as well as the content in the array. The default value is `true` but can be set to `false` if you need access to the headers of the request.



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

### Read By Query
```
$directus->get_items('articles', array(
  search => "",
  filter => array(
    "date_published" => array(
      "_gte" => "\$NOW" // If you use Directus provided dynamic options like $NOW make sure you ascape the item \$NOW so PHP knows it's not a PHP variable
    )
  )
));
```

### Read By Primary Key
```
$directus->get_items('articles', 15);
```

### Update Single Item
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
$directus->auth_password_reset('abc.def.ghi', 'N3wPa33W0rd');
```
