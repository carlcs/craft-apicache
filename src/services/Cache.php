<?php

namespace carlcs\apicache\services;

use carlcs\apicache\jobs\ResaveCaches;
use carlcs\apicache\Plugin;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\FileHelper;

/**
 * @property array|string[] $endpoints
 */
class Cache extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Pushes a resave caches job, and clears existing ones.
     */
    public function pushResaveCachesJob()
    {
        $queue = Craft::$app->getQueue();

        $settings = Plugin::getInstance()->getSettings();
        $endpoints = $this->getEndpoints();

        if ($jobId = $this->_getCurrentJobId()) {
            $queue->release($jobId);
        }

        $queue
            ->delay($settings->resaveJobDelay)
            ->push(new ResaveCaches(compact('endpoints')));
    }

    /**
     * Resaves caches.
     */
    public function resaveCaches()
    {
        $client = Craft::createGuzzleClient();

        foreach ($this->getEndpoints() as $endpoint) {
            $uri = Craft::getAlias($endpoint);
            $filePath = $this->getFilePath($uri);

            try {
                $response = $client->get($uri);
                FileHelper::writeToFile($filePath, $response->getBody());
            } catch (\Throwable $e) {
                Craft::error('Couldn’t resave endpoint: '.$e->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * Clears caches.
     */
    public function clearCaches()
    {
        $settings = Plugin::getInstance()->getSettings();
        $cacheFolder = Craft::getAlias($settings->cacheFolder);

        FileHelper::removeDirectory($cacheFolder);
    }

    /**
     * @return string[]
     */
    public function getEndpoints(): array
    {
        $settings = Plugin::getInstance()->getSettings();
        $endpoints = $settings->endpoints;

        if (is_callable($endpoints)) {
            $endpoints = $endpoints();
        }

        return $endpoints;
    }

    /**
     * @param string $uri
     * @return string
     */
    public function getFilePath(string $uri): string
    {
        $settings = Plugin::getInstance()->getSettings();
        $cacheFolder = Craft::getAlias($settings->cacheFolder);

        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        return FileHelper::normalizePath("{$cacheFolder}/{$path}/{$query}/index.json");
    }

    // Private Methods
    // =========================================================================

    /**
     * @return string|null
     */
    private function _getCurrentJobId()
    {
        return (new Query())
            ->select(['id'])
            ->from('{{%queue}}')
            ->where(['description' => ResaveCaches::DESCRIPTION])
            ->scalar();
    }
}
