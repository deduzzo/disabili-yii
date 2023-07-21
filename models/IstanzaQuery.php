<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Istanza]].
 *
 * @see Istanza
 */
class IstanzaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Istanza[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Istanza|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
