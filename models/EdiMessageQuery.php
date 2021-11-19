<?php

namespace d3yii2\d3edi\models;

use d3system\yii2\db\D3ActiveQuery;

/**
 * This is the ActiveQuery class for [[EdiMessage]].
 *
 * @see EdiMessage
 */
class EdiMessageQuery extends D3ActiveQuery
{
    /**
     * @return EdiMessage[]
     */
    public function allNews(): array
    {
        return $this->where(['status' => EdiMessage::STATUS_NEW])
            ->orderBy(['preperation_time' => SORT_ASC])
            ->all();
    }
}
