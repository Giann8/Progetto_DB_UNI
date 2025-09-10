<?php
session_start();

// Blocca warning e notice
error_reporting(E_ERROR | E_PARSE);

include_once('lib/functions.php');
include_once('lib/cart_functions.php');
include_once('lib/bill_functions.php');
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

// Gestione rimozione prodotto dal carrello
if (isset($_POST['action']) && $_POST['action'] === 'remove_from_cart' && isset($_POST['prodotto_id']) && isset($_POST['negozio_id'])) {
    $prodotto_id = $_POST['prodotto_id'] ?? null;
    $negozio_id = $_POST['negozio_id'] ?? null;
    if ($prodotto_id && $negozio_id) {
        removeFromCart($prodotto_id, $negozio_id);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Gestione aggiornamento quantità
if (isset($_POST['action']) && $_POST['action'] === 'update_quantity' && isset($_POST['prodotto_id']) && isset($_POST['negozio_id']) && isset($_POST['nuova_quantita'])) {
    $prodotto_id = $_POST['prodotto_id'];
    $negozio_id = $_POST['negozio_id'];
    $nuova_quantita = max(1, intval($_POST['nuova_quantita'])); // Minimo 1
    
    updateCartQuantity($prodotto_id, $negozio_id, $nuova_quantita);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Gestione creazione fattura
if (isset($_POST['action']) && $_POST['action'] === 'create_bill') {
    $cartItems = $_SESSION['carrello'] ?? [];
    if (!empty($cartItems)) {
        $bill = createBill($cartItems, isset($_POST['discount']) && $_POST['discount'] === 'TRUE');
        sendBill($bill);
        clearCart();
        // Redirect dopo l'acquisto per evitare refresh accidentali
        header("Location: dashboard_cliente.php?acquisto=successo");
        exit();
    }
}

if (!isLoggedIn() || !isCliente()) {
    header("Location: login.php");
    exit();
}

if(hasFidelityCard($_SESSION['user_id'])) {
    $sconto = getScontoCliente($_SESSION['user_id']);
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrello - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include('navbar.php'); ?>
    
    <!-- Header del carrello -->
    <header class="bg-success text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb text-white-50 mb-2">
                            <li class="breadcrumb-item">
                                <a href="prodotti.php" class="text-white-75">
                                    <i class="bi bi-grid me-1"></i>Prodotti
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white">Carrello</li>
                        </ol>
                    </nav>
                    <h1><i class="bi bi-cart me-2"></i>Il Tuo Carrello</h1>
                    <p class="mb-0">Rivedi i tuoi prodotti e procedi all'acquisto</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="prodotti.php" class="btn btn-light">
                        <i class="bi bi-arrow-left me-2"></i>Continua Shopping
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container my-5 flex-grow-1">
        <?php if (isset($_SESSION['carrello']) && count($_SESSION['carrello']) > 0): ?>
            <?php
            $total = 0;
            $totalItems = 0;
            $totalQuantity = 0;
            foreach ($_SESSION['carrello'] as $item) {
                $total += $item['prezzo'];
                $totalItems++;
                $totalQuantity += $item['quantita'];
            }
            ?>
            
            <!-- Riepilogo rapido del carrello -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>
                            <strong><?php echo $totalItems; ?> prodotti diversi</strong> nel carrello 
                            per un totale di <strong><?php echo $totalQuantity; ?> pezzi</strong>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="fs-4 fw-bold text-success">
                        <i class="bi bi-currency-euro me-1"></i>
                        €<?php echo number_format($total, 2); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Colonna prodotti -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>Prodotti nel Carrello
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($_SESSION['carrello'] as $item): ?>
                                <?php $prodotto = getInfoProdotto($item['prodotto_id']); ?>
                                <div class="border-bottom p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-5">
                                            <h6 class="fw-bold mb-1">
                                                <?php echo htmlspecialchars($prodotto['name']); ?>
                                            </h6>
                                            <p class="text-muted small mb-2">
                                                <?php echo htmlspecialchars($prodotto['description'] ?? 'Nessuna descrizione disponibile'); ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="bi bi-currency-euro me-1"></i>
                                                €<?php echo number_format($item['prezzo'] / $item['quantita'], 2); ?> cad.
                                            </small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                <!-- Pulsante diminuisci -->
                                                <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="m-0">
                                                    <input type="hidden" name="action" value="update_quantity">
                                                    <input type="hidden" name="prodotto_id" value="<?php echo $item['prodotto_id']; ?>">
                                                    <input type="hidden" name="negozio_id" value="<?php echo $item['negozio_id']; ?>">
                                                    <input type="hidden" name="nuova_quantita" value="<?php echo max(1, $item['quantita'] - 1); ?>">
                                                    <button type="submit" 
                                                            class="btn btn-outline-secondary btn-sm"
                                                            <?php echo $item['quantita'] <= 1 ? 'disabled' : ''; ?>>
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                </form>
                                                
                                                <span class="fw-bold mx-2" style="min-width: 30px; text-align: center;">
                                                    <?php echo $item['quantita']; ?>
                                                </span>
                                                
                                                <!-- Pulsante aumenta -->
                                                <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="m-0">
                                                    <input type="hidden" name="action" value="update_quantity">
                                                    <input type="hidden" name="prodotto_id" value="<?php echo $item['prodotto_id']; ?>">
                                                    <input type="hidden" name="negozio_id" value="<?php echo $item['negozio_id']; ?>">
                                                    <input type="hidden" name="nuova_quantita" value="<?php echo $item['quantita'] + 1; ?>">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="fw-bold text-success">
                                                €<?php echo number_format($item['prezzo'], 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                                <input type="hidden" name="action" value="remove_from_cart">
                                                <input type="hidden" name="prodotto_id" value="<?php echo htmlspecialchars($item['prodotto_id']); ?>">
                                                <input type="hidden" name="negozio_id" value="<?php echo htmlspecialchars($item['negozio_id']); ?>">
                                                <button type="submit" 
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Rimuovere questo prodotto dal carrello?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Colonna riepilogo -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>Riepilogo Ordine
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (hasFidelityCard($_SESSION['user_id']) && $sconto > 0): ?>
                                <div class="mb-3">
                                    <div class="alert alert-warning d-flex align-items-center p-2">
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <small>Tessera fedeltà: sconto del <?php echo $sconto; ?>% disponibile</small>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount" value="TRUE" id="discount_si" onchange="updateTotal()">
                                        <label class="form-check-label" for="discount_si">
                                            Applica sconto fedeltà (<?php echo $sconto; ?>%)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount" value="FALSE" id="discount_no" checked onchange="updateTotal()">
                                        <label class="form-check-label" for="discount_no">
                                            Prezzo pieno
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between text-success mb-2" id="sconto-row" style="display: none !important;">
                                    <span>Sconto applicato:</span>
                                    <span id="sconto-amount" class="fw-bold">-€0.00</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-3 border-top pt-3">
                                <strong class="fs-5">Totale:</strong>
                                <strong class="fs-4 text-success" id="totale-finale">€<?php echo number_format($total, 2); ?></strong>
                            </div>
                            
                            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="d-grid">
                                <input type="hidden" name="action" value="create_bill">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-credit-card me-2"></i>Acquista Ora
                                </button>
                            </form>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Pagamento sicuro
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Carrello vuoto -->
            <div class="text-center py-5">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-3">Il tuo carrello è vuoto</h3>
                <p class="text-muted mb-4">Non hai ancora aggiunto prodotti al carrello.<br>Inizia a fare shopping per riempirlo!</p>
                <a href="prodotti.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-shop me-2"></i>Inizia a Fare Shopping
                </a>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2025 Sistema Gestione Negozi. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Totale base del carrello (calcolato da PHP)
        const baseTotal = <?php echo $total ?? 0; ?>;
        const discountRate = <?php echo $sconto ?? 0; ?> / 100;

        function updateTotal() {
            const discountRadio = document.querySelector('input[name="discount"]:checked');
            const isDiscountApplied = discountRadio && discountRadio.value === 'TRUE';

            const scontoRow = document.getElementById('sconto-row');
            const scontoAmount = document.getElementById('sconto-amount');
            const totaleFinale = document.getElementById('totale-finale');

            if (isDiscountApplied && scontoRow) {
                let discountAmount = baseTotal * discountRate;
                // Limita lo sconto massimo a 100€
                if (discountAmount > 100) {
                    discountAmount = 100;
                }
                const finalTotal = baseTotal - discountAmount;

                // Mostra riga sconto
                scontoRow.style.display = 'flex';
                scontoAmount.textContent = '-€' + discountAmount.toFixed(2);
                totaleFinale.textContent = '€' + finalTotal.toFixed(2);
            } else if (scontoRow) {
                // Nascondi riga sconto
                scontoRow.style.display = 'none';
                totaleFinale.textContent = '€' + baseTotal.toFixed(2);
            }
        }

        // Inizializza il totale al caricamento della pagina
        document.addEventListener('DOMContentLoaded', function() {
            updateTotal();
        });
    </script>
</body>

</html>