<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Gruppo]].
 *
 * @see Gruppo
 */
class GruppoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Gruppo[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Gruppo|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
