<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[DocumentoTipologia]].
 *
 * @see DocumentoTipologia
 */
class DocumentoTipologiaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return DocumentoTipologia[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return DocumentoTipologia|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
