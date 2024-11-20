SELECT a.codice_fiscale, i.attivo, i.data_decesso
FROM istanza i JOIN anagrafica a ON i.id_anagrafica_disabile = a.id
WHERE (i.attivo = true AND i.chiuso = false) OR (i.data_decesso IS NOT NULL AND i.data_decesso >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH));