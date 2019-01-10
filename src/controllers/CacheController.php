<?php

namespace carlcs\apicache\controllers;

use carlcs\apicache\Plugin;
use Craft;
use craft\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class CacheController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = ['resave-caches'];

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionResaveCaches(): Response
    {
        $settings = Plugin::getInstance()->getSettings();

        $request = Craft::$app->getRequest();
        $apiSecret = $request->getParam('secret');
        $delay = $request->getParam('delay') ?: 0;

        if (
            $settings->apiSecret === false ||
            $apiSecret !== $settings->apiSecret
        ) {
            throw new ForbiddenHttpException();
        }

        Plugin::getInstance()->getCache()->pushResaveCachesJob($delay);

        return $this->asJson(['success' => true]);
    }
}
