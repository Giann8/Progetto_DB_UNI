<?php

function createBill($cartItems, $wantSconto = FALSE): array
{

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Utente non autenticato");
    }
    $bill = [
        'totale' => 0,
        'articoli' => [],
        'cliente_id' => $_SESSION['user_id'],
        'sconto' => 0
    ];
    foreach ($cartItems as $item) {
        $row = getInfoProdottoNegozio($item['prodotto_id'], $item['negozio_id']);
        if (!is_null($row)) {
            $bill['articoli'][] = [
                'negozio' => $row['negozio'],
                'prodotto' => $row['prodotto'],
                'quantita' => $item['quantita'],
                'prezzo_unitario' => $row['prezzo_unitario']
            ];

            $bill['totale'] += $row['prezzo_unitario'] * $item['quantita'];
        } else {
            return ['errore'=>'Prodotto non trovato: ' . htmlspecialchars(print_r($item, true))];
        }
    }
    if ($wantSconto) {
        $bill['sconto'] = getScontoCliente($_SESSION['user_id']);
    }

    return $bill;
}

function addItemToBill($bill, $id_fattura, $key_prodotto)
{
    global $db;
    $sql = "INSERT INTO riga_fattura(fattura,prodotto,negozio,quantita) VALUES ($1, $2, $3, $4)";
    $params = array($id_fattura, $bill['articoli'][$key_prodotto]['prodotto'], $bill['articoli'][$key_prodotto]['negozio'], $bill['articoli'][$key_prodotto]['quantita']);
    $name="add_item_to_bill_".uniqid();
    $resource = pg_prepare($db, $name, $sql);
    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return false;
    }
    $resource = pg_execute($db, $name, $params);
    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return false;
    }
    return true;
}

function sendBill($bill): bool
{
    global $db;

    $sql = "INSERT INTO fatture (cliente, totale, sconto) VALUES ($1, $2, $3) RETURNING id";
    $resource = pg_prepare($db, "send_bill", $sql);

    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return false;
    }

    $resource = pg_execute($db, "send_bill", [
        $bill['cliente_id'],
        $bill['totale'],
        $bill['sconto']
    ]);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return false;
    }


    $row = pg_fetch_array($resource, NULL, PGSQL_ASSOC);

    if (isset($row['id']) && is_numeric($row['id'])) {
        foreach (array_keys($bill['articoli']) as $key) {
           if (!addItemToBill($bill, $row['id'], $key)) {
               return false;
           }
        }
    } else {
        echo "Errore: ID fattura non ottenuto<br>";
        return false;
    }

    $_SESSION['alert'] = "success";
    return true;
}


function getUserBills($userId): array
{
    global $db;

    $sql = "SELECT * FROM fatture f WHERE cliente = $1 order by f.data_emissione DESC, f.id DESC";
    $resource = pg_prepare($db, "get_user_bills", $sql);

    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return [];
    }

    $resource = pg_execute($db, "get_user_bills", [$userId]);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return [];
    }

    $bills = [];
    while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
        $bills[] = $row;
    }

    return $bills;
}
function getBillsArticles($billId): array
{
    global $db;
    $name="get_bills_articles_".uniqid();
    $sql = "SELECT p.name as prodotto, rf.negozio,rf.quantita,rf.subtotale FROM riga_fattura rf join prodotti p ON rf.prodotto = p.id WHERE fattura = $1";
    $resource = pg_prepare($db, $name, $sql);

    if (!$resource) {
        echo "Errore nella preparazione della query: " . pg_last_error($db);
        return [];
    }

    $resource = pg_execute($db, $name, [$billId]);

    if (!$resource) {
        echo "Errore nell'esecuzione della query: " . pg_last_error($db);
        return [];
    }

    $articles = [];
    while ($row = pg_fetch_array($resource, NULL, PGSQL_ASSOC)) {
        $articles[] = $row;
    }

    return $articles;
}