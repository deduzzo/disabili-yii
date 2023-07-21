<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Pagamento]].
 *
 * @see Pagamento
 */
class PagamentoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Pagamento[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Pagamento|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
