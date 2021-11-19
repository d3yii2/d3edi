<?php

namespace d3yii2\d3edi\controllers;

use d3system\commands\D3CommandController;
use d3yii2\d3edi\components\MessageProcessing;
use Yii;
use yii\console\ExitCode;


class ProcessIncomingEdiController extends D3CommandController
{

    /**
     * default action
     * @param string $messageProcessingComponentName
     * @return int
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex(string $messageProcessingComponentName): int
    {
        if(!Yii::$app->has($messageProcessingComponentName)){
            $message = 'Can not find messageProcessingComponentName: ' . $messageProcessingComponentName;
            $this->out($message);
            Yii::error($message);
            return ExitCode::CONFIG;
        }

        /** @var MessageProcessing $processingCompnent */
        $processingCompnent = Yii::$app->get($messageProcessingComponentName);
        $processingCompnent->connect($this);
        $processingCompnent->downloadFtp();
        $processingCompnent->loadEdi($messageProcessingComponentName);
        return ExitCode::OK;
    }

}

