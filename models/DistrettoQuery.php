<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Distretto]].
 *
 * @see Distretto
 */
class DistrettoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Distretto[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Distretto|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
