# THIS IS NOT A PRODUCTION VERSION

This is still in heavy development and is not a working project, we are working as hard as we can to get this finished.


<p align="center"><img width="400" alt="Logo" src="https://cdn.slations.co.uk/images/Slations-Logo.svg"></p>

<br>

# Directus PHP SDK

The Slations unofficial PHP SDK for Directus 9


# Documentation

## Installation

To install the Directus PHP SDK is super simple and easy. First download the latest release from the releases section of Github and extract the files. Then copy the following files into your project and your ready to get started.
```
Desired Folder
|- Directus.php
```

## Usage

```
<?php 

include 'Directus.php'; // Include the SDK Class

$directus = new Directus(); // Create a new Directus SDK instance

// Setup the config for the Directus SDK
$directus->config([
  "base_url" => "https://url.to.your.directus.install.io/",
  "" => ""
]);

```





## Auth

### Login

```
$directus->auth_user([]);
```

### Refresh


### Logout


### Request a Password Reset



### Reset a Password


## Users

### Invite a New User


### Accept a User Invite


### Enable Two-Factor Authentication


### Disable Two-Factor Authentication


### Get the Current User


### Update the Current Users
