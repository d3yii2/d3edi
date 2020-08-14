<?php

namespace d3yii2\d3edi\models;

use d3yii2\d3edi\dictionaries\EdiCompanyDictionary;
use \d3yii2\d3edi\models\base\EdiCompany as BaseEdiCompany;

/**
 * This is the model class for table "edi_company".
 */
class EdiCompany extends BaseEdiCompany
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        EdiCompanyDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        EdiCompanyDictionary::clearCache();
    }
}
