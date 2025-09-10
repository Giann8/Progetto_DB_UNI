<?php
session_start();
include_once('lib/functions.php');

// Imposta il fuso orario italiano
date_default_timezone_set('Europe/Rome');

// Gestione logout PRIMA di qualsiasi output HTML
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

// Verifica autenticazione
if (!isLoggedIn()) {
    header("Location: Login.php");
    exit();
}

// Solo i manager possono accedere a questa pagina
if (!isManager()) {
    header("Location: dashboard_cliente.php");
    exit();
}

include_once('lib/manager_functions.php');



// Gestione delle azioni POST
if (isset($_POST['action'])&& $_POST['action'] === 'add_fornitore') {
    $p_iva = trim($_POST['p_iva'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $indirizzo = trim($_POST['indirizzo'] ?? '');

    if ($p_iva && $name && $indirizzo) {
        $result = addFornitore($p_iva, $name, $indirizzo);
        if (isset($result['success']) && $result['success']) {
            $success_message = "Fornitore aggiunto con successo.";
        } else if (isset($result['error'])) {
            $error_message = $result['error'];
        } else {
            $error_message = "Errore sconosciuto durante l'aggiunta del fornitore.";
        }
    } else {
        $error_message = "Tutti i campi devono essere compilati.";
    }
}

$fornitori = getFornitori();

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Fornitori - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <header class="bg-success text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-truck me-2"></i>Gestione Fornitori</h1>
                    <p class="mb-0">Gestisci i fornitori e i loro prodotti</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addFornitoreModal">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Fornitore
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-5">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-truck text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Totale Fornitori</h5>
                        <h3 class="text-success"><?php echo count($fornitori); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($fornitori)): ?>
            <div class="text-center py-5">
                <i class="bi bi-truck" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">Nessun fornitore trovato</h3>
                <p class="text-muted">Al momento non ci sono fornitori disponibili nel sistema.</p>
                <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addFornitoreModal">
                    <i class="bi bi-plus-circle me-2"></i>Aggiungi il primo fornitore
                </button>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($fornitori as $fornitore): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-building me-2"></i><?php echo htmlspecialchars($fornitore['name']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><i class="bi bi-card-text text-primary me-2"></i>Partita IVA</h6>
                                    <p class="text-muted mb-0 font-monospace"><?php echo htmlspecialchars($fornitore['p_iva']); ?></p>
                                </div>

                                <div class="mb-3">
                                    <h6><i class="bi bi-geo-alt text-success me-2"></i>Indirizzo</h6>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($fornitore['indirizzo'] ?? 'Non specificato'); ?></p>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-grid">
                                    <a href="gestione_fornitore.php?p_iva=<?php echo $fornitore['p_iva']; ?>"
                                        class="btn btn-success">
                                        <i class="bi bi-gear me-2"></i>Gestisci Fornitore
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Aggiungi Fornitore -->
    <div class="modal fade" id="addFornitoreModal" tabindex="-1" aria-labelledby="addFornitoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addFornitoreModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Nuovo Fornitore
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_fornitore">

                        <div class="mb-3">
                            <label for="p_iva" class="form-label">
                                <i class="bi bi-card-text me-1"></i>Partita IVA *
                            </label>
                            <input type="text" class="form-control" id="p_iva" name="p_iva"
                                maxlength="20" required
                                placeholder="Es: IT12345678901, DE123456789, FR12345678901"
                                style="text-transform: uppercase;">
                            <div class="form-text">Inserisci la Partita IVA (può contenere lettere e numeri)</div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-building me-1"></i>Nome Azienda *
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                maxlength="100" required placeholder="Es: Fornitore SRL">
                        </div>

                        <div class="mb-3">
                            <label for="indirizzo" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i>Indirizzo *
                            </label>
                            <textarea class="form-control" id="indirizzo" name="indirizzo"
                                rows="3" required placeholder="Via, numero civico, città, provincia"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Aggiungi Fornitore
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2025 Sistema Gestione Negozi. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validazione Partita IVA in tempo reale - accetta lettere e numeri
        document.getElementById('p_iva').addEventListener('input', function(e) {
            // Rimuove spazi e converte in maiuscolo, limita a 20 caratteri
            e.target.value = e.target.value.replace(/\s/g, '').toUpperCase().substring(0, 20);
        });
    </script>
</body>

</html>