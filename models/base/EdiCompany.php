<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace d3yii2\d3edi\models\base;

use Yii;


/**
 * This is the base-model class for table "edi_company".
 *
 * @property integer $id
 * @property string $code
 * @property integer $ref_id
 *
 * @property \d3yii2\d3edi\models\EdiMessage[] $ediMessages
 * @property \d3yii2\d3edi\models\EdiMessage[] $ediMessages0
 * @property string $aliasModel
 */
abstract class EdiCompany extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'edi_company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'tinyint Unsigned' => [['id'],'integer' ,'min' => 0 ,'max' => 255],
            'integer Unsigned' => [['ref_id'],'integer' ,'min' => 0 ,'max' => 4294967295],
            [['code'], 'required'],
            [['code'], 'string', 'max' => 35]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'ref_id' => 'Ref ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdiMessages()
    {
        return $this->hasMany(\d3yii2\d3edi\models\EdiMessage::className(), ['interchange_recipient_company_id' => 'id'])->inverseOf('interchangeRecipientCompany');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdiMessages0()
    {
        return $this->hasMany(\d3yii2\d3edi\models\EdiMessage::className(), ['interchange_sender_company_id' => 'id'])->inverseOf('interchangeSenderCompany');
    }




}
