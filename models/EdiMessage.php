<?php

namespace d3yii2\d3edi\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3edi\models\base\EdiMessage as BaseEdiMessage;
use EDI\Reader;
use yii\helpers\Json;

/**
 * This is the model class for table "edi_message".
 */
class EdiMessage extends BaseEdiMessage
{

    /** @var Reader */
    private $reader;

    public function readEdiMessage(): Reader
    {
        if($this->reader){
            return $this->reader;
        }
        return $this->reader = new Reader($this->data);
    }

    /**
     * @throws \d3system\exceptions\D3ActiveRecordException
     */
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

    /**
     * Delete from edi_messages_ref
     * @param $model
     * @throws \Throwable
     * @throws \d3system\exceptions\D3ActiveRecordException
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteRef($model): void
    {
        foreach(EdiMessageRef::find()
            ->where([
                'sys_model_id' => SysModelsDictionary::getIdByClassName(get_class($model)),
                'ref_record_id' => $model->id
            ])
            ->all() as $ref
        ){
            $ref->message->setStatusNew();
            if(!$ref->message->save()){
                throw new D3ActiveRecordException($ref->message);
            }
            $ref->delete();
        }
    }

    /**
     * @throws \d3system\exceptions\D3ActiveRecordException
     */
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
    public static function getUnprocessed($columns = ['id', 'preperation_time', 'status', 'errror']): array
    {
        $res = self::find()
            ->select($columns)
            ->where('status !=:status',  ['status' => parent::STATUS_PROCESSED]);
        return $res->all();
    }

    public function getBgmMessageFunctionCode(): ?string
    {
        $r = $this->readEdiMessage();
        return $r->readEdiDataValue('BGM',3);
    }
}
