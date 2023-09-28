/*utenti che non hanno movimenti*/
select DISTINCT a.cognome_nome, i.id, i.id_distretto from movimento m, conto c,anagrafica a,istanza i
where m.id_conto = c.id AND
        a.id = i.id_anagrafica_disabile AND
        c.id = m.id_conto
  AND i.attivo = true AND i.chiuso = false AND i.id not in
     (select i2.id from movimento m2, istanza i2, conto c2
     where m2.id_conto = c2.id AND c2.id_istanza = i2.id AND m2.is_movimento_bancario = true)

/*utenti deceduti ancora attivi*/
select * from istanza i, anagrafica a where i.id_anagrafica_disabile = a.id AND
        attivo = true AND i.data_decesso is not null

/*update istanza set attivo = false where data_decesso is not null*/

/*isee non presenti*/
select i.id, a.cognome_nome from istanza i, anagrafica a where i.id_anagrafica_disabile = a.id AND i.attivo = true AND
        ((YEAR(CURDATE()) - YEAR(a.data_nascita)) - (RIGHT(CURDATE(), 5) < RIGHT(a.data_nascita, 5))) > 18 AND i.id not in (
        select distinct i.id from istanza i, isee where i.id = isee.id_istanza AND isee.valido = 1)

recuperi inseriti
   select i.id, a.cognome_nome from istanza i, anagrafica a where i.id_anagrafica_disabile = a.id AND i.attivo = true AND
         i.id in (
        select distinct r.id_istanza from recupero r where r.chiuso= false)
ORDER BY `a`.`cognome_nome` ASC

UTENTI NON PAGATI IL MESE SCORSO
   select DISTINCT a.cognome_nome, i.id, i.id_distretto from movimento m, conto c,anagrafica a,istanza i
where m.id_conto = c.id AND
        a.id = i.id_anagrafica_disabile AND
        c.id = m.id_conto
  AND i.attivo = true AND i.chiuso = false AND i.id not in
                                               (select i2.id from movimento m2, istanza i2, conto c2
                                                where m2.id_conto = c2.id AND c2.id_istanza = i2.id AND m2.is_movimento_bancario = true AND m2.data = '2023-08-31')

   UTENTI ATTIVI NON PAGAATI NEL MESE CORRENTE
   SELECT id from istanza i2 where i2.attivo = true AND i2.data_decesso IS NULL AND i2.id NOT IN (SELECT i.id FROM movimento m, istanza i, conto c where c.id = m.id_conto AND c.id_istanza = i.id AND m.data = "2023-09-30" AND i.data_decesso IS NULL AND i.attivo = true AND m.is_movimento_bancario = true);
UTENTI DECEDUTI PAGATI PER ERRORE
SELECT id from istanza i2 where i2.data_decesso IS NOT NULL AND i2.id IN (SELECT i.id FROM movimento m, istanza i, conto c where c.id = m.id_conto AND c.id_istanza = i.id AND m.data = "2023-09-30" AND m.is_movimento_bancario = true);

