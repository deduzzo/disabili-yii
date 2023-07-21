<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Conto]].
 *
 * @see Conto
 */
class ContoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Conto[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Conto|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
