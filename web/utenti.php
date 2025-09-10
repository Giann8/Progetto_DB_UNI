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


if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $result = addUser();
    if(isset($result['success'])&&$result['success']){
        $success_message = "Utente aggiunto con successo.";
    } else if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $error_message = "Errore sconosciuto durante l'aggiunta dell'utente.";
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $result = deleteUser($_POST['codice_fiscale']);
    if(isset($result['success'])&&$result['success']){
        $success_message = "Utente eliminato con successo.";
    } else if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $error_message = "Errore sconosciuto durante l'eliminazione dell'utente.";
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $result = editUser();
    if(isset($result['success'])&&$result['success']){
        $success_message = "Utente modificato con successo.";
    } else if (isset($result['error'])) {
        $error_message = $result['error'];
    } else {
        $error_message = "Errore sconosciuto durante la modifica dell'utente.";
    }
}
        $clienti = getClienti();
        $manager = getManager();
        $utenti = array_merge($clienti, $manager);

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="static/favicon.ico">
    <title>Gestione Utenti - Sistema Gestione Negozi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include('navbar.php'); ?>

    <header class="bg-primary text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-people me-2"></i>Gestione Utenti</h1>
                    <p class="mb-0">Amministrazione utenti del sistema</p>
                </div>
                <div>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus me-2"></i>Aggiungi Utente
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-5">
        <!-- Alerts -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <!-- Statistiche Rapide -->
        <div class="row mb-4">
            <div class="col-md">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Totale Utenti</h5>
                        <h3 class="text-primary"><?php echo count($utenti); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella Utenti -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-table me-2"></i>Lista Utenti</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportUsers()">
                        <i class="bi bi-download me-2"></i>Esporta
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshTable()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Aggiorna
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="usersTable">
                        <thead class="table-dark">
                            <tr>

                                <th>Codice Fiscale</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($utenti)): ?>
                                <?php foreach ($utenti as $utente): ?>
                                    <tr data-user-id="<?php echo htmlspecialchars($utente['c_f']); ?>" <?php if ($_SESSION['user_id'] === $utente['c_f']): ?>class="d-none" <?php endif; ?>>

                                        <td>
                                            <code><?php echo htmlspecialchars($utente['c_f']); ?></code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    <?php echo strtoupper(substr($utente['name'], 0, 1)); ?>
                                                </div>
                                                <strong><?php echo htmlspecialchars($utente['name']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($utente['email']); ?></td>
                                        <td>
                                            <?php if (in_array($utente['c_f'], array_column($manager, 'c_f'))): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-person-gear me-1"></i>Manager
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-person-check me-1"></i>Cliente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Attivo
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-warning"
                                                    data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                    onclick="populateEditModal('<?php echo $utente['c_f']; ?>', '<?php echo htmlspecialchars($utente['name']); ?>', '<?php echo htmlspecialchars($utente['email']); ?>', '<?php echo in_array($utente['c_f'], array_column($manager, 'c_f')) ? 'Manager' : 'Cliente'; ?>')"
                                                    title="Modifica">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="codice_fiscale" value="<?php echo $utente['c_f']; ?>">
                                                    <input type="hidden" name="user_type" value="<?php echo in_array($utente['c_f'], array_column($manager, 'c_f')) ? 'manager' : 'cliente'; ?>">
                                                    <button type="submit" class="btn btn-outline-danger"
                                                        title="Elimina">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people" style="font-size: 3rem;"></i>
                                            <p class="mt-2">Nessun utente trovato</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2025 Progetto Database Universitario</p>
        </div>
    </footer>

    <!-- Modal Aggiungi Utente -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Aggiungi Nuovo Utente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label for="userCF" class="form-label">Codice Fiscale</label>
                            <input type="text" class="form-control" id="userCF" name="codice_fiscale" required
                                placeholder="RSSMRA80A01H501X">
                        </div>
                        <div class="mb-3">
                            <label for="userName" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="userName" name="name" required
                                placeholder="Mario Rossi">
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required
                                placeholder="mario.rossi@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="userType" class="form-label">Tipo Utente</label>
                            <select class="form-select" id="userType" name="user_type" required>
                                <option value="">Seleziona tipo...</option>
                                <option value="cliente">Cliente</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="userPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="userPassword" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Crea Utente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modifica Utente -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="bi bi-pencil me-2"></i>Modifica Utente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="codice_fiscale" id="editUserCF">
                        <input type="hidden" name="user_type" id="editUserType">

                        <div class="mb-3">
                            <label class="form-label"><strong>Codice Fiscale</strong></label>
                            <p class="form-control-plaintext" id="editUserCFDisplay"></p>
                        </div>

                        <div class="mb-3">
                            <label for="editUserName" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="editUserName" name="name" required
                                placeholder="Mario Rossi">
                        </div>

                        <div class="mb-3">
                            <label for="editUserEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editUserEmail" name="email" required
                                placeholder="mario.rossi@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Tipo Utente</strong></label>
                            <p class="form-control-plaintext" id="editUserTypeDisplay"></p>
                            <small class="text-muted">Il tipo utente non può essere modificato</small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshTable() {
            location.reload();
        }

        function populateEditModal(codiceFiscale, nome, email, tipo) {
            // Popola i campi del modal con i dati dell'utente
            document.getElementById('editUserCF').value = codiceFiscale;
            document.getElementById('editUserCFDisplay').textContent = codiceFiscale;
            document.getElementById('editUserName').value = nome;
            document.getElementById('editUserEmail').value = email;
            document.getElementById('editUserTypeDisplay').textContent = tipo;
            
            // Imposta il tipo di utente nel campo hidden (convertendo da "Manager"/"Cliente" a "manager"/"cliente")
            document.getElementById('editUserType').value = tipo.toLowerCase();
        }
    </script>

    <style>
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #007bff;
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