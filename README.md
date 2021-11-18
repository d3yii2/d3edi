# [EDIFACT messaging[(https://en.wikipedia.org/wiki/EDIFACT) 


## Features
 - Collect EDI messages in DB
 - Other processes process collected messages and registries
   * status processed and ref records
   * status error and error message 

## used packages 
[sabas/edifac](https://github.com/php-edifact/edifact) - Tools to process EDI messages in UN/EDIFACT format
[php-edifact/edifact-mapping](https://github.com/php-edifact/edifact-mapping) -  xml files for EDIFACT messages
[unyii2/yii2-panel](https://github.com/unyii2/yii2-panel) - panel controller and widget 

## DB Schema
![DB Schema](/doc/DbSchema.png) 


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ composer require d3yii2/d3edi "*"
```

or add

```
"d3yii2/d3edi": "*"
```

to the `require` section of your `composer.json` file.


## Methods


## Usage
### map edi fata to model attributes

```php


class DepoEdiBooking extends BaseDepoEdiBooking
{
    public function loadEdi($ediMessage): void
    {
        $r = $ediMessage->readEdiMessage();
        $this->booking_ref_number = $r->readEdiDataValue(['RFF',['1'=>'BN']],1,0);
        $this->carier = $r->readEdiDataValue(['NAD',['1'=>'CA']],2,0);
        $this->containerType = $r->readEdiDataValue(['EQD',['1'=>'CN']],3,0);
        if(!$this->cont_type_id = CargoContTypeDictionary::getIdByEdiCode($containerType)){
            $this->cont_type_id = null;
            $this->addError('cont_type_id','Undefined container type: ' . $containerType);
        }
    }
}


````
### procesing

```php
        $cnt = 0;
        foreach(EdiMessage::find()->allNews() as $ediMessage){
            if(!$transaction = $this->module->db->begintransaction()){
                throw new \yii\db\Exception('can not start Begin transaction');
            }
            try {
                $ediBooking = new DepoEdiBooking();
                $ediBooking->loadEdi($ediMessage);
                if ($ediBooking->hasErrors() || !$ediBooking->validate()) {
                    $ediMessage->saveError($ediBooking->getErrors());
                    continue;
                }
                if (!$ediBooking->save()) {
                    throw new D3ActiveRecordException($ediBooking);
                }

                $ediMessage->saveProcessed($ediBooking);

                $transaction->commit();
                $cnt ++;
                continue;
            }catch (Exception $e){
                $transaction->rollBack();
                Yii::error($e->getMessage());
                Yii::error($e->getTraceAsString());
                $ediMessage->saveError($e->getMessage());
            }
        }

        return $cnt;

    }

```

### Panle for unprocessed messages

 For showing unprocessed messages can use panel solution [Yii2Panel](https://github.com/unyii2/yii2-panel)
 
#### add widget
```php 
echo \unyii2\yii2panel\PanelWidget::widget([
    'name' => 'MyAllerts',
]);
```

#### to module add parameter panels
```php 
class MyModule extends Module
{
    
    /**
     * @var array panels for PanelWidgets
     */
    public $panels;
```

#### in module config add widget
```php 
        'mymodule' => [
            'class' => 'MyModule',
            'panels' => [
                'MyAllerts' =>
                [
                    [
                        'route' => 'edi/panel/message',
                     ]
                 ]
            ],
        ],
```

#### in EDI module can set role for access to panel. Otherwise every authorised user has access to panle widget

```php
        'edi' => [
            'class' => 'd3yii2\d3edi\Module',
            'accessRulesMessageRoles' => ['Depo3EdiFull']
        ],
```
