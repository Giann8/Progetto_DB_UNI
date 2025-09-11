CREATE TABLE manager (
    c_f VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100) NOT NULL
);

CREATE TABLE clienti (
    c_f VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100) NOT NULL
);

CREATE TABLE prodotti (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE negozi (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    responsabile VARCHAR(100) NOT NULL,
    orario_apertura TIME DEFAULT '09:00:00',
    orario_chiusura TIME DEFAULT '18:00:00'
);

CREATE TABLE fatture(
    id SERIAL PRIMARY KEY,
    cliente VARCHAR(100) NOT NULL,
    sconto DECIMAL(5, 2) DEFAULT 0.00 CHECK (sconto >= 0 AND sconto <= 30),
    data_emissione DATE DEFAULT CURRENT_DATE NOT NULL,
    totale DECIMAL(10, 2) DEFAULT 0.00 CHECK (totale >= 0),
    FOREIGN KEY (cliente) REFERENCES clienti(c_f) ON DELETE CASCADE
);

CREATE TABLE fornitori (
    p_iva VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    indirizzo TEXT NOT NULL
);

CREATE TABLE fidelity_card (
    id SERIAL PRIMARY KEY,
    cliente VARCHAR(100) REFERENCES clienti(c_f) ON DELETE CASCADE,
    punti INT DEFAULT 0 CHECK (punti >= 0),
    negozio INT REFERENCES negozi(id),
    data_rilascio DATE DEFAULT CURRENT_DATE,
    UNIQUE(cliente) 
);

CREATE TABLE prodotto_negozio (
    prodotto INT REFERENCES prodotti(id) ON DELETE CASCADE,
    negozio INT REFERENCES negozi(id) ON DELETE CASCADE,
    prezzo_unitario DECIMAL(10, 2) NOT NULL CHECK (prezzo_unitario >= 0),
    disponibilita INT NOT NULL DEFAULT 0 CHECK (disponibilita >= 0),
    PRIMARY KEY (prodotto, negozio)
);

CREATE TABLE prodotto_fornitore (
    prodotto INT REFERENCES prodotti(id) ON DELETE CASCADE,
    fornitore VARCHAR REFERENCES fornitori(p_iva) ON DELETE CASCADE,
    prezzo_unitario DECIMAL(10, 2) NOT NULL CHECK (prezzo_unitario >= 0),
    disponibilita INT DEFAULT 0 NOT NULL CHECK (disponibilita >= 0),
    PRIMARY KEY (prodotto, fornitore)
);

CREATE TABLE ordini(
    id SERIAL PRIMARY KEY,
    fornitore VARCHAR(100) ,
    manager_richiedente VARCHAR(100) ,
    negozio INT ,
    prodotto INT ,
    quantita INT NOT NULL CHECK (quantita > 0),
    prezzo INT NOT NULL CHECK (prezzo >= 0),
    data_consegna DATE NOT NULL DEFAULT CURRENT_DATE + INTERVAL '7 days',
    FOREIGN KEY (manager_richiedente) REFERENCES manager(c_f) ON DELETE CASCADE,
    FOREIGN KEY (negozio) REFERENCES negozi(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto) REFERENCES prodotti(id) ON DELETE CASCADE,
    FOREIGN KEY (fornitore) REFERENCES fornitori(p_iva) ON DELETE CASCADE
);

CREATE TABLE riga_fattura (
    fattura INT,
    prodotto INT,
    negozio INT,
    quantita INT NOT NULL CHECK (quantita > 0),
    subtotale DECIMAL(10, 2),
    FOREIGN KEY (fattura) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto,negozio) REFERENCES prodotto_negozio(prodotto,negozio) ON DELETE CASCADE,
    FOREIGN KEY (negozio, prodotto) REFERENCES prodotto_negozio(negozio, prodotto) ON DELETE CASCADE,
    PRIMARY KEY (fattura, prodotto,negozio)
);

CREATE TABLE storico_tessere(
    tessera_id_originale SERIAL PRIMARY KEY,
    negozio_id INT NOT NULL,
    negozio_nome VARCHAR(100) NOT NULL,
    cliente VARCHAR(100) REFERENCES clienti(c_f) ON DELETE CASCADE,
    punti INT DEFAULT 0 CHECK (punti >= 0),
    data_rilascio DATE NOT NULL,
    data_eliminazione_negozio DATE NOT NULL
);

-- Funzione per l'hashing MD5 delle password
CREATE OR REPLACE FUNCTION md5_hash_password() RETURNS TRIGGER AS $$ BEGIN NEW.password = md5(NEW.password);
RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger che applica l'hashing prima di inserire/aggiornare
CREATE TRIGGER hash_password BEFORE
INSERT
    OR
UPDATE OF password ON clienti FOR EACH ROW EXECUTE FUNCTION md5_hash_password();

-- Trigger per l'hashing delle password dei manager
CREATE TRIGGER hash_password_manager BEFORE
INSERT
    OR
UPDATE OF password ON manager FOR EACH ROW EXECUTE FUNCTION md5_hash_password();

-- Funzione per normalizzare l'input
CREATE OR REPLACE FUNCTION normalizzaInput() RETURNS TRIGGER AS $$
BEGIN
    NEW.email = LOWER(NEW.email);
    NEW.c_f = UPPER(NEW.c_f);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger per normalizzare l'input all'inserimento dei clienti
CREATE TRIGGER normalizeInput 
BEFORE INSERT OR UPDATE ON clienti
FOR EACH ROW EXECUTE FUNCTION normalizzaInput();

-- Trigger per normalizzare l'input all'inserimento dei manager
CREATE TRIGGER normalizeInput 
BEFORE INSERT OR UPDATE ON manager
FOR EACH ROW EXECUTE FUNCTION normalizzaInput();

-- Inserimento manager
INSERT INTO manager (c_f, name, email, password)
VALUES (
        'MGMT12345678',
        'Mario Manager',
        'mario.manager@example.com',
        'managerpass'
    );

-- Inserimento dati (le password verranno hashate automaticamente)
INSERT INTO clienti (c_f, name, email, password)
VALUES (
        'GRAZ12345678',
        'Graziano',
        'graziano@example.com',
        'mypassword'
    ),
    (
        'MARIA12345678',
        'Maria',
        'Maria@example.com',
        'password'
    );

INSERT INTO negozi (name, address, responsabile, orario_apertura, orario_chiusura)
VALUES (
        'Negozio 1',
        'Via Roma 1',
        'Mario Rossi',
        '08:30:00',
        '19:30:00'
    ),
    (
        'Negozio 2',
        'Via Milano 2',
        'Luigi Bianchi',
        '09:00:00',
        '20:00:00'
    );

-- Inserimento prodotti di uso quotidiano a prezzi accessibili
INSERT INTO prodotti (name, description)
VALUES 
    ('Quaderno A4', 'Quaderno a righe 80 pagine per scuola e ufficio'),
    ('Penna Bic Blu', 'Penna a sfera colore blu, scrittura fluida'),
    ('Matita HB', 'Matita in grafite durezza HB per disegno e scrittura'),
    ('Gomma da cancellare', 'Gomma bianca per matita, alta qualità'),
    ('Righello 30cm', 'Righello in plastica trasparente con misure precise'),
    ('Temperino', 'Temperino a due fori per matite normali e colorate'),
    ('Evidenziatore Giallo', 'Evidenziatore fluorescente giallo per studiare'),
    ('Colla Stick', 'Colla in stick 15g, ideale per carta e cartone'),
    ('Forbici', 'Forbici da ufficio con lame in acciaio inox'),
    ('Blocco Note', 'Blocco appunti 50 fogli con perforazione'),
    ('Graffette Metalliche', 'Confezione 100 graffette per documenti');

-- Inserimento fornitori di materiale per ufficio
INSERT INTO fornitori (P_IVA, name, indirizzo)
VALUES 
    ('IT11111111111', 'Cartoleria Centrale SRL', 'Via della Stazione 15, Milano'),
    ('IT22222222222', 'Office Supply SpA', 'Corso Europa 78, Roma'),
    ('IT33333333333', 'Distribuzione Ufficio SNC', 'Via Veneto 42, Napoli');

-- Inserimento prodotti nei negozi con prezzi bassi e buona disponibilità
INSERT INTO prodotto_negozio (prodotto, negozio, prezzo_unitario, disponibilita)
VALUES 
    -- Negozio 1 (Via Roma) - Cartoleria e materiale per ufficio
    (1, 1, 2.50, 100),   -- Quaderno A4
    (2, 1, 0.85, 200),   -- Penna Bic Blu
    (3, 1, 0.60, 150),   -- Matita HB
    (4, 1, 1.20, 80),    -- Gomma da cancellare
    (5, 1, 3.50, 50),    -- Righello 30cm
    (6, 1, 2.80, 60),    -- Temperino
    (7, 1, 1.90, 90),    -- Evidenziatore Giallo
    (8, 1, 4.20, 70),    -- Colla Stick
    
    -- Negozio 2 (Via Milano) - Prezzi leggermente diversi
    (1, 2, 2.80, 80),    -- Quaderno A4
    (2, 2, 0.90, 180),   -- Penna Bic Blu
    (3, 2, 0.65, 120),   -- Matita HB
    (9, 2, 8.50, 25),    -- Forbici
    (10, 2, 3.70, 40),   -- Blocco Note
    (11, 2, 2.10, 100);  -- Graffette Metalliche

-- Inserimento prodotti dai fornitori con prezzi all'ingrosso convenienti
INSERT INTO prodotto_fornitore (prodotto, fornitore, prezzo_unitario, disponibilita)
VALUES 
    -- Quaderno A4 (id=1) da diversi fornitori
    (1, 'IT11111111111', 1.80, 500),   -- Cartoleria Centrale - prezzo più basso
    (1, 'IT22222222222', 1.90, 300),   -- Office Supply
    (1, 'IT33333333333', 2.00, 400),   -- Distribuzione Ufficio
    
    -- Penna Bic Blu (id=2) da diversi fornitori
    (2, 'IT11111111111', 0.55, 1000),  -- Cartoleria Centrale
    (2, 'IT22222222222', 0.50, 1200),  -- Office Supply - prezzo più basso
    (2, 'IT33333333333', 0.60, 800),   -- Distribuzione Ufficio
    
    -- Matita HB (id=3) da diversi fornitori  
    (3, 'IT11111111111', 0.40, 800),   -- Cartoleria Centrale - prezzo più basso
    (3, 'IT22222222222', 0.45, 600),   -- Office Supply
    
    -- Gomma da cancellare (id=4) da diversi fornitori
    (4, 'IT11111111111', 0.80, 400),   -- Cartoleria Centrale
    (4, 'IT22222222222', 0.75, 500),   -- Office Supply - prezzo più basso
    (4, 'IT33333333333', 0.90, 300),   -- Distribuzione Ufficio
    
    -- Righello 30cm (id=5) da diversi fornitori
    (5, 'IT11111111111', 2.20, 200),   -- Cartoleria Centrale - prezzo più basso
    (5, 'IT22222222222', 2.50, 150),   -- Office Supply
    (5, 'IT33333333333', 2.80, 180);   -- Distribuzione Ufficio




-- Funzione per il trigger di validazione tessere fedeltà
CREATE OR REPLACE FUNCTION validate_fidelity_card() RETURNS TRIGGER AS $$
BEGIN
    -- Controlla se il cliente esiste
    PERFORM * FROM clienti WHERE c_f = NEW.cliente;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Cliente con codice fiscale % non trovato', NEW.cliente;
    END IF;
    
    -- Controlla se il negozio esiste
    PERFORM * FROM negozi WHERE id = NEW.negozio;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Negozio con ID % non trovato', NEW.negozio;
    END IF;

    IF NEW.punti < 0 THEN
        RAISE NOTICE 'I punti della tessera fedeltà non possono essere negativi';
        NEW.punti = 0;
    END IF;

    RAISE NOTICE 'Tessera fedeltà rilasciata al cliente % dal %', NEW.cliente, NEW.negozio;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger che esegue i controlli prima di inserire/aggiornare tessere fedeltà
CREATE TRIGGER validate_fidelity_card_trigger 
BEFORE INSERT OR UPDATE ON fidelity_card
FOR EACH ROW 
EXECUTE FUNCTION validate_fidelity_card();

-- VIEW: Catalogo completo prodotti per negozio
CREATE VIEW catalogo_negozi AS 
SELECT 
    n.id as negozio_id,
    n.name as negozio_nome,
    n.address as negozio_indirizzo,
    p.id as prodotto_id,
    p.name as prodotto_nome,
    p.description as prodotto_descrizione,
    pn.prezzo_unitario,
    pn.disponibilita,
    CASE 
        WHEN pn.disponibilita > 0 THEN 'Disponibile'
        ELSE 'Esaurito'
    END as stato_disponibilita
FROM negozi n
JOIN prodotto_negozio pn ON n.id = pn.negozio
JOIN prodotti p ON pn.prodotto = p.id
ORDER BY p.name,pn.prezzo_unitario ASC,pn.disponibilita DESC;



-- VIEW: Tessere con più di 300 punti
CREATE VIEW tessere_maggiori_punti AS
SELECT 
    fc.cliente as cliente_c_f,
    c.name as cliente_nome,
    fc.id as tessera_id,
    fc.punti as punti,
    fc.data_rilascio as data_rilascio
FROM fidelity_card fc JOIN clienti c on fc.cliente = c.C_F
WHERE fc.punti >= 300
ORDER BY punti DESC, cliente_c_f DESC;

-- Funzione per aggiungere manualmente punti al cliente (probabilmente eliminabile)
CREATE OR REPLACE FUNCTION aggiorna_punti_fidelity() RETURNS TRIGGER AS $$
BEGIN
    -- Verifica che il cliente abbia una tessera fedeltà
    PERFORM * from fidelity_card WHERE cliente = NEW.cliente;
    IF FOUND THEN
        UPDATE fidelity_card
        SET punti = punti + FLOOR(NEW.totale)
        WHERE cliente = NEW.cliente;
        RAISE NOTICE 'Punti aggiornati per il cliente %: aggiunti % punti', NEW.cliente, FLOOR(NEW.totale);
    ELSE
        RAISE NOTICE 'Cliente % non ha una tessera fedeltà, punti non assegnati', NEW.cliente;
    END IF;
    
    RETURN NEW;
END;

$$ LANGUAGE plpgsql;
 
-- Trigger per aggiornare punti tessera all'aggiunta della fatturazione
CREATE TRIGGER aggiorna_punti_fidelity_trigger
AFTER INSERT ON fatture
FOR EACH ROW
EXECUTE FUNCTION aggiorna_punti_fidelity();

-- Lista tessere rilasciate per un negozio specifico
CREATE OR REPLACE FUNCTION lista_tessere_rilasciate(negozio_id INT) RETURNS TABLE(tessera_id INT, cliente_c_f VARCHAR, punti INT, data_rilascio DATE) AS $$
BEGIN
    RETURN QUERY
    SELECT fc.id, fc.cliente, fc.punti, fc.data_rilascio
    FROM fidelity_card fc
    WHERE fc.negozio = negozio_id
    ORDER BY fc.data_rilascio DESC;
END;
$$ LANGUAGE plpgsql;


-- Ordina prodotto da fornitore
CREATE OR REPLACE FUNCTION convalida_ordine_prodotto() RETURNS TRIGGER AS $$
DECLARE
    fornitore_migliore VARCHAR;
    prezzo_migliore DECIMAL;
BEGIN
    -- Trova direttamente il fornitore con il prezzo più basso e disponibilità sufficiente
 select pf.fornitore, MIN(pf.prezzo_unitario) as prezzo 
 INTO fornitore_migliore, prezzo_migliore
 from prodotto_fornitore pf 
 where pf.prodotto=NEW.prodotto and pf.disponibilita >= NEW.quantita
 group by pf.fornitore
 ORDER BY prezzo ASC
 LIMIT 1;

    -- Verifica che sia stato trovato un fornitore
    IF fornitore_migliore IS NULL THEN
        RAISE EXCEPTION 'Nessun fornitore trovato per il prodotto con ID %', NEW.prodotto;
    END IF;
    
    -- Verifica che il manager esista
    PERFORM * FROM manager WHERE C_F = NEW.manager_richiedente;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Manager con codice fiscale % non trovato', NEW.manager_richiedente;
    END IF;
    
    -- Verifica che il negozio esista
    PERFORM * FROM negozi WHERE id = NEW.negozio;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Negozio con ID % non trovato', NEW.negozio;
    END IF;

    NEW.fornitore := fornitore_migliore;
    NEW.prezzo := prezzo_migliore * NEW.quantita;

    RAISE NOTICE 'Ordine % creato: % unità del prodotto % dal fornitore % al prezzo di €% per unità. Manager: %. Data di consegna: %',
                 NEW.id, NEW.quantita, NEW.prodotto, fornitore_migliore, prezzo_migliore, NEW.manager_richiedente, CURRENT_DATE + INTERVAL '7 days';
RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Trigger per convalidare l'ordine del prodotto
CREATE TRIGGER convalida_ordine_prodotto
BEFORE INSERT ON ordini
FOR EACH ROW
EXECUTE FUNCTION convalida_ordine_prodotto();

-- Funzione per aggiornare disponibilità prodotti dal fornitore
CREATE OR REPLACE FUNCTION aggiorna_disponibilita_prodotto_fornitore() RETURNS TRIGGER AS $$
BEGIN
    UPDATE prodotto_fornitore
    SET disponibilita = disponibilita - NEW.quantita
    WHERE prodotto = NEW.prodotto AND fornitore = NEW.fornitore;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Impossibile aggiornare la disponibilità: prodotto % o fornitore % non trovato', NEW.prodotto, NEW.fornitore;
    END IF;

    RAISE NOTICE 'Disponibilità aggiornata per il prodotto % dal fornitore %: -% unità', NEW.prodotto, NEW.fornitore, NEW.quantita;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger per aggiornare disponibilità prodotti da fornitore dopo ordine
CREATE TRIGGER aggiorna_disponibilita_prodotto_fornitore 
AFTER INSERT ON ordini
    FOR EACH ROW
    EXECUTE FUNCTION aggiorna_disponibilita_prodotto_fornitore();

-- View per gli sconti dei clienti
CREATE VIEW sconti_clienti AS (
    SELECT c.name, c.C_F as cliente, fc.punti,
           CASE
               WHEN fc.punti >= 300 THEN 30.00
               WHEN fc.punti >= 200 THEN 15.00
               WHEN fc.punti >= 100 THEN 5.00
               ELSE 0.00
           END AS sconto_percentuale,
              CASE
                WHEN fc.punti >= 300 THEN 300
                WHEN fc.punti >= 200 THEN 200
                WHEN fc.punti >= 100 THEN 100
                ELSE 0
              END
           AS punti_decurtati
    FROM fidelity_card fc JOIN clienti c on fc.cliente = c.C_F
);

-- Funzione per aggiungere prodotti al negozio quando vengono ordinati
CREATE OR REPLACE FUNCTION aggiungi_prodotto_negozio() RETURNS TRIGGER AS $$
BEGIN
    PERFORM * FROM prodotto_negozio WHERE prodotto = NEW.prodotto AND negozio = NEW.negozio;
    IF FOUND THEN
        UPDATE prodotto_negozio
        SET disponibilita = disponibilita + NEW.quantita
        WHERE prodotto = NEW.prodotto AND negozio = NEW.negozio;
    ELSE
        INSERT INTO prodotto_negozio (prodotto, negozio, prezzo_unitario, disponibilita)
        VALUES (NEW.prodotto, NEW.negozio, NEW.prezzo/NEW.quantita, NEW.quantita);
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger per aggiungere prodotti al negozio
CREATE TRIGGER aggiungi_prodotto_negozio_trigger
AFTER INSERT ON ordini
FOR EACH ROW
EXECUTE FUNCTION aggiungi_prodotto_negozio();

-- Funzione Trigger per aggiornare disponibilità prodotto nel negozio
CREATE OR REPLACE FUNCTION aggiorna_disponibilita_prodotto_negozio() RETURNS TRIGGER AS $$
BEGIN
    UPDATE prodotto_negozio
    SET disponibilita = disponibilita - NEW.quantita
    WHERE prodotto = NEW.prodotto AND negozio = NEW.negozio;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger per aggiornare disponibilità prodotto nel negozio
CREATE TRIGGER aggiorna_disponibilita_prodotto_negozio_trigger
AFTER INSERT ON riga_fattura
FOR EACH ROW
EXECUTE FUNCTION aggiorna_disponibilita_prodotto_negozio();

-- Funzione per calcolare il subtotale della riga fattura
CREATE OR REPLACE FUNCTION calcola_subtotale_riga_fattura() RETURNS trigger AS $$
DECLARE
    prezzo_unitario DECIMAL;
BEGIN
    SELECT pn.prezzo_unitario
    INTO prezzo_unitario
    FROM prodotto_negozio pn
    WHERE pn.prodotto = NEW.prodotto AND pn.negozio = NEW.negozio;

    NEW.subtotale := prezzo_unitario * NEW.quantita;

    RAISE NOTICE 'Riga fattura: prodotto %, negozio %, quantità %, subtotale €%', 
                 NEW.prodotto, NEW.negozio, NEW.quantita, NEW.subtotale;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger per calcolare il subtotale della riga fattura
CREATE TRIGGER calcola_subtotale_riga_fattura
BEFORE INSERT ON riga_fattura
FOR EACH ROW
EXECUTE FUNCTION calcola_subtotale_riga_fattura();

-- Funzione trigger per aggiungere tessere nello storico
CREATE OR REPLACE FUNCTION aggiorna_tessere_negozi_eliminati() RETURNS TRIGGER AS $$
DECLARE
    tessera_record RECORD;
BEGIN
    -- Itera su tutte le tessere del negozio eliminato
    FOR tessera_record IN 
        SELECT id, cliente, punti, data_rilascio 
        FROM fidelity_card 
        WHERE negozio = OLD.id
    LOOP
        INSERT INTO storico_tessere (tessera_id_originale, negozio_id, negozio_nome, cliente, punti, data_rilascio,data_eliminazione_negozio)
        VALUES (tessera_record.id, OLD.id, OLD.name, tessera_record.cliente, tessera_record.punti, tessera_record.data_rilascio, CURRENT_DATE);
        DELETE FROM fidelity_card WHERE id = tessera_record.id;
        RAISE NOTICE 'Tessera % del cliente % spostata nello storico', tessera_record.id, tessera_record.cliente;
    END LOOP;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

-- Trigger che inserisce le tessere rilasciate dal negozio eliminato nella tabella storico_tessere
CREATE TRIGGER aggiorna_tessere_negozi_eliminati_trigger
BEFORE DELETE ON negozi
FOR EACH ROW
EXECUTE FUNCTION aggiorna_tessere_negozi_eliminati();

-- Funzione per rilasciare una fattura
CREATE or REPLACE function valida_fattura() RETURNS TRIGGER AS $$
DECLARE
    punti_decurtati INT;
    sconto_percentuale DECIMAL(5,2) DEFAULT 0;
    sconto_euro DECIMAL(10,2) DEFAULT 0;
    totale_effettivo DECIMAL(10, 2);
BEGIN
    SELECT sc.punti_decurtati, sc.sconto_percentuale
    FROM sconti_clienti sc
    WHERE sc.cliente = NEW.cliente 
    INTO punti_decurtati, sconto_percentuale;
    IF FOUND AND NEW.sconto <= sconto_percentuale THEN

        sconto_euro := NEW.totale * (sconto_percentuale / 100);
        

        IF sconto_euro > 100 THEN 
            sconto_euro := 100; 
        END IF;
        

        UPDATE fidelity_card 
        SET punti = punti - punti_decurtati 
        WHERE cliente = NEW.cliente;
        
        RAISE NOTICE 'Sconto applicato per il cliente %: €%', NEW.cliente, sconto_euro;
    ELSE
        sconto_percentuale=0;
        RAISE NOTICE 'Sconto non applicato per il cliente %', NEW.cliente;
    END IF;
    

    totale_effettivo := NEW.totale - sconto_euro;


    NEW.totale := totale_effettivo;
    NEW.sconto := sconto_percentuale;

    RAISE NOTICE 'Fattura rilasciata per il cliente %: totale originale €%, totale finale €%,sconto percentuale %%%', 
                 NEW.cliente, (NEW.totale + sconto_euro), NEW.totale,NEW.sconto;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER rilascia_fattura_trigger
BEFORE INSERT ON fatture
FOR EACH ROW
EXECUTE FUNCTION valida_fattura();

-- VIEW che permette di visualizzare lo storico degli ordini in modo approfondito
CREATE VIEW storicoOrdineApprofondito AS
SELECT o.id, o.fornitore AS id_fornitore, f.name AS nome_fornitore, o.manager_richiedente, o.negozio as id_negozio, n.name AS nome_negozio, o.prodotto as id_prodotto, p.name AS nome_prodotto, o.quantita, o.prezzo, o.data_consegna
FROM ordini o
JOIN fornitori f ON o.fornitore = f.p_iva
JOIN negozi n ON o.negozio = n.id
JOIN prodotti p ON o.prodotto = p.id
ORDER BY o.data_consegna DESC; 

-- Funzione per ottenere lo storico ordini di un fornitore specifico
CREATE OR REPLACE FUNCTION storicoOrdiniFornitore(p_iva VARCHAR)
RETURNS TABLE(id INT, id_fornitore VARCHAR,nome_fornitore VARCHAR, manager_richiedente VARCHAR, id_negozio INT,nome_negozio VARCHAR, id_prodotto int,nome_prodotto VARCHAR,quantita int,prezzo int, data_consegna DATE) AS $$
BEGIN
IF p_iva IS NOT NULL THEN
    RETURN QUERY
    SELECT *
    FROM storicoOrdineApprofondito soa
    WHERE soa.id_fornitore = p_iva
    ORDER BY soa.data_consegna DESC;
    ELSE 
    RAISE WARNING 'Partita IVA non valida';
END IF;
END;
$$ LANGUAGE plpgsql;
