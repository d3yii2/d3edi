<?php

use yii\helpers\VarDumper;


/**
 * @var array $tableData
 */
if (class_exists('eaBlankonThema\widget\ThTableSimple2')) {
    echo eaBlankonThema\widget\ThTableSimple2\ThTableSimple2::widget($tableData);
} else {
    /**
     * @todo an alternative display should be introduced
     */
    echo VarDumper::dumpAsString($tableData);
}

