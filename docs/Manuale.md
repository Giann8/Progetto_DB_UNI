
Le seguenti istruzioni spiegheranno i principali software da usare e i comandi per poter avviare facilmente l'applicazione
## Requisiti
Il progetto è stato realizzato con l'utilizzo di docker e docker-compose (si consiglia il secondo), non è necessario ma è consigliato installare postgres nel proprio terminale per potervi accedere da remoto connettendosi alla porta utilizzata.

Per la configurazioni è stato creato un file *.env.example* che mostrerà le variabili di ambiente da configurare.

## Credenziali principali e funzionalità
### Manager
```
	EMAIL:= mario.manager@example.com
	password:= managerpass
```
Il manager può gestire completamente i negozi, i clienti, i fornitori e i prodotti scegliendo cosa eliminare, modificare o aggiungere
### Clienti
```
	EMAIL:= Graziano@example.com
	password:=mypassword
```
Il cliente può fare acquisti da negozi differenti, richiedere la propria tessera fidelity ad un negozio e vedere lo storico degli acquisti effettuati
## Istruzioni

1.  Per prima cosa bisogna rinominare il file `.env.example`:
	```bash
	mv .env.example .env
	```
	Successivamente bisogna cambiare i valori delle variabili in esso contenute a nostro piacere, la modifica della porta del db potrebbe portare ad un errore in caso questa fosse già utilizzata(spesso perché è già attivo un altro db postgre)  in caso questo problema si verificasse seguire i seguenti passaggi (fare kill solo se la porta non è usata da un db che si ritiene importante):
	```bash
	sudo lsof -i :nome_porta
	sudo kill pid_number
	```

2. Successivamente possiamo avviare il progetto, dopo esserci assicurati di essere nella stessa cartella del ```docker-compose.yml``` possiamo eseguire il seguente comando:
	```bash
	docker-compose up --build
	```
	facendo così avvieremo docker mostrando i logs di entrambi i container, in caso volessimo avviarlo senza basterà usare ```docker-compose up -d --build```

3. Ora possiamo accedere alla pagina al seguente link https://localhost:3000, verrete reindirizzati subito alla pagina di login del progetto a cui vi basterà accedere.

4. Nel momento in cui vorrete spegnere il docker potrete usare 
	```bash 
	docker-compose down
	oppure
	docker-compose down -v 
	```
	aggiungendo -v eliminerete i volumi all'interno del container, per il riavvio è consigliato comunque fare docker-compose up --build.
