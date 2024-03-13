<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "determina".
 *
 * @property int $id
 * @property string|null $numero
 * @property string|null $pagamenti_da
 * @property string|null $pagamenti_a
 * @property string|null $data
 * @property float|null $importo
 * @property boolean $storico
 * @property boolean $non_ordinaria
 * @property string|null $descrizione
 *
 * @property DeterminaGruppoPagamento[] $determinaGruppoPagamentos
 * @property GruppoPagamento[] $gruppos
 * @property Ricovero[] $ricoveros
 */
class Determina extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'determina';
    }

    public static function getAllDetermineMap()
    {
        return array_column(self::find()->select(['id','desc' => 'CONCAT(numero," - ", descrizione)'])->where(['storico' => false])->orderBy('data desc')->asArray()->all(),'desc', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data','pagamenti_da','pagamenti_a'], 'safe'],
            [['descrizione'], 'string'],
            [['storico','non_ordinaria'], 'boolean'],
            [['importo'], 'number'],
            [['numero'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'numero' => 'Numero',
            'data' => 'Data',
            'pagamenti_da' => 'Pagamenti Da',
            'pagamenti_a' => 'Pagamenti A',
            'importo' => 'Importo',
            'storico' => 'Storico',
            'non_ordinaria' => 'Non Ordinaria',
            'descrizione' => 'Descrizione',
        ];
    }

    /**
     * Gets query for [[DeterminaGruppoPagamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeterminaGruppoPagamentos()
    {
        return $this->hasMany(DeterminaGruppoPagamento::class, ['id_determina' => 'id']);
    }

    /**
     * Gets query for [[Gruppos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppos()
    {
        return $this->hasMany(GruppoPagamento::class, ['id' => 'id_gruppo'])->viaTable('determina_gruppo_pagamento', ['id_determina' => 'id']);
    }

    /**
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_determina' => 'id']);
    }
}
