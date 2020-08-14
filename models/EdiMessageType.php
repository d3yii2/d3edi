<?php

namespace d3yii2\d3edi\models;

use d3yii2\d3edi\dictionaries\EdiMessageTypeDictionary;
use \d3yii2\d3edi\models\base\EdiMessageType as BaseEdiMessageType;

/**
 * This is the model class for table "edi_message_type".
 */
class EdiMessageType extends BaseEdiMessageType
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        EdiMessageTypeDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        EdiMessageTypeDictionary::clearCache();
    }
}
