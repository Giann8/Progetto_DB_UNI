<?php
$pagelink = $_SERVER['PHP_SELF'];

session_start();
include_once('lib/functions.php');


if(isset($_POST['email']) && isset($_POST['password'])){
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error = 'Per favore, compila tutti i campi.';
    }else{
       $_SESSION['logged_in'] = login($_POST['email'], $_POST['password'], $_POST['user_type']);
       if(!isLoggedIn()){
        $error = 'Credenziali non valide. Riprova.';
       }
    }
}
if (isLoggedIn()) {
    header('Location: dashboard_'.$_SESSION['user_type'].'.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Gestione Negozi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <div class="container-fluid login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-4 col-lg-3">
                <div class="card login-card border-0">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <i class="bi bi-shop display-4 text-primary mb-3"></i>
                            <h3 class="fw-bold text-dark">Accedi</h3>
                            <p class="text-muted">Sistema Gestione Negozi</p>
                        </div>

                        <!-- Alert per errori -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form di Login -->
                        <form method="POST" action="<?php echo $pagelink; ?>">
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-person me-2"></i>Email
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg"
                                    id="email"
                                    name="email"
                                    placeholder="Inserisci email o codice fiscale"
                                    >
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control form-control-lg"
                                        id="password"
                                        name="password"
                                        placeholder="Inserisci la password"
                                        >
                                    <button class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tipo di utente -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-2"></i>Tipo di accesso
                                </label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="radio"
                                                name="user_type"
                                                id="cliente"
                                                value="cliente"
                                                checked>
                                            <label class="form-check-label" for="cliente">
                                                <i class="bi bi-person me-1"></i>Cliente
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="radio"
                                                name="user_type"
                                                id="manager"
                                                value="manager">
                                            <label class="form-check-label" for="manager">
                                                <i class="bi bi-briefcase me-1"></i>Manager
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Accedi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script per mostrare/nascondere password -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });
    </script>
</body>

</html>