# Progetto_DB_UNI

## Funzionalità
Queste funzionalità devono essere implementate all'interno del db con viste,trigger e funzioni/procedure.

- Aggiornamento saldo punti su tessera fedelt`a. Per ogni Euro speso, viene accumu- lato un punto sulla tessera del cliente che effettua la spesa. Il saldo punti su ogni tessera deve essere continuamente aggiornato.

- Applicazione sconto sulla spesa. Al raggiungimento di determinate soglie di punti, vengono sbloccati alcuni sconti. In particolare: a 100 punti si sblocca uno sconto del 5%, a 200 punti del 15%, a 300 punti del 30%. Si noti che lo sconto non può mai essere più elevato di 100 Euro.
L’applicazione dello sconto avviene su scelta del cliente, e lo sconto viene applicato sul totale della fattura sulla quale viene applicato. In seguito all’applicazione dello sconto, il saldo punti della tessera fedelt`a deve essere decurtato del numero di punti usato per lo sconto.

- Mantenimento storico tessere. Quando un negozio viene eliminato, `e necessario man- tenere in una tabella di storico le informazioni sulle tessere che erano state emesse dal negozio stesso, con la data di emissione.

- Aggiornamento disponibilità prodotti dai fornitori. La disponibilità dei prodotti dai vari fornitori è ovviamente limitata (e comunicata da ciascun fornitore alla catena di negozi). In seguito ad un ordine di un certo prodotto presso un certo fornitore, è necessario mantenere aggiornata la disponibilit`a di quel prodotto da quel fornitore.

- Ordine prodotti da fornitore. Quando un prodotto deve essere rifornito di una certa quantità, è necessario inserire un ordine presso un determinato fornitore. Il fornitore deve essere automaticamente scelto sulla base del criterio di economicità (vale a dire, l’ordine viene automaticamente effettuato presso il fornitore che, oltre ad avere disponibili`a di prodotto sufficiente, offre il costo minore).

- Lista tesserati. Dato un negozio, è necessario conoscere la lista dei clienti ai quali il negozio ha emesso la tessera fedeltà.

- Storico ordini a fornitori. Dato un fornitore, è necessario conoscere tutti gli ordini che sono stati effettuati presso di lui.

- Saldi punti. è necessario mantenere un elenco aggiornato dei clienti che hanno una tessera fedelt`a con un saldo punti superiore a 300 punti.
