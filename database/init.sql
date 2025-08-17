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
    cliente_c_f VARCHAR(100),
    sconto DECIMAL(5, 2) DEFAULT 0.00,
    data DATE NOT NULL,
    totale DECIMAL(10, 2),
    FOREIGN KEY (cliente_c_f) REFERENCES clienti(C_F) ON DELETE SET NULL
);

CREATE TABLE fornitori (
    P_IVA VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    indirizzo TEXT
);

CREATE TABLE fidelity_card (
    id SERIAL PRIMARY KEY,
    cliente_c_f VARCHAR(100) REFERENCES clienti(C_F) ON DELETE CASCADE,
    punti INT DEFAULT 0,
    negozio_id INT REFERENCES negozi(id),
    data_rilascio DATE DEFAULT CURRENT_DATE,
    UNIQUE(cliente_c_f) 
);

CREATE TABLE prodotto_negozio (
    prodotto_id INT REFERENCES prodotti(id) ON DELETE CASCADE,
    negozio_id INT REFERENCES negozi(id) ON DELETE CASCADE,
    prezzo DECIMAL(10, 2) NOT NULL,
    disponibilita INT DEFAULT 0,
    PRIMARY KEY (prodotto_id, negozio_id)
);

CREATE TABLE ordini(
    id SERIAL PRIMARY KEY,
    fornitore VARCHAR(100),
    data_consegna DATE NOT NULL DEFAULT CURRENT_DATE + INTERVAL '7 days',
    manager_richiedente VARCHAR(100),
    stato VARCHAR(20) DEFAULT 'In attesa',
    totale DECIMAL(10, 2),
    FOREIGN KEY (fornitore) REFERENCES fornitori(P_IVA) ON DELETE RESTRICT,
    FOREIGN KEY (manager_richiedente) REFERENCES manager(C_F) ON DELETE SET NULL
);

CREATE TABLE riga_ordine (
    id_lista SERIAL PRIMARY KEY,
    ordine INT,
    prodotto INT,
    quantita INT NOT NULL CHECK (quantita > 0),
    prezzo_unitario DECIMAL(10, 2) NOT NULL,
    subtotale DECIMAL(10, 2) GENERATED ALWAYS AS (quantita * prezzo_unitario) STORED,
    FOREIGN KEY (ordine) REFERENCES ordini_fornitore(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto) REFERENCES prodotti(id) ON DELETE RESTRICT
);

CREATE TABLE riga_fattura (
    id_lista SERIAL PRIMARY KEY,
    fattura INT,
    prodotto INT,
    quantita INT NOT NULL CHECK (quantita > 0),
    prezzo_unitario DECIMAL(10, 2) NOT NULL,
    subtotale DECIMAL(10, 2) GENERATED ALWAYS AS (quantita * prezzo_unitario) STORED,
    FOREIGN KEY (fattura) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto) REFERENCES prodotti(id) ON DELETE RESTRICT
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
        'Admin',
        'admin@example.com',
        'adminpassword'
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

-- Inserimento prodotti nei negozi con prezzi e disponibilità
INSERT INTO prodotto_negozio (prodotto_id, negozio_id, prezzo, disponibilita)
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



CREATE OR REPLACE FUNCTION rilascia_tessera_fidelity(cliente_cf VARCHAR, negozio_id INT) RETURNS VOID AS $$
DECLARE
    nome_negozio VARCHAR;
BEGIN
    -- Inserimento semplice - i controlli sono gestiti dal trigger
    INSERT INTO fidelity_card (cliente_c_f, negozio_id, punti, data_rilascio)
    VALUES (cliente_cf, negozio_id, 0, CURRENT_DATE);
    
    SELECT name INTO nome_negozio FROM negozi WHERE id = negozio_id;
    RAISE NOTICE 'Tessera fedeltà rilasciata al cliente % dal %', cliente_cf, nome_negozio;
END;
$$ LANGUAGE plpgsql;

-- Funzione per il trigger di validazione tessere fedeltà
CREATE OR REPLACE FUNCTION validate_fidelity_card() RETURNS TRIGGER AS $$
BEGIN
    -- Controlla se il cliente esiste
    IF NOT EXISTS (SELECT 1 FROM clienti WHERE c_f = NEW.cliente_c_f) THEN
        RAISE EXCEPTION 'Cliente con codice fiscale % non trovato', NEW.cliente_c_f;
    END IF;
    
    -- Controlla se il negozio esiste
    IF NOT EXISTS (SELECT 1 FROM negozi WHERE id = NEW.negozio_id) THEN
        RAISE EXCEPTION 'Negozio con ID % non trovato', NEW.negozio_id;
    END IF;
    
    -- Il constraint UNIQUE(cliente_c_f) si occuperà automaticamente del controllo duplicati
    -- ma possiamo dare un messaggio più chiaro
    IF TG_OP = 'INSERT' AND EXISTS (SELECT 1 FROM fidelity_card WHERE cliente_c_f = NEW.cliente_c_f) THEN
        RAISE EXCEPTION 'Il cliente % ha già una tessera fedeltà', NEW.cliente_c_f;
    END IF;
    
    -- Validazione valori
    IF NEW.punti < 0 THEN
        RAISE EXCEPTION 'I punti della tessera fedeltà non possono essere negativi';
    END IF;
    
    -- Se tutti i controlli passano, procedi
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
    pn.prezzo,
    pn.disponibilita,
    CASE 
        WHEN pn.disponibilita > 0 THEN 'Disponibile'
        ELSE 'Esaurito'
    END as stato_disponibilita
FROM negozi n
JOIN prodotto_negozio pn ON n.id = pn.negozio_id
JOIN prodotti p ON pn.prodotto_id = p.id
ORDER BY n.name, p.name;

-- VIEW: Solo prodotti disponibili
CREATE VIEW prodotti_disponibili AS 
SELECT 
    n.name as negozio,
    p.name as prodotto,
    pn.prezzo,
    pn.disponibilita
FROM negozi n
JOIN prodotto_negozio pn ON n.id = pn.negozio_id
JOIN prodotti p ON pn.prodotto_id = p.id
WHERE pn.disponibilita > 0
ORDER BY n.name, pn.prezzo;

-- VIEW: Tessere con più di 300 punti
CREATE VIEW tessere_maggiori_punti AS
SELECT 
    fc.cliente_c_f as cliente_cf,
    fc.id as tessera_id,
    fc.punti as saldo_punti
FROM fidelity_card fc
WHERE fc.punti > 300
ORDER BY saldo_punti DESC, cliente_cf DESC;


CREATE OR REPLACE FUNCTION aggiorna_punti_fidelity(cliente_cf VARCHAR, punti INT) RETURNS VOID AS $$
BEGIN
    UPDATE fidelity_card
    SET punti = punti + punti
    WHERE cliente_c_f = cliente_cf;
    RAISE NOTICE 'Punti aggiornati per il cliente %: nuovi punti = %', cliente_cf, punti;
END;
$$ LANGUAGE plpgsql;


-- Listaa tessere rilasciate per un negozio specifico
CREATE OR REPLACE FUNCTION lista_tessere_rilasciate(negozio_id INT) RETURNS TABLE(tessera_id INT, cliente_c_f VARCHAR, punti INT, data_rilascio DATE) AS $$
BEGIN
    RETURN QUERY
    SELECT id, cliente_c_f, punti, data_rilascio
    FROM fidelity_card
    WHERE negozio_id = negozio_id
    ORDER BY data_rilascio DESC;
END;
$$ LANGUAGE plpgsql;


