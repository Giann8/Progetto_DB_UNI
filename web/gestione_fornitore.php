<?php
session_start();
include_once('lib/functions.php');

// Imposta il fuso orario italiano
date_default_timezone_set('Europe/Rome');

// Gestione logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

// Verifica autenticazione e permessi
if (!isLoggedIn() || !isManager()) {
    header("Location: login.php");
    exit();
}

include_once('lib/manager_functions.php');

// Recupera P.IVA dall'URL
$p_iva = $_GET['p_iva'] ?? null;

if (!$p_iva) {
    header("Location: gestione_fornitori.php");
    exit();
}


// Gestione delle azioni POST
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_fornitore':
            $nome = trim($_POST['nome'] ?? '');
            $indirizzo = trim($_POST['indirizzo'] ?? '');
            
            if ($nome && $indirizzo) {
                modificaFornitore($p_iva, $nome, $indirizzo);
                $success_message = "Fornitore aggiornato con successo!";
            } else {
                $error_message = "Tutti i campi devono essere compilati.";
            }
            break;
            
        case 'delete_fornitore':
            eliminaFornitore($p_iva);
            header("Location: fornitori.php");
            exit();
            break;
            
        case 'add_prodotto_fornitore':
            $prodotto_id = $_POST['prodotto_id'] ?? '';
            $prezzo = $_POST['prezzo'] ?? '';
            $quantita = $_POST['quantita'] ?? '';
            
            if ($prodotto_id && $prezzo && $quantita) {
               $result = addProdottoFornitore($prodotto_id, $p_iva, $quantita, $prezzo);
                if(isset($result['success']) && $result['success']) {
                    $success_message = "Prodotto aggiunto al fornitore!";
                } else {
                    $error_message = $result['error'];
                }
            } else {
                $error_message = "Tutti i campi devono essere compilati.";
            }
            break;
            
        case 'update_prodotto_fornitore':
            $prodotto_id = $_POST['prodotto_id'] ?? '';
            $prezzo = $_POST['prezzo'] ?? '';
            $quantita = $_POST['quantita'] ?? '';
            
            if ($prodotto_id && $prezzo && $quantita) {
               $result = modificaProdottoFornitore($prodotto_id, $p_iva, $quantita, $prezzo);
               if (isset($result['success']) && $result['success']) {
                   $success_message = "Prodotto aggiornato!";
               } else {
                   $error_message = $result['error'];
               }
            }
            break;
            
        case 'remove_prodotto_fornitore':
            $prodotto_id = $_POST['prodotto_id'] ?? '';
            if ($prodotto_id) {
               $result = eliminaProdottoFornitore($prodotto_id, $p_iva);
               if (isset($result['success']) && $result['success']) {
                   $success_message = "Prodotto rimosso dal fornitore!";
               } else {
                   $error_message = $result['error'];
               }
            }
            break;
    }
}

$fornitore = getFornitore($p_iva);
$storicoOrdini = getOrdiniFornitore($p_iva);
$prodotti_fornitore = getProdottiFornitore($p_iva);

// Ottieni tutti i prodotti e rimuovi quelli già associati al fornitore
$tutti_prodotti = getProdotti();
$prodotti_fornitore_ids = array_column($prodotti_fornitore, 'id');
$prodotti_disponibili = array_filter($tutti_prodotti, function($prodotto) use ($prodotti_fornitore_ids) {
    return !in_array($prodotto['id'], $prodotti_fornitore_ids);
});
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Fornitore - <?php echo htmlspecialchars($fornitore['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <header class="bg-success text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb text-white-50 mb-2">
                            <li class="breadcrumb-item">
                                <a href="fornitori.php" class="text-white-75">
                                    <i class="bi bi-truck me-1"></i>Fornitori
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white">Dettaglio</li>
                        </ol>
                    </nav>
                    <h1><i class="bi bi-building me-2"></i><?php echo htmlspecialchars($fornitore['name']); ?></h1>
                    <p class="mb-0">P.IVA: <?php echo htmlspecialchars($fornitore['p_iva']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#editFornitoreModal">
                        <i class="bi bi-pencil me-2"></i>Modifica
                    </button>
                    <button class="btn btn-danger" onclick="eliminaFornitore()">
                        <i class="bi bi-trash me-2"></i>Elimina
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container my-5">
        <!-- Messaggi -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Informazioni Fornitore -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="bi bi-info-circle me-2"></i>Informazioni Fornitore</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Partita IVA</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($fornitore['p_iva']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Azienda</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($fornitore['name']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Indirizzo</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($fornitore['indirizzo']); ?></p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editFornitoreModal">
                                <i class="bi bi-pencil me-2"></i>Modifica Informazioni
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistiche -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="bi bi-graph-up me-2"></i>Statistiche</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <i class="bi bi-box-seam text-info" style="font-size: 2rem;"></i>
                                <h4 class="text-info mt-1"><?php echo count($prodotti_fornitore); ?></h4>
                                <small class="text-muted">Prodotti Forniti</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestione Prodotti -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-box-seam me-2"></i>Prodotti Forniti</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                            <i class="bi bi-plus-circle me-2"></i>Aggiungi Prodotto
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($prodotti_fornitore)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">Nessun prodotto associato</h5>
                                <p class="text-muted">Aggiungi prodotti a questo fornitore</p>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                                    <i class="bi bi-plus-circle me-2"></i>Aggiungi Primo Prodotto
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Prodotto</th>
                                            <th>Prezzo</th>
                                            <th>Quantità</th>
                                            <th>Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prodotti_fornitore as $prodotto): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($prodotto['nome']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        €<?php echo number_format($prodotto['prezzo'], 2); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $prodotto['quantita']; ?> pz
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" 
                                                                onclick="modificaProdotto(<?php echo $prodotto['id']; ?>, '<?php echo htmlspecialchars($prodotto['nome']); ?>', <?php echo $prodotto['prezzo']; ?>, <?php echo $prodotto['quantita']; ?>)"
                                                                title="Modifica">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="rimuoviProdotto(<?php echo $prodotto['id']; ?>, '<?php echo htmlspecialchars($prodotto['nome']); ?>')"
                                                                title="Rimuovi">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-box-seam me-2"></i>Ordini effettuati da questo fornitore</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($storicoOrdini)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">Nessun ordine presente</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Negozio</th>
                                            <th>Prodotto</th>
                                            <th>Prezzo</th>
                                            <th>Quantità acquistata</th>
                                            <th>Data di consegna prevista</th>
                                            <th>Manager richiedente </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storicoOrdini as $ordine): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($ordine['nome_negozio']); ?></strong>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($ordine['nome_prodotto']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        €<?php echo number_format($ordine['prezzo'], 2); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $ordine['quantita']; ?> pz
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        <?php echo htmlspecialchars($ordine['data_consegna']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($ordine['manager_richiedente']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Modifica Fornitore -->
    <div class="modal fade" id="editFornitoreModal" tabindex="-1" aria-labelledby="editFornitoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editFornitoreModalLabel">
                        <i class="bi bi-pencil me-2"></i>Modifica Fornitore
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?p_iva=<?php echo $p_iva; ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_fornitore">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">
                                <i class="bi bi-building me-1"></i>Nome Azienda *
                            </label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($fornitore['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="indirizzo" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i>Indirizzo *
                            </label>
                            <textarea class="form-control" id="indirizzo" name="indirizzo" 
                                      rows="3" required><?php echo htmlspecialchars($fornitore['indirizzo']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Salva Modifiche
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Aggiungi Prodotto -->
    <div class="modal fade" id="addProdottoModal" tabindex="-1" aria-labelledby="addProdottoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addProdottoModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Prodotto al Fornitore
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?p_iva=<?php echo $p_iva; ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_prodotto_fornitore">
                        
                        <div class="mb-3">
                            <label for="prodotto_id" class="form-label">
                                <i class="bi bi-box me-1"></i>Prodotto *
                            </label>
                            <select class="form-select" id="prodotto_id" name="prodotto_id" required>
                                <option value="">Seleziona un prodotto...</option>
                                <?php foreach ($prodotti_disponibili as $prodotto): ?>
                                    <option value="<?php echo $prodotto['id']; ?>">
                                        <?php echo htmlspecialchars($prodotto['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prezzo" class="form-label">
                                <i class="bi bi-currency-euro me-1"></i>Prezzo Unitario *
                            </label>
                            <input type="number" class="form-control" id="prezzo" name="prezzo" 
                                   min="0" step="0.01" required placeholder="0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantita" class="form-label">
                                <i class="bi bi-boxes me-1"></i>Quantità Disponibile *
                            </label>
                            <input type="number" class="form-control" id="quantita" name="quantita" 
                                   min="1" required placeholder="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Aggiungi Prodotto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modifica Prodotto -->
    <div class="modal fade" id="editProdottoModal" tabindex="-1" aria-labelledby="editProdottoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editProdottoModalLabel">
                        <i class="bi bi-pencil me-2"></i>Modifica Prodotto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?p_iva=<?php echo $p_iva; ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_prodotto_fornitore">
                        <input type="hidden" name="prodotto_id" id="edit_prodotto_id">
                        
                        <div class="mb-3">
                            <label for="edit_prodotto_nome" class="form-label">
                                <i class="bi bi-box me-1"></i>Prodotto
                            </label>
                            <input type="text" class="form-control" id="edit_prodotto_nome" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_prezzo" class="form-label">
                                <i class="bi bi-currency-euro me-1"></i>Prezzo Unitario *
                            </label>
                            <input type="number" class="form-control" id="edit_prezzo" name="prezzo" 
                                   min="0" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_quantita" class="form-label">
                                <i class="bi bi-boxes me-1"></i>Quantità Disponibile *
                            </label>
                            <input type="number" class="form-control" id="edit_quantita" name="quantita" 
                                   min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Aggiorna Prodotto
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
        function eliminaFornitore() {
            if (confirm('Sei sicuro di voler eliminare questo fornitore?\n\nATTENZIONE: Verranno eliminati anche tutti i prodotti associati!')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo $_SERVER['PHP_SELF']; ?>?p_iva=<?php echo $p_iva; ?>';
                
                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_fornitore';
                
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function modificaProdotto(id, nome, prezzo, quantita) {
            document.getElementById('edit_prodotto_id').value = id;
            document.getElementById('edit_prodotto_nome').value = nome;
            document.getElementById('edit_prezzo').value = prezzo;
            document.getElementById('edit_quantita').value = quantita;
            
            var modal = new bootstrap.Modal(document.getElementById('editProdottoModal'));
            modal.show();
        }

        function rimuoviProdotto(id, nome) {
            if (confirm('Sei sicuro di voler rimuovere "' + nome + '" da questo fornitore?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo $_SERVER['PHP_SELF']; ?>?p_iva=<?php echo $p_iva; ?>';
                
                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'remove_prodotto_fornitore';
                
                var prodottoInput = document.createElement('input');
                prodottoInput.type = 'hidden';
                prodottoInput.name = 'prodotto_id';
                prodottoInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(prodottoInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
