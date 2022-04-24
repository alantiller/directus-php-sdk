<?php

/*
 * Directus-PHP-SDK (https://github.com/alantiller/directus-php-sdk)
 * Copyright (c) Alan Tiller (https://www.alantiller.co.uk/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace AlanTiller\DirectusSdk;

final class AuthManager extends ApiManager {

    /** @var string the url section selecting the item */
	private $item_url;

    /**
	 * @param string $base_url the base url of the directus installation the user wants to connect too
	 * @param string $storage_prefix the prefix names the session so multiple directus sdk's can be called
	 * @param bool $item_url 
	 */
    public function __construct($base_url, $storage_prefix)
    {
        parent::__construct($base_url, $storage_prefix);
    }


}