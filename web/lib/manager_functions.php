<?php

function isAuthorized(): bool
{
    global $db;
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        $sql = "SELECT * FROM manager where c_f = $1";
        $name = "is_authorized_" . uniqid();
        $resource = pg_prepare($db, $name, $sql);
        if (!$resource) {
            echo "Errore nella preparazione della query." . pg_last_error($db);
            return false;
        }
        $resource = pg_execute($db, $name, [$_SESSION['user_id']]);
        if (!$resource) {
            echo "Errore nell'esecuzione della query." . pg_last_error($db);
            return false;
        }
        return true;
    }
    return false;
}

function getClienti(): array
{
    if (!isAuthorized()) {
        return ['error' => "Accesso non autorizzato"];
    }
    global $db;

    $sql = "SELECT * FROM clienti";
    $name = "get_clienti_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return [];
    }
    $resource = pg_execute($db, $name, []);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return [];
    }
    $utenti = [];
    if ($resource) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $utenti[] = $row;
        }
    }
    return $utenti;
}
function getManager(): array
{
    if (!isAuthorized()) {
        return ['error' => "Accesso non autorizzato"];
    }
    global $db;

    $sql = "SELECT * FROM manager";
    $name = "get_manager_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return [];
    }
    $resource = pg_execute($db, $name, []);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return [];
    }
    $utenti = [];
    if ($resource) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $utenti[] = $row;
        }
    }
    return $utenti;
}


function addCliente()
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    $sql = "INSERT INTO clienti (c_f,name,email,password) VALUES ($1,$2,$3,$4)";
    $name = "add_cliente_" . uniqid();
    $params = [$_POST['codice_fiscale'], $_POST['name'], $_POST['email'], $_POST['password']];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function addManager()
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    $sql = "INSERT INTO manager (c_f,name,email,password) VALUES ($1,$2,$3,$4)";
    $name = "add_manager_" . uniqid();
    $params = [$_POST['codice_fiscale'], $_POST['name'], $_POST['email'], $_POST['password']];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function addUser()
{
    if ($_POST['user_type'] === 'cliente') {
        return addCliente();
    } else if ($_POST['user_type'] === 'manager') {
        return addManager();
    }
}

function deleteUser()
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    if ($_POST['user_type'] === 'cliente') {
        $sql = "DELETE FROM clienti WHERE c_f = $1";
    } else if ($_POST['user_type'] === 'manager') {
        $sql = "DELETE FROM manager WHERE c_f = $1";
    } else {
        return ['error' => 'Tipo di utente non valido'];
    }

    $name = "elimina_user_" . uniqid();
    $params = [$_POST['codice_fiscale']];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function editUser()
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    if ($_POST['user_type'] === 'cliente') {
        $sql = "UPDATE clienti SET name = $1, email = $2 WHERE c_f = $3";
    } else if ($_POST['user_type'] === 'manager') {
        $sql = "UPDATE manager SET name = $1, email = $2 WHERE c_f = $3";
    } else {
        return ['error' => 'Tipo di utente non valido'];
    }

    $name = "modifica_user_" . uniqid();
    $params = [$_POST['name'], $_POST['email'], $_POST['codice_fiscale']];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function addProdotto($name_prodotto, $description)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    $sql = "INSERT INTO prodotti (name, description) VALUES ($1, $2)";
    $name = "add_prodotto_" . uniqid();
    $params = [$name_prodotto, $description];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function editProdotto($id, $name_prodotto, $description)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    $sql = "UPDATE prodotti SET name = $1, description = $2 WHERE id = $3";
    $name = "edit_prodotto_" . uniqid();
    $params = [$name_prodotto, $description, $id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function deleteProdotto($id)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato'];
    }
    global $db;

    $sql = "DELETE FROM prodotti WHERE id = $1";
    $name = "delete_prodotto_" . uniqid();
    $params = [$id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function addNegozio($nome_negozio, $indirizzo, $responsabile, $orario_apertura, $orario_chiusura)
{
    global $db;
    $sql = 'INSERT INTO negozi (name, address, responsabile, orario_apertura, orario_chiusura) VALUES ($1, $2, $3, $4, $5)';
    $name = "add_negozio_" . uniqid();
    $params = [$nome_negozio, $indirizzo, $responsabile, $orario_apertura, $orario_chiusura];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function deleteNegozio($id)
{
    global $db;
    $sql = 'DELETE FROM negozi WHERE id = $1';
    $name = "delete_negozio_" . uniqid();
    $params = [$id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function modificaInformazioniNegozio($id, $nome_negozio, $indirizzo, $responsabile)
{
    global $db;
    $sql = 'UPDATE negozi SET name = $1, address = $2, responsabile = $3 WHERE id = $4';
    $name = "edit_negozio_" . uniqid();
    $params = [$nome_negozio, $indirizzo, $responsabile, $id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function modificaOrariNegozio($id, $orario_apertura, $orario_chiusura)
{
    global $db;
    $sql = 'UPDATE negozi SET orario_apertura = $1, orario_chiusura = $2 WHERE id = $3';
    $name = "modifica_orari_negozio_" . uniqid();
    $params = [$orario_apertura, $orario_chiusura, $id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function getNegozio($negozio_id)
{
    global $db;
    $sql = 'SELECT * FROM negozi where id = $1';
    $name = "get_negozio_" . uniqid();
    $params = [$negozio_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $row = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
    return $row;
}

function eliminaNegozio($negozio_id){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "DELETE FROM negozi WHERE id = $1";
    $name = "elimina_negozio_" . uniqid();
    $params = [$negozio_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function getStoricoTessere()
{
    global $db;
    $sql = 'SELECT * FROM storico_tessere ORDER BY data_eliminazione_negozio DESC';
    $name = "get_storico_tessere_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, []);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $tessere = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $tessere[] = $row;
        }
    }
    return $tessere;
}

function getProdottiNegozio($negozio_id): array
{
    global $db;
    $sql = 'SELECT p.name as nome,pn.prezzo_unitario as prezzo,pn.disponibilita as disponibilita,p.id FROM prodotto_negozio pn join prodotti p on pn.prodotto = p.id where pn.negozio = $1';
    $name = "get_prodotti_negozio_" . uniqid();
    $params = [$negozio_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $prodotto = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $prodotto[] = $row;
        }
    }
    return $prodotto;
}
function eliminaProdottoNegozio($negozio_id, $prodotto_id)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "DELETE FROM prodotto_negozio WHERE negozio = $1 AND prodotto = $2";
    $name = "elimina_prodotto_negozio_" . uniqid();
    $params = [$negozio_id, $prodotto_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function modificaPrezzoProdottoNegozio($negozio_id, $prodotto_id, $nuovo_prezzo)
{
    global $db;
    $sql = 'UPDATE prodotto_negozio SET prezzo_unitario = $1 WHERE negozio = $2 AND prodotto = $3';
    $name = "modifica_prezzo_prodotto_negozio_" . uniqid();
    $params = [$nuovo_prezzo, $negozio_id, $prodotto_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function ordinaProdottoNegozio($prodotto_id, $negozio_id, $quantita)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "INSERT INTO ordini (manager_richiedente,negozio,prodotto,quantita) VALUES ($1,$2,$3,$4)";
    $name = "ordina_prodotto_negozio_" . uniqid();
    $params = [$_SESSION['user_id'], $negozio_id, $prodotto_id, $quantita];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        $error = pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'.$error];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        $error = pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'.$error];
    }
    return ['success' => true];
}

function getTessereNegozio($negozio_id)
{
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "SELECT * from lista_tessere_rilasciate($1)";
    $name = "ottieni_tessere_negozio_" . uniqid();
    $params = [$negozio_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $tessere = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $tessere[] = $row;
        }
    }
    return $tessere;
}

function getTessereMaggioriPunti(){

    global $db;

    $sql = "SELECT * FROM tessere_maggiori_punti";
    $name = "ottieni_tessere_maggiori_punti_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, []);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $tessere = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $tessere[] = $row;
        }
    }
    return $tessere;
}

function getFornitori(){
    global $db;

    $sql = "SELECT * FROM fornitori";
    $name = "ottieni_fornitori_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, []);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $fornitori = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $fornitori[] = $row;
        }
    }
    return $fornitori;
}

function getFornitore($p_iva){
    global $db;

    $sql = "SELECT * FROM fornitori WHERE p_iva = $1";
    $name = "ottieni_fornitore_" . uniqid();
    $params = [$p_iva];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $fornitore = null;
    if (isset($resource) && !is_null($resource)) {
        $fornitore = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
    }
    return $fornitore;
}
function addFornitore($p_iva, $nome_fornitore, $indirizzo) {
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "INSERT INTO fornitori (p_iva, name, indirizzo) VALUES ($1, $2, $3)";
    $name = "aggiungi_fornitore_" . uniqid();
    $params = [$p_iva, $nome_fornitore, $indirizzo];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function modificaFornitore($p_iva,$nome_fornitore,$indirizzo){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "UPDATE fornitori SET name = $1, indirizzo = $2 WHERE p_iva = $3";
    $name = "modifica_fornitore_" . uniqid();
    $params = [$nome_fornitore, $indirizzo, $p_iva];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function eliminaFornitore($p_iva){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "DELETE FROM fornitori WHERE p_iva = $1";
    $name = "elimina_fornitore_" . uniqid();
    $params = [$p_iva];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function getProdottiFornitore($p_iva){
    global $db;

    $sql = "SELECT p.name as nome, pf.prezzo_unitario as prezzo, pf.disponibilita as quantita,pf.prodotto as id FROM prodotto_fornitore pf join prodotti p on pf.prodotto = p.id WHERE pf.fornitore = $1";
    $name = "ottieni_prodotti_fornitore_" . uniqid();
    $params = [$p_iva];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $prodotti = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $prodotti[] = $row;
        }
    }
    return $prodotti;
}

function addProdottoFornitore($prodotto_id,$p_iva,$quantita,$prezzo_unitario){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "INSERT INTO prodotto_fornitore (fornitore,prodotto,disponibilita,prezzo_unitario) VALUES ($1,$2,$3,$4)";
    $name = "aggiungi_prodotto_fornitore_" . uniqid();
    $params = [$p_iva, $prodotto_id, $quantita, $prezzo_unitario];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function modificaProdottoFornitore($prodotto_id,$p_iva,$quantita,$prezzo_unitario){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "UPDATE prodotto_fornitore SET disponibilita = $1, prezzo_unitario = $2 WHERE fornitore = $3 AND prodotto = $4";
    $name = "modifica_prodotto_fornitore_" . uniqid();
    $params = [$quantita, $prezzo_unitario, $p_iva, $prodotto_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function eliminaProdottoFornitore($prodotto_id,$p_iva){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "DELETE FROM prodotto_fornitore WHERE fornitore = $1 AND prodotto = $2";
    $name = "elimina_prodotto_fornitore_" . uniqid();
    $params = [$p_iva, $prodotto_id];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    return ['success' => true];
}

function getOrdiniFornitore($p_iva){
    if (!isAuthorized()) {
        return ['error' => 'Accesso non autorizzato.'];
    }
    global $db;

    $sql = "SELECT * FROM storicoOrdiniFornitore($1)";
    $name = "ottieni_ordini_fornitore_" . uniqid();
    $params = [$p_iva];
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query." . pg_last_error($db);
        return ['error' => 'Errore nella preparazione della query.'];
    }
    //aggiunto '@' per sopprimere warning del db
    $resource = @pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query." . pg_last_error($db);
        return ['error' => 'Errore nell\'esecuzione della query.'];
    }
    $ordini = [];
    if (isset($resource) && !is_null($resource)) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $ordini[] = $row;
        }
    }
    return $ordini;
}