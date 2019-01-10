<?php

namespace carlcs\apicache;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $enableRouting = true;

    /**
     * @var bool
     */
    public $resaveOnElementSave = true;

    /**
     * @var int
     */
    public $resaveJobDelay = 30;

    /**
     * @var string|bool
     */
    public $apiSecret = false;

    /**
     * @var string
     */
    public $cacheFolder = '@runtime/cache/api-cache';

    /**
     * @var string[]
     */
    public $endpoints = [];
}
