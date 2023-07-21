<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Ricovero]].
 *
 * @see Ricovero
 */
class RicoveroQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Ricovero[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Ricovero|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
