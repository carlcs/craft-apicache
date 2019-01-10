<?php

namespace carlcs\apicache\console\controllers;

use carlcs\apicache\Plugin;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manages API caches.
 */
class CacheController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $defaultAction = 'resave-caches';

    /**
     * @var int The time in seconds job execution is delayed.
     */
    public $delay = 0;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);

        if ($actionID === 'resave-caches') {
            $options[] = 'delay';
        }

        return $options;
    }

    /**
     * Re-saves all API caches.
     * Use `--delay=<seconds>` to set a delay for job execution.
     */
    public function actionResaveCaches()
    {
        Plugin::getInstance()->getCache()->pushResaveCachesJob($this->delay);

        $this->stdout('Cache resaving job added to the queue.'.PHP_EOL, Console::FG_GREEN);
    }
}
