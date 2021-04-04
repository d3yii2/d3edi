<?php


namespace d3yii2\d3edi\logic;


use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3edi\dictionaries\EdiCompanyDictionary;
use d3yii2\d3edi\dictionaries\EdiMessageTypeDictionary;
use d3yii2\d3edi\models\EdiMessage;
use EDI\Reader;
use yii\helpers\VarDumper;

class MessageLogic
{

    /**
     * @param string $ediMessage
     * @param string $fileName
     * @throws D3ActiveRecordException
     */
    public function saveIn(string $ediMessage, string $fileName): void
    {
        $model = new EdiMessage();
        $model->read_time = date('Y-m-d H:i:s');
        $model->type = EdiMessage::TYPE_IN;
        $model->data = $ediMessage;
        if($fileName){
            $model->file_name = $fileName;
        }
        $r = new Reader($ediMessage);
        if($interchangeSender = $r->readUNBInterchangeSender()) {
            $model->interchange_sender_company_id = EdiCompanyDictionary::getIdByName($interchangeSender);
        }
        $model->interchange_recipient_company_id = EdiCompanyDictionary::getIdByName($r->readUNBInterchangeRecipient());
        $model->preperation_time = $r->readUNBDateTimeOfPreparation();
        $model->messageReferenceNumber = $r->readUNHmessageNumber();
        if(!$unhId = $r->readUNHmessageType()){
            throw new \Exception('Can not find UNH segment. Error. ' . VarDumper::dumpAsString($r->errors()));
        }
        $model->message_type_id = EdiMessageTypeDictionary::getIdByName($unhId);
        $model->messageRelease = $r->readUNHmessageRealise();
        $model->status = EdiMessage::STATUS_NEW;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }
    }
}