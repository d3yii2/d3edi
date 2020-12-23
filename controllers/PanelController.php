<?php

namespace d3yii2\d3edi\controllers;

use d3yii2\d3edi\models\EdiMessage;
use d3yii2\d3edi\Module;
use depo3\edi\accessRights\Depo3EdiFullUserRole;
use unyii2\yii2panel\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use Yii;

/**
 * Class PanelController
 * @package depo3\edi\controllers
 * @property Module $module
 */
class PanelController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'message',
                        ],
                        'roles' => $this->module->rulesMessageRoles??['@'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @param string $action
     * @return string
     */
    public function actionMessage(string $action = '')
    {
        $tableData = [
            'title' => Yii::t('d3edi', 'EDI Message'),
            'columns' => [
                ['attribute' => 'id', 'header' => EdiMessage::instance()->getAttributeLabel('id')],
                ['attribute' => 'preperation_time', 'header' => EdiMessage::instance()->getAttributeLabel('preperation_time'), 'format' => 'date'],
                ['attribute' => 'status', 'header' => EdiMessage::instance()->getAttributeLabel('status')],
                ['attribute' => 'errror', 'header' => EdiMessage::instance()->getAttributeLabel('error')],
            ],
            'data' => EdiMessage::getUnprocessed()
        ];
    
        return $this->renderPartial('message', compact('tableData'));
    }
}
