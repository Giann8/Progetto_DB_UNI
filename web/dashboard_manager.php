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

if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error_message = "Le password non corrispondono.";
    } else if (changeManagerPassword($_SESSION['user_id'], $_POST['new_password'], $_POST['current_password'])) {
        $success_message = "Password modificata con successo.";
    } else {
        $error_message = "Errore nella modifica della password.";
    }
}
$tessere = getTessereMaggioriPunti();
$storicoTessere = getStoricoTessere();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="static/favicon.ico">
    <title>Dashboard Manager</title>
    <link rel="stylesheet" href="static/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    <header class="bg-primary text-white py-4">
        <div class="container">
            <h1>Benvenuto nella Dashboard Manager, <?php echo $_SESSION['user_name']; ?>!</h1>
        </div>
    </header>
    <main class="container my-5">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2><i class="bi bi-person-circle me-2"></i>Le tue informazioni</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                                <p><strong>Tipo utente:</strong> <?php echo $_SESSION['user_type']; ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>ID Utente:</strong> <?php echo $_SESSION['user_id']; ?></p>
                                <p><strong>Nome:</strong> <?php echo $_SESSION['user_name']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="bi bi-key me-2"></i>Modifica password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Attuale</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nuova Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Conferma Nuova Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Cambia Password
                            </button>
                        </form>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="bi bi-tools me-2"></i>Azioni Manager</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="negozi.php" class="btn btn-outline-primary">
                                <i class="bi bi-shop me-2"></i>Gestione Negozi
                            </a>
                            <a href="prodotti.php" class="btn btn-outline-success">
                                <i class="bi bi-box me-2"></i>Gestione Prodotti
                            </a>
                            <a href="utenti.php" class="btn btn-outline-warning">
                                <i class="bi bi-people me-2"></i>Gestione Utenti
                            </a>
                        </div>
                    </div>
                </div>
                <div class="my-3"></div>
                <div class="card">
                    <div class="card-header">
                        <h4> Tessere con maggiori_punti </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tessere)): ?>
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
                                        <?php foreach ($tessere as $tessera): ?>
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

                <div class="card mt-3">
                    <div class="card-header">
                        <h4><i class="bi bi-info-circle me-2"></i>Informazioni Sistema</h4>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            Dashboard Manager v1.0<br>
                            Ultimo accesso: <?php echo date('d/m/Y H:i'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Storico Tessere a tutta larghezza -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-archive me-2"></i>Storico Tessere - Negozi Eliminati</h4>
                        <small class="text-muted">Tessere fedeltà di negozi che sono stati rimossi dal sistema</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($storicoTessere)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Nessuna tessera archiviata</h5>
                                <p class="text-muted">Non ci sono tessere di negozi eliminati nel sistema</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Tessera</th>
                                            <th>Cliente</th>
                                            <th>Negozio Eliminato</th>
                                            <th>Punti</th>
                                            <th>Data Rilascio</th>
                                            <th>Data Eliminazione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storicoTessere as $tessera): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">#<?php echo $tessera['tessera_id_originale']; ?></span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($tessera['cliente_nome'] ?? 'N/A'); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($tessera['cliente']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($tessera['negozio_nome']); ?></strong>
                                                        <br><small class="text-muted">ID: <?php echo $tessera['negozio_id']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $tessera['punti'] >= 300 ? 'bg-warning' : ($tessera['punti'] >= 100 ? 'bg-info' : 'bg-light text-dark'); ?>">
                                                        <?php echo $tessera['punti']; ?> punti
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($tessera['data_rilascio'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-danger">
                                                        <i class="bi bi-trash me-1"></i>
                                                        <?php echo date('d/m/Y', strtotime($tessera['data_eliminazione_negozio'])); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Statistiche riassuntive -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <h6 class="mb-1">Tessere Archiviate</h6>
                                        <strong class="text-primary"><?php echo count($storicoTessere); ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <h6 class="mb-1">Punti Totali Persi</h6>
                                        <strong class="text-warning"><?php echo array_sum(array_column($storicoTessere, 'punti')); ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <h6 class="mb-1">Negozi Eliminati</h6>
                                        <strong class="text-danger"><?php echo count(array_unique(array_column($storicoTessere, 'negozio_id'))); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2023 Progetto DB - Dashboard Manager</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>