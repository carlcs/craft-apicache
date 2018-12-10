<?php

namespace carlcs\apicache;

use carlcs\apicache\services\Cache;
use Craft;
use craft\base\Element;
use yii\base\Event;
use yii\web\JsonResponseFormatter;
use yii\web\Response;

/**
 * @property Cache $cache
 * @property Settings $settings
 * @method Settings getSettings()
 * @method static Plugin getInstance()
 */
class Plugin extends \craft\base\Plugin
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'cache' => Cache::class,
        ]);

        $this->_registerCraftEventHandlers();
        $this->_handleApiRequest();
    }

    /**
     * Returns the api cache service.
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->get('cache');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Handles API requests.
     */
    private function _handleApiRequest()
    {
        $request = Craft::$app->getRequest();

        if (
            !$request->getIsSiteRequest() ||
            !$request->getIsGet() ||
            $request->getIsActionRequest()
        ) {
            return;
        }

        $url = $request->getUrl();
        $filePath  = $this->getCache()->getFilePath($url);

        if (is_file($filePath)) {
            $response = Craft::$app->getResponse();

            // Set the JSON headers
            (new JsonResponseFormatter())->format($response);

            // Set the cached JSON on the response and return
            $response->format = Response::FORMAT_RAW;
            $response->content = file_get_contents($filePath);

            Craft::$app->end();
        }
    }

    /**
     * Registers Craft event handlers.
     */
    private function _registerCraftEventHandlers()
    {
        if (!$this->getSettings()->resaveOnElementSave) {
            return;
        }

        Event::on(Element::class, Element::EVENT_AFTER_SAVE, function() {
            $this->getCache()->pushResaveCachesJob();
        });

        Event::on(Element::class, Element::EVENT_AFTER_MOVE_IN_STRUCTURE, function() {
            $this->getCache()->pushResaveCachesJob();
        });

        Event::on(Element::class, Element::EVENT_AFTER_DELETE, function() {
            $this->getCache()->pushResaveCachesJob();
        });
    }
}
