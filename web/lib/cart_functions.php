<?php
/**
 * Funzioni per gestire il carrello
 */


/**
 * Inizializza il carrello se non esiste
 */
function initCart() {
    if (!isset($_SESSION['carrello'])) {
        $_SESSION['carrello'] = array();
    }
}

/**
 * Aggiungi prodotto al carrello
 */
function addToCart($prodotto_id, $negozio_id, $prezzo_unitario, $quantita = 1) {
    initCart();
    
    // Crea una chiave composta per identificare univocamente prodotto + negozio
    $cart_key = $prodotto_id . '_' . $negozio_id;
    
    if (isset($_SESSION['carrello'][$cart_key])) {
        $_SESSION['carrello'][$cart_key]['quantita'] += $quantita;
    } else {
        $_SESSION['carrello'][$cart_key] = array(
            'prodotto_id' => $prodotto_id,
            'negozio_id' => $negozio_id,
            'quantita' => $quantita,
            'prezzo' => $prezzo_unitario * $quantita
        );
    }
}

/**
 * Rimuovi prodotto dal carrello
 */
function removeFromCart($prodotto_id, $negozio_id) {
    initCart();

    $cart_key = $prodotto_id . '_' . $negozio_id;

    if (isset($_SESSION['carrello'][$cart_key])) {
        unset($_SESSION['carrello'][$cart_key]);
    }
}

/**
 * Aggiorna quantità prodotto nel carrello
 */
function updateCartQuantity($prodotto_id, $negozio_id, $quantita) {
    initCart();

    $cart_key = $prodotto_id . '_' . $negozio_id;

    if ($quantita <= 0) {
        removeFromCart($prodotto_id, $negozio_id);
    } else if (isset($_SESSION['carrello'][$cart_key])) {
        $prezzo_unitario = $_SESSION['carrello'][$cart_key]['prezzo'] / $_SESSION['carrello'][$cart_key]['quantita'];
        $_SESSION['carrello'][$cart_key]['quantita'] = $quantita;
        $_SESSION['carrello'][$cart_key]['prezzo'] = $prezzo_unitario * $quantita;
    }
}

/**
 * Ottieni contenuto del carrello
 */
function getCart() {
    initCart();
    return $_SESSION['carrello'];
}

/**
 * Conta articoli nel carrello
 */
function getCartCount(): int {
    initCart();
    return array_sum($_SESSION['carrello']);
}

/**
 * Svuota carrello
 */
function clearCart() {
    $_SESSION['carrello'] = array();
}

?>
