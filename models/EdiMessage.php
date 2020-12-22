<?php

namespace d3yii2\d3edi\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use \d3yii2\d3edi\models\base\EdiMessage as BaseEdiMessage;
use depo3\edi\models\EdiMessageRef;
use EDI\Reader;
use yii\helpers\Json;

/**
 * This is the model class for table "edi_message".
 */
class EdiMessage extends BaseEdiMessage
{

    public function readEdiMessage(): Reader
    {
        return new Reader($this->data);
    }

    public function saveProcessed($model): void
    {
        $ref = new EdiMessageRef();
        $ref->message_id = $this->id;
        $ref->sys_model_id = SysModelsDictionary::getIdByClassName(get_class($model));
        $ref->ref_record_id = $model->id;
        if(!$ref->save()){
            throw new D3ActiveRecordException($ref);
        }
        $this->status = self::STATUS_PROCESSED;
        if(!$this->save()){
            throw new D3ActiveRecordException($this);
        }
    }

    public function saveError($error): void
    {
        $this->status = self::STATUS_ERROR;
        $this->errror = Json::encode($error);
        if (!$this->save()) {
            throw new D3ActiveRecordException($this);
        }
    }
    
    /**
     * @return EdiMessage[]
     */
    public static function getUnprocessed($columns = ['id', 'preperation_time', 'status', 'errror'])
    {
        $res = self::find()
            ->select($columns)
            ->where('status !=:status',  ['status' => parent::STATUS_PROCESSED]);
        return $res->all();
    }
}
