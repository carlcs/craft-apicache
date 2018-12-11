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
    const USER_AGENT_HEADER = 'API cache plugin';

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
        $client = Craft::createGuzzleClient([
            'headers' => ['User-Agent' => self::USER_AGENT_HEADER],
        ]);

        foreach ($this->endpoints as $i => $endpoint) {
            $this->setProgress($queue, $i / $totalEndpoints);

            $uri = Craft::getAlias($endpoint);
            $filePath = $cacheService->getFilePath($uri);

            try {
                $response = $client->get($uri);
                FileHelper::writeToFile($filePath, $response->getBody());
            } catch (\Throwable $e) {
                Craft::error("Couldn’t resave endpoint {$endpoint}: {$e->getMessage()}", __METHOD__);
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
