<?php
session_start();
include_once('lib/functions.php');

// Imposta il fuso orario italiano
date_default_timezone_set('Europe/Rome');

// Gestione logout PRIMA di qualsiasi output HTML
if(isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

// Verifica autenticazione
if (!isLoggedIn()) {
    header("Location: Login.php");
    exit();
}

$negozi = getNegozi();

if(isManager()){
    include_once('lib/manager_functions.php');
}
if(isset($_POST['action']) && $_POST['action'] === 'add_negozio' && isManager()) {
    // Validazione input
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $responsabile = $_POST['responsabile'] ?? '';
    $orario_apertura = $_POST['orario_apertura'] ?? '09:00';
    $orario_chiusura = $_POST['orario_chiusura'] ?? '18:00';

    if ($name && $address && $orario_apertura && $orario_chiusura) {
        addNegozio($name, $address, $responsabile, $orario_apertura, $orario_chiusura);
        // Ricarica la pagina per vedere il nuovo negozio
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "Tutti i campi obbligatori devono essere compilati.";
    }
}

if(isset($_POST['action']) && $_POST['action']=== 'richiedi_tessera' && isCliente()) {

    if(richiediTesseraFedelta($_SESSION['user_id'], $_POST['negozio_id'])) {
        $success_message = "Richiesta di tessera di fedeltà inviata con successo.";
    } else {
        $error_message = "Si è verificato un errore durante la richiesta della tessera di fedeltà.";
    }
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negozi - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <header class="bg-primary text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-shop me-2"></i>I Nostri Negozi</h1>
                    <p class="mb-0">Trova il negozio più vicino a te</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if (isManager()): ?>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addNegozioModal">
                            <i class="bi bi-plus-circle me-2"></i>Aggiungi Negozio
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container my-5">
        
        <!-- Messaggi di feedback -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-shop-window text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Totale Negozi</h5>
                        <h3 class="text-primary"><?php echo count($negozi); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-clock text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Aperti Ora</h5>
                        <h3 class="text-success">
                            <?php 
                            $ora_corrente = date('H:i:s');
                            $aperti = 0;
                            foreach ($negozi as $negozio) {
                                if ($ora_corrente >= $negozio['orario_apertura'] && $ora_corrente <= $negozio['orario_chiusura']) {
                                    $aperti++;
                                }
                            }
                            echo $aperti;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($negozi)): ?>
            <div class="text-center py-5">
                <i class="bi bi-shop" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">Nessun negozio trovato</h3>
                <p class="text-muted">Al momento non ci sono negozi disponibili nel sistema.</p>
                <?php if (isManager()): ?>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addNegozioModal">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi il primo negozio
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($negozi as $negozio): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-shop me-2"></i><?php echo htmlspecialchars($negozio['name']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><i class="bi bi-geo-alt text-primary me-2"></i>Indirizzo</h6>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($negozio['address'] ?? 'Non specificato'); ?></p>
                                </div>
                                
                                <?php if (!empty($negozio['responsabile'])): ?>
                                <div class="mb-3">
                                    <h6><i class="bi bi-person-badge text-success me-2"></i>Responsabile</h6>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($negozio['responsabile']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-clock me-2"></i>Orari di apertura</h6>
                                    <div class="d-flex justify-content-between">
                                        <span><strong>Apertura:</strong> <?php echo htmlspecialchars($negozio['orario_apertura'] ?? '09:00'); ?></span>
                                        <span><strong>Chiusura:</strong> <?php echo htmlspecialchars($negozio['orario_chiusura'] ?? '18:00'); ?></span>
                                    </div>
                                    <?php 
                                    $ora_corrente = date('H:i:s');
                                    $aperto = ($ora_corrente >= $negozio['orario_apertura'] && $ora_corrente <= $negozio['orario_chiusura']);
                                    ?>

                                    <div class="mt-2">
                                        <?php if ($aperto): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-circle-fill me-1"></i>Aperto ora
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle-fill me-1"></i>Chiuso ora
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-grid gap-2">
                                    <?php if (isCliente()): ?>
                                        <a href="prodotti.php?negozio_id=<?php echo $negozio['id']; ?>" 
                                           class="btn btn-primary">
                                            <i class="bi bi-bag me-2"></i>Vedi Prodotti
                                        </a>
                                        
                                        <?php if (!hasFidelityCard($_SESSION['user_id'])): ?>
                                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="m-0">
                                                <input type="hidden" name="action" value="richiedi_tessera">
                                                <input type="hidden" name="negozio_id" value="<?php echo $negozio['id']; ?>">
                                                <button type="submit" class="btn btn-warning w-100"
                                                        onclick="return confirm('Vuoi richiedere la tessera fedeltà per questo negozio?')">
                                                    <i class="bi bi-star me-2"></i>Richiedi Tessera Fedeltà
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-outline-success w-100" disabled>
                                                <i class="bi bi-check-circle me-2"></i>Tessera Fedeltà Attiva
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (isManager()): ?>
                                        <a href="gestione_negozio.php?negozio_id=<?php echo $negozio['id']; ?>" 
                                           class="btn btn-success">
                                            <i class="bi bi-gear me-2"></i>Gestisci Negozio
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal per Aggiungere Negozio (solo per Manager) -->
    <?php if (isManager()): ?>
    <div class="modal fade" id="addNegozioModal" tabindex="-1" aria-labelledby="addNegozioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNegozioModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Nuovo Negozio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_negozio">
                        <div class="mb-3">
                            <label for="negozioName" class="form-label">Nome Negozio</label>
                            <input type="text" class="form-control" id="negozioName" name="name" required 
                                   placeholder="Centro Commerciale Roma">
                        </div>
                        <div class="mb-3">
                            <label for="negozioAddress" class="form-label">Indirizzo</label>
                            <input type="text" class="form-control" id="negozioAddress" name="address" required 
                                   placeholder="Via Roma 123, Roma">
                        </div>
                        <div class="mb-3">
                            <label for="negozioResponsabile" class="form-label">Responsabile</label>
                            <input type="text" class="form-control" id="negozioResponsabile" name="responsabile" 
                                   placeholder="Mario Rossi">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="orarioApertura" class="form-label">Orario Apertura</label>
                                <input type="time" class="form-control" id="orarioApertura" name="orario_apertura" 
                                       value="09:00" required>
                            </div>
                            <div class="col-md-6">
                                <label for="orarioChiusura" class="form-label">Orario Chiusura</label>
                                <input type="time" class="form-control" id="orarioChiusura" name="orario_chiusura" 
                                       value="18:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Crea Negozio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2023 Sistema Gestione Negozi</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
