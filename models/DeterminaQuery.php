<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Determina]].
 *
 * @see Determina
 */
class DeterminaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Determina[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Determina|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
