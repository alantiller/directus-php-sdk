<?php

/*
 * Directus-PHP-SDK (https://github.com/alantiller/directus-php-sdk)
 * Copyright (c) Alan Tiller (https://www.alantiller.co.uk/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace AlanTiller\DirectusSdk;

/** Component that provides all features and utilities for secure authentication of individual users */
final class Directus extends ApiManager
{

    /**
	 * @param string $base_url the base url of the directus installation the user wants to connect too
	 * @param string $storage_prefix (optional) the prefix names the session so multiple directus sdk's can be called
	 */
    public function __construct($base_url, $storage_prefix = 'directus')
    {
        parent::__construct($base_url, $storage_prefix);

        $this->init_session_if_necessary();
		$this->enhance_http_security();
    }

    /** Initializes the session and sets the correct configuration */
    private function init_session_if_necessary()
    {
        if (\session_status() === \PHP_SESSION_NONE) {
            // use cookies to store session IDs
            \ini_set('session.use_cookies', 1);

            // use cookies only
            \ini_set('session.use_only_cookies', 1);

            // do not send session IDs in URLs
            \ini_set('session.use_trans_sid', 0);

            // start the session
            \session_start();
        }
    }

    /** Improves the application's security over HTTP(S) by setting specific headers */
    private function enhance_http_security()
    {
        // remove exposure of PHP version (at least where possible)
        \header_remove('X-Powered-By');

        // if the user is signed in
        // if (CHECK WHEN USER IS LOGGED IN: TODO) {
        //     // prevent clickjacking
        //     \header('X-Frame-Options: sameorigin');

        //     // prevent content sniffing (MIME sniffing)
        //     \header('X-Content-Type-Options: nosniff');

        //     // disable caching of potentially sensitive data
        //     \header('Cache-Control: no-store, no-cache, must-revalidate', true);
        //     \header('Expires: Thu, 19 Nov 1981 00:00:00 GMT', true);
        //     \header('Pragma: no-cache', true);
        // }
    }

    /** Auth */
    public function auth()
    {
        return new AuthManager(parent::$this->base_url, parent::$this->storage_prefix);
    }

    /** Items */
    public function items($collection)
    {
        return new ItemManager(parent::$this->base_url, parent::$this->storage_prefix, '/items/' . $collection);
    }
}
