<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Isee]].
 *
 * @see Isee
 */
class IseeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Isee[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Isee|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
