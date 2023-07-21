<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Anagrafica]].
 *
 * @see Anagrafica
 */
class AnagraficaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Anagrafica[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Anagrafica|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
