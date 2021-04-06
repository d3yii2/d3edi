<?php

namespace d3yii2\d3edi\controllers;

use d3system\commands\D3CommandController;
use d3yii2\d3edi\components\MessageProcessing;
use depo3\edi\Module;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use yii\console\ExitCode;

/**
* Class ProcessIncomingEdiController* @property Module $module
*/
class ProcessIncomingEdiController extends D3CommandController
{

    /**
     * default action
     * @return int
     */
    public function actionIndex(string $messageProcessingComponentName): int
    {
        if(!\Yii::$app->has($messageProcessingComponentName)){
            $message = 'Can not find messageProcessingComponentName: ' . $messageProcessingComponentName;
            $this->out($message);
            \Yii::error($message);
            return ExitCode::CONFIG;
        }

        /** @var MessageProcessing $processingCompnent */
        $processingCompnent = \Yii::$app->get($messageProcessingComponentName);
        $processingCompnent->connect($this);
        $processingCompnent->downloadFtp();
        $processingCompnent->loadEdi($messageProcessingComponentName);
        return ExitCode::OK;
    }

}

