<?php
session_start();
include_once('lib/functions.php');
include_once('lib/manager_functions.php');

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

if (!isLoggedIn() || !isManager()) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'add_prodotto') {
    $result = addProdotto($_POST['name'], $_POST['description']);
    if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $success_message = "Prodotto aggiunto con successo!";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'edit_prodotto') {
    $result = editProdotto($_POST['id'], $_POST['name'], $_POST['description']);
    if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $success_message = "Prodotto modificato con successo!";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'delete_prodotto') {
    $result = deleteProdotto($_POST['id']);
    if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $success_message = "Prodotto eliminato con successo!";
    }
}

$prodotti = getProdotti();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="static/favicon.ico">
    <title>Gestione Prodotti - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    
    <header class="bg-success text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-box me-2"></i>Gestione Prodotti</h1>
                    <p class="mb-0">Amministrazione catalogo prodotti</p>
                </div>
                <div>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Prodotto
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
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Totale Prodotti</h5>
                        <h3 class="text-success"><?php echo count($prodotti); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella Prodotti -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-table me-2"></i>Lista Prodotti</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshTable()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Aggiorna
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($prodotti)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
                        <h3 class="mt-3">Nessun prodotto trovato</h3>
                        <p class="text-muted">Inizia aggiungendo il tuo primo prodotto al catalogo.</p>
                        <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addProdottoModal">
                            <i class="bi bi-plus-circle me-2"></i>Aggiungi il primo prodotto
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="prodottiTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Prodotto</th>
                                    <th>Descrizione</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prodotti as $prodotto): ?>
                                    <tr data-prodotto-id="<?php echo htmlspecialchars($prodotto['id']); ?>">
                                        <td>
                                            <span class="badge bg-secondary">#<?php echo htmlspecialchars($prodotto['id']); ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2 bg-success">
                                                    <i class="bi bi-box"></i>
                                                </div>
                                                <strong><?php echo htmlspecialchars($prodotto['name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-muted" style="max-width: 300px;">
                                                <?php 
                                                $desc = htmlspecialchars($prodotto['description'] ?? 'Nessuna descrizione');
                                                echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                                ?>
                                            </p>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-warning"
                                                        data-bs-toggle="modal" data-bs-target="#editProdottoModal"
                                                        onclick="populateEditModal('<?php echo $prodotto['id']; ?>', '<?php echo htmlspecialchars($prodotto['name']); ?>', '<?php echo htmlspecialchars($prodotto['description']); ?>')"
                                                        title="Modifica">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteProdotto(<?php echo $prodotto['id']; ?>)"
                                                        title="Elimina">
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
    </main>

    <!-- Modal Aggiungi Prodotto -->
    <div class="modal fade" id="addProdottoModal" tabindex="-1" aria-labelledby="addProdottoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProdottoModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Aggiungi Nuovo Prodotto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_prodotto">
                        <div class="mb-3">
                            <label for="prodottoName" class="form-label">Nome Prodotto</label>
                            <input type="text" class="form-control" id="prodottoName" name="name" required 
                                   placeholder="Es: Smartphone Samsung Galaxy">
                        </div>
                        <div class="mb-3">
                            <label for="prodottoDescription" class="form-label">Descrizione</label>
                            <textarea class="form-control" id="prodottoDescription" name="description" rows="4" 
                                      placeholder="Descrizione dettagliata del prodotto..."></textarea>
                            <div class="form-text">Descrizione opzionale del prodotto</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Crea Prodotto
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
                <div class="modal-header">
                    <h5 class="modal-title" id="editProdottoModalLabel">
                        <i class="bi bi-pencil me-2"></i>Modifica Prodotto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_prodotto">
                        <input type="hidden" name="id" id="editProdottoId">
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>ID Prodotto</strong></label>
                            <p class="form-control-plaintext" id="editProdottoIdDisplay"></p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editProdottoName" class="form-label">Nome Prodotto</label>
                            <input type="text" class="form-control" id="editProdottoName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editProdottoDescription" class="form-label">Descrizione</label>
                            <textarea class="form-control" id="editProdottoDescription" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Salva Modifiche
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2023 Progetto DB - Gestione Prodotti</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funzioni per azioni prodotti
        function populateEditModal(id, nome, descrizione) {
            document.getElementById('editProdottoId').value = id;
            document.getElementById('editProdottoIdDisplay').textContent = '#' + id;
            document.getElementById('editProdottoName').value = nome;
            document.getElementById('editProdottoDescription').value = descrizione;
        }

        function deleteProdotto(prodottoId) {
            if (confirm('Sei sicuro di voler eliminare questo prodotto?')) {
                // Crea form per eliminazione
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo $_SERVER['PHP_SELF']; ?>';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_prodotto';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = prodottoId;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function refreshTable() {
            location.reload();
        }
    </script>

    <style>
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }
    </style>
</body>

</html>
