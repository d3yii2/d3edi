<?php

namespace d3yii2\d3edi\dictionaries;

use Yii;
use d3yii2\d3edi\models\EdiCompany;
use yii\helpers\ArrayHelper;
use d3system\exceptions\D3ActiveRecordException;

class EdiCompanyDictionary{

    private const CACHE_KEY_LIST = 'EdiCompanyDictionaryList';

    public static function getIdByName(string $code): int
    {
        $list = self::getList();
        if($id = (int)array_search($code, $list, true)){
            return $id;
        }
        $model = new EdiCompany();
        $model->code = $code;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }

        return $model->id;

    }

    public static function getList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST,
            static function () {
                return ArrayHelper::map(
                    EdiCompany::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'code',
                    ])
                    ->asArray()
                    ->all()
                ,
                'id',
                'name'
                );
            }
        );
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST);
    }
}
