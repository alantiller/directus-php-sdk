<?php

/*
 * Directus-PHP-SDK (https://github.com/alantiller/directus-php-sdk)
 * Copyright (c) Alan Tiller (https://www.alantiller.co.uk/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace AlanTiller\DirectusSdk;

final class ItemManager extends ApiManager {

    /** @var string the url section selecting the item */
	private $item_url;

    /**
	 * @param string $base_url the base url of the directus installation the user wants to connect too
	 * @param string $storage_prefix the prefix names the session so multiple directus sdk's can be called
	 * @param bool $item_url 
	 */
    public function __construct($base_url, $storage_prefix, $item_url)
    {
        parent::__construct($base_url, $storage_prefix);

        $this->item_url = $item_url;
    }

    // Get
    public function get($data = false)
    {
        if (is_array($data)):
            return parent::make_call($this->item_url, $data, 'GET');
        elseif (is_integer($data) || is_string($data)):
            return parent::make_call($this->item_url . '/' . $data, false, 'GET');
        else:
            return parent::make_call($this->item_url, false, 'GET');
        endif;
    }

    // Create
    public function create($fields)
    {
        return parent::make_call($this->item_url, $fields, 'POST');
    }

    // Update
    public function update($fields, $id = null)
    {
        if ($id != NULL):
            return parent::make_call($this->item_url . '/' . $id, $fields, 'PATCH');
        else:
            return parent::make_call($this->item_url, $fields, 'PATCH');
        endif;
    }

    // Delete
    public function delete($id)
    {
        if (is_array($id)):
            return parent::make_call($this->item_url, $id, 'DELETE');
        else:
            return parent::make_call($this->item_url . '/' . $id, false, 'DELETE');
        endif;
    }
}