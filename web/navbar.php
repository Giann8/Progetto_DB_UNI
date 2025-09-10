<?php
// Verifica se l'utente è loggato
include_once('lib/functions.php');

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="bi bi-shop me-2"></i>Sistema Gestione Negozi
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isLoggedIn() && isCliente()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_cliente.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                <?php elseif (isLoggedIn() && isManager()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_manager.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="negozi.php">
                        <i class="bi bi-shop me-1"></i>Negozi
                    </a>
                </li>
                <?php if (isLoggedIn() && isCliente()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="prodotti.php">
                            <i class="bi bi-bag me-1"></i>Prodotti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrello.php">
                            <i class="bi bi-cart me-1"></i>Carrello
                            <?php 
                            $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantita')) : 0;
                            if ($cart_count > 0): 
                            ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php elseif (isLoggedIn() && isManager()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="gestore_prodotti.php">
                            <i class="bi bi-box me-1"></i>Gestione Prodotti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="utenti.php">
                            <i class="bi bi-people me-1"></i>Gestione Utenti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fornitori.php">
                            <i class="bi bi-truck me-1"></i>Fornitori
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utente'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <h6 class="dropdown-header">
                                <i class="bi bi-info-circle me-2"></i>Informazioni Account
                            </h6>
                        </li>
                        <li><span class="dropdown-item-text"><small>Email: <?php echo $_SESSION['email'] ?? 'N/A'; ?></small></span></li>
                        <li><span class="dropdown-item-text"><small>Tipo: <?php echo $_SESSION['user_type'] ?? 'N/A'; ?></small></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (isLoggedIn() && isCliente()): ?>
                            <li>
                                <a class="dropdown-item" href="dashboard_cliente.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                        <?php elseif (isLoggedIn() && isManager()): ?>
                            <li>
                                <a class="dropdown-item" href="dashboard_manager.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="d-inline">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>