<?php
session_start();
include_once('lib/functions.php');
include_once('lib/manager_functions.php');

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

// Recupera l'ID del negozio dall'URL
$negozio_id = $_GET['negozio_id'] ?? null;

if (!$negozio_id) {
    header("Location: negozi.php");
    exit();
}
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'delete_prodotto':
            $prodotto_id = $_POST['id'] ?? null;
            if ($prodotto_id) {
                $result = deleteProdotto($prodotto_id);
                if ($result['success']) {
                    $success_message = "Prodotto rimosso con successo.";

                    $prodotti_negozio = getProdottiNegozio($negozio_id);
                } else {
                    $error_message = "Errore durante la rimozione del prodotto.";
                }
            } else {
                $error_message = "ID prodotto non valido.";
            }
            break;

        case 'modifica_prezzo_prodotto':
            echo 'modifica_prezzo';
            $prodotto_id = $_POST['prodotto_id'] ?? null;
            $nuovo_prezzo = $_POST['nuovo_prezzo'] ?? null;
            if ($prodotto_id && is_numeric($nuovo_prezzo) && $nuovo_prezzo >= 0) {
                $result = modificaPrezzoProdottoNegozio($negozio_id, $prodotto_id, $nuovo_prezzo);
                if ($result['success']) {
                    $success_message = "Prezzo aggiornato con successo.";

                    $prodotti_negozio = getProdottiNegozio($negozio_id);
                } else {
                    $error_message = "Errore durante l'aggiornamento del prezzo.";
                }
            } else {
                $error_message = "Dati non validi per l'aggiornamento del prezzo.";
            }
            break;

        case 'modifica_info_negozio':
            $nome_negozio = $_POST['nome_negozio'] ?? null;
            $indirizzo = $_POST['address'] ?? null;
            $responsabile = $_POST['responsabile'] ?? null;

            if ($nome_negozio && $indirizzo && $responsabile) {
                $result = modificaInformazioniNegozio($negozio_id, $nome_negozio, $indirizzo, $responsabile);
                if ($result['success']) {
                    $success_message = "Informazioni negozio aggiornate con successo.";
                } else {
                    $error_message = "Errore durante l'aggiornamento delle informazioni del negozio.";
                }
            } else {
                $error_message = "Dati non validi per l'aggiornamento delle informazioni del negozio.";
            }
            break;
        case 'modifica_orari_negozio':
            $orario_apertura = $_POST['orario_apertura'] ?? null;
            $orario_chiusura = $_POST['orario_chiusura'] ?? null;
            if ($orario_apertura && $orario_chiusura) {
                $result = modificaOrariNegozio($negozio_id, $orario_apertura, $orario_chiusura);
                if ($result['success']) {
                    $success_message = "Orari aggiornati con successo.";
                } else {
                    $error_message = "Errore durante l'aggiornamento degli orari.";
                }
            } else {
                $error_message = "Dati non validi per l'aggiornamento degli orari.";
            }
            break;
        case 'ordina_prodotto':
            $prodotto_id = $_POST['prodotto_id'] ?? null;
            $quantita = $_POST['quantita'] ?? null;
            if ($prodotto_id && is_numeric($quantita) && $quantita > 0) {
                $result = ordinaProdottoNegozio($prodotto_id, $negozio_id, $quantita);
                if (isset($result['success']) && $result['success']) {
                    $success_message = "Prodotto ordinato con successo.";

                    $prodotti_negozio = getProdottiNegozio($negozio_id);
                } else {
                    $error_message = "Errore durante l'ordinazione del prodotto, il prodotto non è disponibile in questa quantita o si è verificato un problema.";
                }
            } else {
                $error_message = "Dati non validi per l'ordinazione del prodotto.";
            }
            break;
        case 'elimina_negozio':
            $result = eliminaNegozio($negozio_id);
            if (isset($result['success']) && $result['success']) {
                $success_message = "Negozio eliminato con successo.";
                header("Location: negozi.php");
                exit();
            } else {
                $error_message = "Errore durante l'eliminazione del negozio.";
            }
        default:
            break;
    }
}

// Recupera i dati del negozio
$negozio = getNegozio($negozio_id);

$tessere_rilasciate = getTessereNegozio($negozio_id);
if (!$negozio) {
    header("Location: negozi.php");
    exit();
}

// Recupera prodotti del negozio (se esiste la funzione)
$prodotti_negozio = getProdottiNegozio($negozio_id);
if ($prodotti_negozio === false) {
    $prodotti_negozio = [];
}

// Ottiene la lista dei prodotti esistenti
$prodotti = getProdotti();

// Calcola statistiche
$ora_corrente = date('H:i:s');
$is_aperto = ($ora_corrente >= $negozio['orario_apertura'] && $ora_corrente <= $negozio['orario_chiusura']);




$pagelink = $_SERVER['PHP_SELF'];
if ($negozio_id) {
    $pagelink .= '?negozio_id=' . urlencode($negozio_id);
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="static/favicon.ico">
    <title>Gestione Negozio - <?php echo htmlspecialchars($negozio['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include('navbar.php'); ?>

    <header class="bg-info text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb text-white-50 mb-2">
                            <li class="breadcrumb-item">
                                <a href="negozi.php" class="text-white-75">
                                    <i class="bi bi-shop me-1"></i>Negozi
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white">Gestione</li>
                        </ol>
                    </nav>
                    <h1><i class="bi bi-gear me-2"></i><?php echo htmlspecialchars($negozio['name']); ?></h1>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?php echo htmlspecialchars($negozio['address'] ?? 'Indirizzo non specificato'); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="negozi.php" class="btn btn-light me-2">
                        <i class="bi bi-arrow-left me-2"></i>Torna ai Negozi
                    </a>
                    <button class="btn btn-danger" onclick="eliminaNegozio()" title="Elimina Negozio">
                        <i class="bi bi-trash me-2"></i>Elimina
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-5">
        <!-- Messaggi di feedback -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiche Rapide -->

        <div class="row my-3">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-clock text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Stato Attuale</h5>
                        <?php if ($is_aperto): ?>
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-circle-fill me-1"></i>Aperto
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-x-circle-fill me-1"></i>Chiuso
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-box text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Prodotti</h5>
                        <h3 class="text-success"><?php echo count($prodotti_negozio); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-person-badge text-warning" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Responsabile</h5>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($negozio['responsabile'] ?? 'N/A'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informazioni Negozio -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Informazioni Generali</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $pagelink; ?>">
                            <input type="hidden" name="action" value="modifica_info_negozio">

                            <div class="mb-3">
                                <label for="negozioName" class="form-label">Nome Negozio</label>
                                <input type="text" class="form-control" id="negozioName" name="nome_negozio"
                                    value="<?php echo htmlspecialchars($negozio['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="negozioAddress" class="form-label">Indirizzo</label>
                                <input type="text" class="form-control" id="negozioAddress" name="address"
                                    value="<?php echo htmlspecialchars($negozio['address'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="negozioResponsabile" class="form-label">Responsabile</label>
                                <input type="text" class="form-control" id="negozioResponsabile" name="responsabile"
                                    value="<?php echo htmlspecialchars($negozio['responsabile'] ?? ''); ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Aggiorna Informazioni
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Gestione Orari -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-clock me-2"></i>Gestione Orari</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Ora corrente:</strong> <?php echo date('H:i:s'); ?>
                        </div>

                        <form method="POST" action="<?php echo $pagelink; ?>">
                            <input type="hidden" name="action" value="modifica_orari_negozio">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="orarioApertura" class="form-label">Orario Apertura</label>
                                        <input type="time" class="form-control" id="orarioApertura" name="orario_apertura"
                                            value="<?php echo htmlspecialchars($negozio['orario_apertura']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="orarioChiusura" class="form-label">Orario Chiusura</label>
                                        <input type="time" class="form-control" id="orarioChiusura" name="orario_chiusura"
                                            value="<?php echo htmlspecialchars($negozio['orario_chiusura']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-clock me-2"></i>Aggiorna Orari
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestione Prodotti -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-box-seam me-2"></i>Prodotti del Negozio</h5>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                    <i class="bi bi-plus-circle me-2"></i>Ordina Prodotto
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($prodotti_negozio)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-box" style="font-size: 3rem; color: #ccc;"></i>
                        <h4 class="mt-3">Nessun prodotto</h4>
                        <p class="text-muted">Questo negozio non ha ancora prodotti associati.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                            <i class="bi bi-plus-circle me-2"></i>Aggiungi il primo prodotto
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Prodotto</th>
                                    <th>Prezzo</th>
                                    <th>Disponibilità</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prodotti_negozio as $prodotto): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prodotto['nome'] ?? 'N/A'); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($prodotto['descrizione'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                €<?php echo number_format($prodotto['prezzo'] ?? 0, 2); ?>
                                            </span>
                                            <form action="<?php echo $pagelink; ?>" method="POST">
                                                <input type="hidden" name="action" value="modifica_prezzo_prodotto">
                                                <input type="hidden" name="prodotto_id" value="<?php echo htmlspecialchars($prodotto['id']); ?>">
                                                <div class="input-group">
                                                    <input type="number" name="nuovo_prezzo" class="form-control" step="0.01" min="0" placeholder="Nuovo Prezzo" required>
                                                    <button type="submit" class="btn btn-outline-primary">Aggiorna</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if (($prodotto['disponibilita']  > 0)): ?>
                                                <span class="badge bg-success">
                                                    <?php echo $prodotto['disponibilita']; ?> pz
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Esaurito</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form action="<?php echo $pagelink; ?>" method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_prodotto">
                                                    <input type="hidden" name="id" value="<?php echo $prodotto['id']; ?>">
                                                    <button class="btn btn-outline-danger" title="Rimuovi" type="submit">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
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

        <!-- Spazio tra le tabelle -->
        <div class="my-4"></div>

        <!-- Tabella tessere rilasciate -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-postcard me-2"></i>Tessere rilasciate</h5>
                <span class="badge bg-info">
                    <?php echo count($tessere_rilasciate); ?> tessere
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($tessere_rilasciate)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-postcard text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Nessuna tessera rilasciata da questo negozio</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Tessera</th>
                                    <th>Cliente</th>
                                    <th>Punti</th>
                                    <th>Data Rilascio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tessere_rilasciate as $tessera): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">#<?php echo $tessera['tessera_id']; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($tessera['cliente_c_f']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $tessera['punti'] >= 300 ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $tessera['punti']; ?> punti
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($tessera['data_rilascio'])); ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal Ordina Prodotto -->
    <div class="modal fade" id="addProdottoModal" tabindex="-1" aria-labelledby="addProdottoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProdottoModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Prodotto al Negozio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?negozio_id=<?php echo $negozio_id; ?>" method="post">
                        <input type="hidden" name="action" value="ordina_prodotto">

                        <div class="mb-3">
                            <label for="prodotto_select" class="form-label">Seleziona Prodotto</label>
                            <select class="form-select" name="prodotto_id" id="prodotto_select" required>
                                <option value="">-- Scegli un prodotto --</option>
                                <?php foreach ($prodotti as $prodotto): ?>
                                    <option value="<?php echo $prodotto['id']; ?>">
                                        <?php echo htmlspecialchars($prodotto['name']); ?>
                                        (<?php echo htmlspecialchars($prodotto['description']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="quantita" class="form-label">Quantità</label>
                            <input type="number" class="form-control" name="quantita" id="quantita"
                                min="1" value="1" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cart-plus me-2"></i>Ordina Prodotto
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2025 Progetto Database Universitario</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Conferma prima di modificare orari
        document.querySelector('form[action*="update_orari"]').addEventListener('submit', function(e) {
            if (!confirm('Sei sicuro di voler modificare gli orari del negozio?')) {
                e.preventDefault();
            }
        });

        // Funzione per eliminare il negozio
        function eliminaNegozio() {
            if (confirm('⚠️ ATTENZIONE!\n\nSei sicuro di voler eliminare questo negozio?\n\nQuesta azione:\n• Eliminerà definitivamente il negozio\n• Rimuoverà tutti i prodotti associati\n• Cancellerà tutte le tessere fedeltà rilasciate\n• NON può essere annullata\n\nConfermi l\'eliminazione?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo $_SERVER['PHP_SELF']; ?>?negozio_id=<?php echo $negozio_id; ?>';
                
                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'elimina_negozio';
                
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>