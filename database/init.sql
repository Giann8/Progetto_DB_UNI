CREATE TABLE manager (
    C_F VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100) NOT NULL
);

CREATE TABLE clienti (
    C_F VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100) NOT NULL
);

CREATE TABLE prodotti (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE negozi (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    responsabile VARCHAR(100),
    orario_apertura TIME DEFAULT '09:00:00',
    orario_chiusura TIME DEFAULT '18:00:00'
);

CREATE TABLE fatture(
    id SERIAL PRIMARY KEY,
    cliente VARCHAR(100) NOT NULL,
    sconto DECIMAL(5, 2) DEFAULT 0.00,
    data_emissione DATE NOT NULL,
    totale DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (cliente) REFERENCES clienti(C_F) ON DELETE SET NULL
);

CREATE TABLE fornitori (
    P_IVA VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    indirizzo TEXT
);

CREATE TABLE fidelity_card (
    id SERIAL PRIMARY KEY,
    cliente VARCHAR(100) REFERENCES clienti(C_F) ON DELETE CASCADE,
    punti INT DEFAULT 0,
    negozio INT REFERENCES negozi(id),
    data_rilascio DATE DEFAULT CURRENT_DATE,
    UNIQUE(cliente) 
);

CREATE TABLE prodotto_negozio (
    prodotto INT REFERENCES prodotti(id) ON DELETE CASCADE,
    negozio INT REFERENCES negozi(id) ON DELETE CASCADE,
    prezzo_unitario DECIMAL(10, 2) NOT NULL,
    disponibilita INT DEFAULT 0,
    PRIMARY KEY (prodotto, negozio)
);

CREATE TABLE prodotto_fornitore (
    prodotto INT REFERENCES prodotti(id) ON DELETE CASCADE,
    fornitore VARCHAR REFERENCES fornitori(P_IVA) ON DELETE CASCADE,
    prezzo_unitario DECIMAL(10, 2) NOT NULL,
    disponibilita INT DEFAULT 0,
    PRIMARY KEY (prodotto, fornitore)
);

CREATE TABLE ordini(
    id SERIAL PRIMARY KEY,
    fornitore VARCHAR(100) NOT NULL,
    manager_richiedente VARCHAR(100) NOT NULL,
    negozio INT NOT NULL,
    prodotto INT NOT NULL,
    quantita INT NOT NULL,
    prezzo INT NOT NULL,
    data_consegna DATE NOT NULL DEFAULT CURRENT_DATE + INTERVAL '7 days',
    FOREIGN KEY (manager_richiedente) REFERENCES manager(C_F) ON DELETE SET NULL,
    FOREIGN KEY (negozio) REFERENCES negozi(id) ON DELETE SET NULL,
    FOREIGN KEY (prodotto) REFERENCES prodotti(id),
    FOREIGN KEY (fornitore) REFERENCES fornitori(P_IVA)
);

CREATE TABLE riga_fattura (
    fattura INT,
    prodotto INT,
    quantita INT NOT NULL CHECK (quantita > 0),
    subtotale DECIMAL(10, 2),
    FOREIGN KEY (fattura) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto) REFERENCES prodotti(id),
    PRIMARY KEY (fattura, prodotto)
);

CREATE TABLE storico_tessere(
    tessera_id_originale SERIAL PRIMARY KEY,
    cliente VARCHAR(100) REFERENCES clienti(C_F) ON DELETE CASCADE,
    punti INT DEFAULT 0,
    data_rilascio DATE NOT NULL
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

-- Inserimento manager
INSERT INTO manager (c_f, name, email, password)
VALUES (
        'MGMT12345678',
        'Mario Manager',
        'mario.manager@example.com',
        'managerpass'
    ),
    (
        'MGMT87654321', 
        'Luigi Supervisor',
        'luigi.supervisor@example.com',
        'supervisorpass'
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

-- Inserimento prodotti
INSERT INTO prodotti (name, description)
VALUES 
    ('iPhone 15', 'Smartphone Apple con chip A17 Pro, fotocamera avanzata e design premium'),
    ('Samsung Galaxy S24', 'Smartphone Android con display AMOLED e AI integrata'),
    ('MacBook Air M3', 'Laptop ultraleggero Apple con chip M3 e batteria a lunga durata'),
    ('Dell XPS 13', 'Laptop Windows compatto con display InfinityEdge'),
    ('AirPods Pro', 'Auricolari wireless con cancellazione attiva del rumore'),
    ('Sony WH-1000XM5', 'Cuffie over-ear con cancellazione del rumore premium'),
    ('iPad Air', 'Tablet Apple con chip M2 e supporto Apple Pencil'),
    ('Nintendo Switch', 'Console gaming ibrida portatile/fissa'),
    ('PlayStation 5', 'Console gaming di nuova generazione Sony'),
    ('Kindle Paperwhite', 'E-reader con display ad alta risoluzione e luce regolabile'),
    ('Apple Watch Series 9', 'Smartwatch con monitoraggio salute avanzato'),
    ('Canon EOS R8', 'Fotocamera mirrorless full-frame per fotografia professionale'),
    ('Dyson V15', 'Aspirapolvere cordless con tecnologia laser'),
    ('Bose SoundLink', 'Speaker Bluetooth portatile con audio premium'),
    ('GoPro Hero 12', 'Action camera 4K impermeabile per sport estremi');

-- Inserimento fornitori
INSERT INTO fornitori (P_IVA, name, indirizzo)
VALUES 
    ('IT12345678901', 'TechSupply SRL', 'Via Milano 123, Milano'),
    ('IT98765432109', 'ElectroWholesale SpA', 'Corso Roma 456, Roma'),
    ('IT55555555555', 'DigitalWorld SNC', 'Viale Torino 789, Torino');

-- Inserimento prodotti nei negozi con prezzi e disponibilità
INSERT INTO prodotto_negozio (prodotto, negozio, prezzo_unitario, disponibilita)
VALUES 
    -- Negozio 1 (Via Roma)
    (1, 1, 999.99, 15),   -- iPhone 15
    (2, 1, 899.99, 8),    -- Samsung Galaxy S24
    (3, 1, 1299.99, 5),   -- MacBook Air M3
    (5, 1, 279.99, 25),   -- AirPods Pro
    (7, 1, 649.99, 10),   -- iPad Air
    (8, 1, 329.99, 12),   -- Nintendo Switch
    (10, 1, 149.99, 20),  -- Kindle Paperwhite
    (11, 1, 429.99, 7),   -- Apple Watch Series 9
    
    -- Negozio 2 (Via Milano)
    (1, 2, 989.99, 10),   -- iPhone 15 (prezzo leggermente diverso)
    (2, 2, 879.99, 15),   -- Samsung Galaxy S24
    (4, 2, 1199.99, 6),   -- Dell XPS 13
    (6, 2, 399.99, 8),    -- Sony WH-1000XM5
    (9, 2, 549.99, 3),    -- PlayStation 5
    (12, 2, 1899.99, 2),  -- Canon EOS R8
    (13, 2, 649.99, 4),   -- Dyson V15
    (14, 2, 199.99, 12),  -- Bose SoundLink
    (15, 2, 449.99, 6);   -- GoPro Hero 12

-- Inserimento prodotti dai fornitori con prezzi e disponibilità
INSERT INTO prodotto_fornitore (prodotto, fornitore, prezzo_unitario, disponibilita)
VALUES 
    -- iPhone 15 (id=1) da diversi fornitori con prezzi diversi
    (1, 'IT12345678901', 850.00, 50),   -- TechSupply - prezzo più basso
    (1, 'IT98765432109', 870.00, 30),   -- ElectroWholesale
    (1, 'IT55555555555', 890.00, 20),   -- DigitalWorld
    
    -- Samsung Galaxy S24 (id=2) da diversi fornitori
    (2, 'IT12345678901', 780.00, 40),   -- TechSupply
    (2, 'IT98765432109', 750.00, 60),   -- ElectroWholesale - prezzo più basso
    (2, 'IT55555555555', 800.00, 25),   -- DigitalWorld
    
    -- MacBook Air M3 (id=3) da diversi fornitori  
    (3, 'IT12345678901', 1150.00, 15),  -- TechSupply - prezzo più basso
    (3, 'IT98765432109', 1200.00, 10),  -- ElectroWholesale
    
    -- AirPods Pro (id=5) da diversi fornitori
    (5, 'IT12345678901', 230.00, 100),  -- TechSupply
    (5, 'IT98765432109', 220.00, 80),   -- ElectroWholesale - prezzo più basso
    (5, 'IT55555555555', 240.00, 60),   -- DigitalWorld
    
    -- PlayStation 5 (id=9) da diversi fornitori
    (9, 'IT12345678901', 480.00, 20),   -- TechSupply - prezzo più basso
    (9, 'IT98765432109', 500.00, 15),   -- ElectroWholesale
    (9, 'IT55555555555', 520.00, 10);   -- DigitalWorld



CREATE OR REPLACE FUNCTION rilascia_tessera_fidelity(cliente_cf VARCHAR, negozio_id INT) RETURNS VOID AS $$
DECLARE
    nome_negozio VARCHAR;
BEGIN
    INSERT INTO fidelity_card (cliente, negozio, punti, data_rilascio)
    VALUES (cliente_cf, negozio_id, 0, CURRENT_DATE);
    
    SELECT name INTO nome_negozio FROM negozi WHERE id = negozio_id;
    RAISE NOTICE 'Tessera fedeltà rilasciata al cliente % dal %', cliente_cf, nome_negozio;
END;
$$ LANGUAGE plpgsql;

-- Funzione per il trigger di validazione tessere fedeltà
CREATE OR REPLACE FUNCTION validate_fidelity_card() RETURNS TRIGGER AS $$
BEGIN
    -- Controlla se il cliente esiste
    IF NOT EXISTS (SELECT 1 FROM clienti WHERE c_f = NEW.cliente) THEN
        RAISE EXCEPTION 'Cliente con codice fiscale % non trovato', NEW.cliente;
    END IF;
    
    -- Controlla se il negozio esiste
    IF NOT EXISTS (SELECT 1 FROM negozi WHERE id = NEW.negozio) THEN
        RAISE EXCEPTION 'Negozio con ID % non trovato', NEW.negozio;
    END IF;
    
    -- Messaggio di duplicato anche se Unique già presente come constraint
    IF TG_OP = 'INSERT' AND EXISTS (SELECT 1 FROM fidelity_card WHERE cliente = NEW.cliente) THEN
        RAISE EXCEPTION 'Il cliente % ha già una tessera fedeltà', NEW.cliente;
    END IF;
    

    IF NEW.punti < 0 THEN
        RAISE EXCEPTION 'I punti della tessera fedeltà non possono essere negativi';
    END IF;
    
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
ORDER BY n.name, p.name;

-- VIEW: Solo prodotti disponibili
CREATE VIEW prodotti_disponibili AS 
SELECT 
    n.name as negozio,
    p.name as prodotto,
    pn.prezzo_unitario,
    pn.disponibilita
FROM negozi n
JOIN prodotto_negozio pn ON n.id = pn.negozio
JOIN prodotti p ON pn.prodotto = p.id
WHERE pn.disponibilita > 0
ORDER BY n.name, pn.prezzo_unitario;

-- VIEW: Tessere con più di 300 punti
CREATE VIEW tessere_maggiori_punti AS
SELECT 
    fc.cliente as cliente_cf,
    c.name as cliente_nome,
    fc.id as tessera_id,
    fc.punti as saldo_punti
FROM fidelity_card fc JOIN clienti c on fc.cliente = c.C_F
WHERE fc.punti >= 300
ORDER BY saldo_punti DESC, cliente_cf DESC;

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


-- Ordina da fornitore per costo inferiore
CREATE OR REPLACE FUNCTION fornitore_costo_inferiore(prodotto_id INT, quantita INT) 
RETURNS TABLE(fornitore VARCHAR, prezzo_unitario DECIMAL) AS $$
BEGIN
    RETURN QUERY
    SELECT pf.fornitore, pf.prezzo_unitario
    FROM prodotto_fornitore pf
    WHERE pf.prodotto = prodotto_id 
      AND pf.disponibilita >= quantita
      AND pf.prezzo_unitario = (
          SELECT MIN(pf2.prezzo_unitario)
          FROM prodotto_fornitore pf2
          WHERE pf2.prodotto = prodotto_id AND pf2.disponibilita >= quantita
      );
END;
$$ LANGUAGE plpgsql;

-- Ordina prodotto da fornitore
CREATE OR REPLACE FUNCTION ordina_prodotto(prodotto_id INT, quantita INT, manager_CF VARCHAR, negozio INT) RETURNS VOID AS $$
DECLARE
    fornitore_migliore VARCHAR;
    prezzo_migliore DECIMAL;
    ordine_id INT;
BEGIN
    -- Trova il fornitore con il prezzo più basso usando la funzione fornitore_costo_inferiore
    SELECT fornitore, prezzo_unitario 
    INTO fornitore_migliore, prezzo_migliore
    FROM fornitore_costo_inferiore(prodotto_id, quantita)
    LIMIT 1;
    
    -- Verifica che sia stato trovato un fornitore
    IF fornitore_migliore IS NULL THEN
        RAISE EXCEPTION 'Nessun fornitore trovato per il prodotto con ID %', prodotto_id;
    END IF;
    
    -- Verifica che il manager esista
    PERFORM * FROM manager WHERE C_F = manager_CF;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Manager con codice fiscale % non trovato', manager_CF;
    END IF;
    
    -- Verifica che il negozio esista
    PERFORM * FROM negozi WHERE id = negozio;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Negozio con ID % non trovato', negozio;
    END IF;

    -- Crea l'ordine nella tabella ordini
    INSERT INTO ordini (fornitore, prodotto, manager_richiedente, quantita, prezzo, data_consegna, negozio)
    VALUES (fornitore_migliore, prodotto_id, manager_CF, quantita, prezzo_migliore * quantita, CURRENT_DATE + INTERVAL '7 days', negozio)
    RETURNING id INTO ordine_id;
    
    RAISE NOTICE 'Ordine % creato: % unità del prodotto % dal fornitore % al prezzo di €% per unità. Manager: %. Data di consegna: %', 
                 ordine_id, quantita, prodotto_id, fornitore_migliore, prezzo_migliore, manager_CF, CURRENT_DATE + INTERVAL '7 days';
END;
$$ LANGUAGE plpgsql;

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
CREATE TRIGGER aggiorna_disponibilita_prodotto_fornitore AFTER INSERT ON ordini
    FOR EACH ROW
    EXECUTE FUNCTION aggiorna_disponibilita_prodotto_fornitore();

-- View per gli sconti dei clienti
CREATE VIEW sconti_clienti AS (
    SELECT c.name, c.C_F, fc.punti,
           CASE
               WHEN fc.punti >= 300 THEN 30.00
               WHEN fc.punti >= 200 THEN 15.00
               WHEN fc.punti >= 100 THEN 5.00
               ELSE 0.00
           END AS sconto_percentuale
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

-- Funzione trigger per aggiungere tessere nello storico
CREATE OR REPLACE FUNCTION aggiorna_tessere_negozi_eliminati() RETURNS TRIGGER AS $$
BEGIN  
    INSERT INTO storico_tessere (tessera_id_originale, cliente, punti, data_rilascio)
    VALUES (OLD.id, OLD.cliente, OLD.punti, OLD.data_rilascio);
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

-- Trigger che inserisce le tessere rilasciate dal negozio eliminato nella tabella storico_tessere
CREATE TRIGGER aggiorna_tessere_negozi_eliminati_trigger
AFTER DELETE ON negozi
FOR EACH ROW
EXECUTE FUNCTION aggiorna_tessere_negozi_eliminati();

-- Funzione per rilasciare una fattura
CREATE or REPLACE function rilascia_fattura(cliente_c_f VARCHAR, totale NUMERIC, scontato BOOLEAN) RETURNS VOID AS $$
DECLARE
    punti_decurtati INT;
    sconto_percentuale NUMERIC DEFAULT 0;
    sconto_euro DECIMAL(10,2);
    totale_effettivo DECIMAL(10, 2);
    nuova_fattura_id INT;
BEGIN
    IF scontato THEN
        SELECT sc.punti,sc.sconto_percentuale INTO punti_decurtati,sconto_percentuale
        FROM sconti_clienti sc
        WHERE sc.C_F = cliente_c_f;
        IF FOUND AND sconto_percentuale > 0 THEN
            UPDATE fidelity_card SET punti = punti - punti_decurtati WHERE cliente = cliente_c_f;
        ELSE
            RAISE NOTICE 'Cliente % non ha punti sufficienti per uno sconto o non ha una tessera fidelity. Nessuno sconto verrà applicato.', cliente_c_f;
        END IF;
    END IF;
    
        sconto_euro := (totale * sconto_percentuale / 100);
        IF sconto_euro > 100 THEN sconto_euro := 100;
        totale_effettivo := totale - sconto_euro ;
    END IF;

    INSERT INTO fatture (cliente, totale,sconto,data_emissione)
    VALUES (cliente_c_f, totale_effettivo, sconto_percentuale, CURRENT_DATE)
    RETURNING id INTO nuova_fattura_id;

    RAISE NOTICE 'Fattura % rilasciata per il cliente %: totale €% scontato a €%', nuova_fattura_id, cliente_c_f, totale, totale_effettivo;
END;
$$ LANGUAGE plpgsql;
