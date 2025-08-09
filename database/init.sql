CREATE TABLE users (
    C_F VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100) NOT NULL,
    isAdmin BOOLEAN DEFAULT FALSE
);
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);
CREATE TABLE negozio (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    responsabile VARCHAR(100),
    orari TEXT
);
CREATE TABLE fatture(
    id SERIAL PRIMARY KEY,
    sconto DECIMAL(5, 2) DEFAULT 0.00,
    data DATE NOT NULL,
    totale DECIMAL(5, 2)
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
UPDATE OF password ON users FOR EACH ROW EXECUTE FUNCTION md5_hash_password();
-- Inserimento dati (le password verranno hashate automaticamente)
INSERT INTO users (c_f,name, email, password, isAdmin)
VALUES (
        'GRAZ12345678',
        'Graziano',
        'graziano@example.com',
        'mypassword',
        FALSE
    ),
    (
        'MARIA12345678',
        'Admin',
        'admin@example.com',
        'adminpassword',
        TRUE
    );