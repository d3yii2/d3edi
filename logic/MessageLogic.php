<?php


namespace d3yii2\d3edi\logic;


use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3edi\dictionaries\EdiCompanyDictionary;
use d3yii2\d3edi\dictionaries\EdiMessageTypeDictionary;
use d3yii2\d3edi\models\EdiMessage;
use EDI\Reader;

class MessageLogic
{


    public function saveIn(string $ediMessage): void
    {
        $model = new EdiMessage();
        $model->read_time = date('Y-m-d H:i:s');
        $model->type = EdiMessage::TYPE_IN;
        $model->data = $ediMessage;
        $r = new Reader($ediMessage);
        $model->interchange_sender_company_id = EdiCompanyDictionary::getIdByName($r->readUNBInterchangeSender());
        $model->interchange_recipient_company_id = EdiCompanyDictionary::getIdByName($r->readUNBInterchangeRecipient());
        $model->preperation_time = $r->readUNBDateTimeOfPreparation();
        $model->messageReferenceNumber = $r->readUNHmessageNumber();
        $model->message_type_id = EdiMessageTypeDictionary::getIdByName($r->readUNHmessageType());
        $model->messageRelease = $r->readUNHmessageRealise();
        $model->status = EdiMessage::STATUS_NEW;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }
    }
}