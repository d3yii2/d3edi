<?php

use eaBlankonThema\assetbundles\layout\LayoutAsset;
use eaBlankonThema\widget\ThTableSimple2;

LayoutAsset::register($this);

/**
 * @var \d3system\yii2\web\D3SystemView $this
 * @var array $tableData
 */
?>
<?= ThTableSimple2::widget($tableData);

