<?php
session_start();
include_once('lib/functions.php');
include_once('lib/bill_functions.php');

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    logout();
    exit();
}

if (!isLoggedIn() || !isCliente()) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error_message = "Le password non corrispondono.";
    } else if (changeCustomerPassword($_SESSION['user_id'], $_POST['new_password'], $_POST['current_password'])) {
        $success_message = "Password modificata con successo.";
    } else {
        $error_message = "Errore nella modifica della password.";
    }
}
$bills = getUserBills($_SESSION['user_id']);

$_SESSION['tessera'] = getTesseraFedelta($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="static/favicon.ico">
    <title>Dashboard Cliente</title>
    <link rel="stylesheet" href="static/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    <header class="bg-primary text-white py-4">
        <div class="container">
            <h1>Benvenuto nella tua Dashboard, <?php echo $_SESSION['user_name']; ?>!</h1>
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
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2><i class="bi bi-person-circle me-2"></i>Le tue informazioni</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                        <p><strong>Tipo utente:</strong> <?php echo $_SESSION['user_type']; ?></p>
                        <p><strong>ID Utente:</strong> <?php echo $_SESSION['user_id']; ?></p>
                        <p><strong>Nome:</strong> <?php echo $_SESSION['user_name']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="bi bi-credit-card me-2"></i>Tessera Fedeltà</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($_SESSION['tessera']): ?>
                            <p><strong>ID Tessera:</strong> <?php echo $_SESSION['tessera']['id']; ?></p>
                            <p><strong>Data Rilascio:</strong> <?php echo $_SESSION['tessera']['data_rilascio']; ?></p>
                            <p><strong>Saldo punti:</strong>
                                <span class="badge bg-success fs-6"><?php echo $_SESSION['tessera']['punti']; ?> punti</span>
                            </p>
                        <?php else: ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Non possiedi una tessera fedeltà.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="bi bi-receipt me-2"></i>Le tue fatture</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($bills)): ?>
                    <div class="accordion" id="accordionFatture">
                        <?php foreach ($bills as $index => $bill): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false"
                                        aria-controls="collapse<?php echo $index; ?>">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <span><strong>Fattura #<?php echo $bill['id']; ?></strong></span>
                                            <span class="text-muted">€<?php echo number_format($bill['totale'], 2); ?> - <?php echo $bill['data_emissione']; ?></span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse"
                                    aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#accordionFatture">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Prodotto</th>
                                                        <th>Negozio</th>
                                                        <th>Quantità</th>
                                                        <th>Subtotale</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (getBillsArticles($bill['id']) as $articolo): ?>
                                                        <tr>
                                                            <td><?php echo $articolo['prodotto']; ?></td>
                                                            <td><?php echo $articolo['negozio']; ?></td>
                                                            <td><?php echo $articolo['quantita']; ?></td>
                                                            <td>€<?php echo number_format($articolo['subtotale'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <?php if ($bill['sconto'] > 0): ?>
                                                    <span class="badge bg-success">Sconto: <?php echo $bill['sconto']; ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="mb-0"><strong>Totale: €<?php echo number_format($bill['totale'], 2); ?></strong></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        Non hai ancora fatture.
                    </div>
                <?php endif; ?>
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
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2023 Progetto DB - Dashboard Cliente</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>