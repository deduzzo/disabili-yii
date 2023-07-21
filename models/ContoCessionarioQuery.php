<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ContoCessionario]].
 *
 * @see ContoCessionario
 */
class ContoCessionarioQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ContoCessionario[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ContoCessionario|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
