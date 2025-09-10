<?php
$host = getenv('PHP_DB_HOST') ?? 'postgres';  // Nome del servizio docker
$port = getenv('PHP_DB_PORT') ?? '5432';
$dbname = getenv('POSTGRES_DB') ?? 'mydb';
$user = getenv('POSTGRES_USER') ?? 'Graziano';  // Come nel tuo .env
$password = getenv('POSTGRES_PASSWORD') ?? 'subemelaradio';  // Come nel tuo .env

$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$db = pg_connect($connection_string) or die('Connessione fallita: ' . pg_last_error());

/**
 * Verifica se l'utente è loggato
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Verifica se l'utente è un manager
 */
function isManager(): bool
{
    return isLoggedIn() && $_SESSION['user_type'] === 'manager';
}

/**
 * Verifica se l'utente è un cliente
 */
function isCliente(): bool
{
    return isLoggedIn() && $_SESSION['user_type'] === 'cliente';
}

/**
 * Valida se l'email è corretta
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


/**
 * Effettua il login dell'utente
 */
function login($email, $password, $user_type): bool
{
    global $db;
    if ($user_type === 'manager') {
        $table = "manager";
    } else if ($user_type === 'cliente') {
        $table = "clienti";
    }

    // Query per verificare le credenziali
    $sql = "SELECT * FROM $table WHERE email = $1 AND password = $2";
    $params = array(normalizzaEmail($email), md5($password));
    $resource = pg_prepare($db, "login_query", $sql);
    $resource = pg_execute($db, "login_query", $params);

    if ($resource) {
        $user = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
        if ($user) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['c_f'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            pg_close($db);
            return true;
        }
    }
    return false;
}

function logout(): void
{
    session_unset();
    session_destroy();
    header("Location: Login.php");
    exit();
}

function getProdottiNegozi(): array
{
    global $db;

    if (isset($_GET['negozio_id']) && is_numeric($_GET['negozio_id'])) {
        $negozio_id = (int)$_GET['negozio_id'];
    } else {
        $negozio_id = null;
    }

    if (is_null($negozio_id)) {
        $sql = "SELECT * FROM catalogo_negozi";
        $resource = pg_prepare($db, "get_all_prodotti", $sql);
        $resource = pg_execute($db, "get_all_prodotti", []);
    } else {
        $sql = "SELECT * FROM catalogo_negozi WHERE negozio_id = $1";
        $resource = pg_prepare($db, "get_prodotti_negozio", $sql);
        $resource = pg_execute($db, "get_prodotti_negozio", [$negozio_id]);
    }

    $prodotti = [];
    if ($resource) {
        while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
            $prodotti[] = $row;
        }
    }
    return $prodotti;
}

function changePassword($user_id, $new_password, $user_type): bool
{
    global $db;

    if ($user_type === 'manager') {
        $table = "manager";
    } else if ($user_type === 'cliente') {
        $table = "clienti";
    }

    $sql = "UPDATE $table SET password = $1 WHERE id = $2";
    $params = array($new_password, $user_id);
    $resource = pg_prepare($db, "change_password", $sql);
    $resource = pg_execute($db, "change_password", $params);

    return $resource !== false;
}

function getInfoProdotto($prodotto_id): array
{
    global $db;

    $sql = "SELECT * FROM prodotti WHERE id = $1";
    $params = array($prodotto_id);
    $resource = pg_prepare($db, "get_info_prodotto", $sql);
    $resource = pg_execute($db, "get_info_prodotto", $params);

    if (isset($resource) && !is_null($resource)) {
        $prodotto = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
        ## fatto per utilizzo in foreach
        pg_free_result($resource);
        return $prodotto;
    }

    return [];
}

function getInfoProdottoNegozio($prodotto_id, $negozio_id): array
{
    global $db;

    $sql = "SELECT * FROM prodotto_negozio WHERE prodotto = $1 AND negozio = $2";
    $params = array($prodotto_id, $negozio_id);
    $resource = pg_prepare($db, "get_info_prodotto_negozio", $sql);
    $resource = pg_execute($db, "get_info_prodotto_negozio", $params);

    if (isset($resource) && !is_null($resource)) {
        $prodotto = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
        pg_free_result($resource);
        return $prodotto;
    }

    return [];
}

function getScontoCliente($cliente_id): float
{
    global $db;

    $sql = "SELECT sconto_percentuale as sconto FROM sconti_clienti WHERE cliente = $1";
    $name = "get_sconto_cliente_" . uniqid();
    $params = array($cliente_id);
    $resource = pg_prepare($db, $name, $sql);

    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return 0.0;
    }

    $resource = pg_execute($db, $name, $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return 0.0;
    }

    if (isset($resource) && !is_null($resource) && pg_num_rows($resource) > 0) {
        $cliente = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
        pg_free_result($resource);
        return (float)$cliente['sconto'];
    }
    return 0.0;
}

function getTesseraFedelta($cliente_id): array
{
    global $db;

    $sql = "SELECT * FROM fidelity_card WHERE cliente = $1";
    $params = array($cliente_id);
    $name = "get_tessera_fedelta_" . uniqid();
    $resource = pg_prepare($db, $name, $sql);
    $resource = pg_execute($db, $name, $params);

    if (isset($resource) && !is_null($resource) && pg_num_rows($resource) > 0) {
        $tessera = pg_fetch_array($resource, NULL, PGSQL_ASSOC);
        pg_free_result($resource);
        return $tessera;
    }

    return [];
}

function hasFidelityCard($cliente_id): bool
{
    $tessera = getTesseraFedelta($cliente_id);
    return !empty($tessera);
}

function normalizzaEmail($email): string
{
    return strtolower(trim($email));
}

function normalizzaC_F($C_F): string
{
    return strtoupper(trim($C_F));
}

function changeCustomerPassword($customer_id, $new_password, $current_password): bool
{
    global $db;

    $sql = "UPDATE clienti SET password = $1 WHERE c_f = $2 AND password = $3";
    $params = array($new_password, $customer_id, md5($current_password));
    $resource = pg_prepare($db, "change_customer_password", $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return false;
    }
    $resource = pg_execute($db, "change_customer_password", $params);


    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return false;
    }
    if (pg_affected_rows($resource) > 0) {
        return true;
    }
    return false;
}

function changeManagerPassword($manager_id, $new_password, $current_password): bool
{
    global $db;

    $sql = "UPDATE manager SET password = $1 WHERE c_f = $2 AND password = $3";
    $params = array($new_password, $manager_id, md5($current_password));
    $resource = pg_prepare($db, "change_manager_password", $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return false;
    }
    $resource = pg_execute($db, "change_manager_password", $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return false;
    }
    if (pg_affected_rows($resource) > 0) {
        return true;
    }
    return false;}

function richiediTesseraFedelta($cliente_id, $negozio_id): bool
{
    global $db;

    $sql = "INSERT INTO fidelity_card (cliente, negozio) VALUES ($1, $2)";
    $params = array($cliente_id, $negozio_id);
    $resource = pg_prepare($db, "richiedi_tessera_fedelta", $sql);

    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return false;
    }

    $resource = pg_execute($db, "richiedi_tessera_fedelta", $params);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return false;
    }

    return true;
}

function getProdotti()
{
    global $db;
    $sql = "SELECT * FROM prodotti";
    $resource = pg_prepare($db, "get_prodotti", $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return [];
    }
    $resource = pg_execute($db, "get_prodotti", []);
    $prodotti = [];
    if ($resource) {
        while ($row = pg_fetch_assoc($resource)) {
            $prodotti[] = $row;
        }
        pg_free_result($resource);
    }
    return $prodotti;
}

function getNegozi(): array
{
    global $db;
    $sql = "SELECT id, name, address, responsabile, orario_apertura, orario_chiusura FROM negozi ORDER BY name";
    $result = pg_prepare($db, "fetch_negozi", $sql);
    if (!$result) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return [];
    }
    $result = pg_execute($db, "fetch_negozi", []);
    if (!$result) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return [];
    }
    $negozi = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $negozi[] = $row;
        }
    }
    return $negozi;
}
