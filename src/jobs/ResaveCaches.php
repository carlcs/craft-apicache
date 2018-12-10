<?php

namespace carlcs\apicache\jobs;

use carlcs\apicache\Plugin;
use Craft;
use craft\helpers\FileHelper;
use craft\queue\BaseJob;

class ResaveCaches extends BaseJob
{
    // Constants
    // =========================================================================

    const DESCRIPTION = 'Resaving API caches';

    // Properties
    // =========================================================================

    /**
     * @var string[]
     */
    public $endpoints;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $totalEndpoints = count($this->endpoints);

        $cacheService = Plugin::getInstance()->getCache();
        $client = Craft::createGuzzleClient();

        foreach ($this->endpoints as $i => $endpoint) {
            $this->setProgress($queue, $i / $totalEndpoints);

            $uri = Craft::getAlias($endpoint);
            $filePath = $cacheService->getFilePath($uri);

            try {
                $response = $client->get($uri);
                FileHelper::writeToFile($filePath, $response->getBody());
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('api-cache', self::DESCRIPTION);
    }
}
