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

update istanza set attivo = false where data_decesso is not null