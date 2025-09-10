<?php
session_start();
include_once('lib/functions.php');
include_once('lib/cart_functions.php');
// Gestione logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_cart']) && isset($_POST['prodotto_id']) && isset($_POST['negozio_id'])) {
    $prodotto_id = $_POST['prodotto_id'] ?? null;
    $quantita = $_POST['quantita'] ?? 1;

    if ($prodotto_id) {
        addToCart($prodotto_id, $_POST['negozio_id'], $_POST['prezzo_unitario'], $quantita);
    }
}

// Recupera negozio_id dall'URL
$negozio_id = $_GET['negozio_id'] ?? null;

$prodotti = getProdottiNegozi();

// Costruisce l'URL completo con parametri GET
$pagelink = $_SERVER['PHP_SELF'];
if ($negozio_id) {
    $pagelink .= '?negozio_id=' . urlencode($negozio_id);
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $negozio_id ? 'Prodotti del Negozio' : 'Tutti i Prodotti'; ?> - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include('navbar.php'); ?>
    
    <!-- Header dinamico -->
    <header class="bg-primary text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <?php if ($negozio_id): ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb text-white-50 mb-2">
                                <li class="breadcrumb-item">
                                    <a href="negozi.php" class="text-white-75">
                                        <i class="bi bi-shop me-1"></i>Negozi
                                    </a>
                                </li>
                                <li class="breadcrumb-item active text-white">Prodotti</li>
                            </ol>
                        </nav>
                        <h1><i class="bi bi-box-seam me-2"></i>Prodotti del Negozio</h1>
                        <p class="mb-0">Scopri tutti i prodotti disponibili in questo negozio</p>
                    <?php else: ?>
                        <h1><i class="bi bi-grid me-2"></i>Tutti i Prodotti</h1>
                        <p class="mb-0">Esplora il catalogo completo di tutti i negozi</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if ($negozio_id): ?>
                        <a href="negozi.php" class="btn btn-light">
                            <i class="bi bi-arrow-left me-2"></i>Torna ai Negozi
                        </a>
                    <?php endif; ?>
                    <?php if (isCliente()): ?>
                        <a href="carrello.php" class="btn btn-success ms-2">
                            <i class="bi bi-cart me-2"></i>Carrello
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container my-5 flex-grow-1">
        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Prodotti Disponibili</h5>
                        <h3 class="text-primary"><?php echo count(array_filter($prodotti, fn($p) => $p['disponibilita'] > 0)); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Prodotti in Stock</h5>
                        <h3 class="text-success"><?php echo array_sum(array_column($prodotti, 'disponibilita')); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($prodotti)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">Nessun prodotto disponibile</h3>
                <p class="text-muted">
                    <?php echo $negozio_id ? 'Questo negozio non ha ancora prodotti in vendita.' : 'Non ci sono prodotti disponibili al momento.'; ?>
                </p>
                <a href="negozi.php" class="btn btn-primary">
                    <i class="bi bi-shop me-2"></i>Esplora Altri Negozi
                </a>
            </div>
        <?php else: ?>
            <!-- Grid di prodotti -->
            <div class="row g-4">
                <?php foreach ($prodotti as $prodotto): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header <?php echo $prodotto['disponibilita'] > 0 ? 'bg-success' : 'bg-secondary'; ?> text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-box me-2"></i>
                                    <?php echo htmlspecialchars($prodotto['prodotto_nome']); ?>
                                </h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h6><i class="bi bi-info-circle text-primary me-2"></i>Descrizione</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($prodotto['prodotto_descrizione'] ?? 'Nessuna descrizione disponibile'); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="bi bi-currency-euro text-success me-2"></i>Prezzo</h6>
                                    <h4 class="text-success mb-0">€<?php echo number_format($prodotto['prezzo_unitario'], 2); ?></h4>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="bi bi-boxes text-info me-2"></i>Disponibilità</h6>
                                    <?php if ($prodotto['disponibilita'] > 0): ?>
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle me-1"></i>
                                            <?php echo $prodotto['disponibilita']; ?> pezzi disponibili
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6">
                                            <i class="bi bi-x-circle me-1"></i>
                                            Esaurito
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!$negozio_id): ?>
                                    <div class="mb-3">
                                        <h6><i class="bi bi-shop text-warning me-2"></i>Negozio</h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($prodotto['negozio_nome']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Area acquisto per clienti -->
                                <?php if (isCliente()): ?>
                                    <div class="mt-auto">
                                        <?php if ($prodotto['disponibilita'] > 0): ?>
                                            <form method="POST" action="<?php echo $pagelink; ?>" class="d-grid">
                                                <input type="hidden" name="add_cart" value="aggiungi_carrello">
                                                <input type="hidden" name="prodotto_id" value="<?php echo $prodotto['prodotto_id']; ?>">
                                                <input type="hidden" name="negozio_id" value="<?php echo $prodotto['negozio_id']; ?>">
                                                <input type="hidden" name="prezzo_unitario" value="<?php echo $prodotto['prezzo_unitario']; ?>">
                                                
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-123"></i>
                                                    </span>
                                                    <input
                                                        type="number"
                                                        name="quantita"
                                                        value="1"
                                                        min="1"
                                                        max="<?php echo $prodotto['disponibilita']; ?>"
                                                        class="form-control"
                                                        aria-label="Quantità">
                                                    <span class="input-group-text">pz</span>
                                                </div>
                                                
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-cart-plus me-2"></i>Aggiungi al Carrello
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-x-circle me-2"></i>Non Disponibile
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
</body>

</html>