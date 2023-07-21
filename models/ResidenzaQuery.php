<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Residenza]].
 *
 * @see Residenza
 */
class ResidenzaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Residenza[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Residenza|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
