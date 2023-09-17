<?php

namespace app\controllers;

use yii\data\SqlDataProvider;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $query = "select DISTINCT a.cognome_nome, i.id, i.id_distretto, a.data_nascita, CASE 
        WHEN 
            DATE_FORMAT(CURDATE(), '%m-%d') >= DATE_FORMAT(a.data_nascita, '%m-%d') THEN 
                YEAR(CURDATE()) - YEAR(a.data_nascita) 
        ELSE 
            (YEAR(CURDATE()) - YEAR(a.data_nascita)) - 1 END as eta from movimento m, conto c,anagrafica a,istanza i
                 where m.id_conto = c.id AND
        a.id = i.id_anagrafica_disabile AND
        c.id = m.id_conto
            AND i.attivo = true AND i.chiuso = false AND i.id not in
     (select i2.id from movimento m2, istanza i2, conto c2
     where m2.id_conto = c2.id AND c2.id_istanza = i2.id AND m2.is_movimento_bancario = true)";
        $dataProvider = new SqlDataProvider([
            'sql' => $query,
        ]);

        return $this->render('simulazione', [
            'dataProvider' => $dataProvider,
        ]);
    }

}
