<?php
declare(strict_types=1);
require "config.php";

$error = '';

if (isset($_GET['x']) && $_GET['x'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = pdo_query('SELECT * FROM users WHERE login = ?', [$login]);
    $user = $stmt->fetch();
    if ($user) {
        if (password_verify($password, $user->pass)) {
            $_SESSION['login'] = $user->login;
            header('Location: index.php');
            exit;
        }
        // Legacy MD5 support
        if ($user->pass === md5($password)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            pdo_query('UPDATE users SET pass = ? WHERE login = ?', [$newHash, $user->login]);
            $_SESSION['login'] = $user->login;
            header('Location: index.php');
            exit;
        }
    }
    $error = 'Verkeerde gebruikersnaam of wachtwoord.';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Vendetta Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-5">
    <h1 class="mb-4">Inloggen</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mb-3">
        <div class="mb-3">
            <label for="login" class="form-label">Gebruikersnaam</label>
            <input type="text" id="login" name="login" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Wachtwoord</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p><a href="register.php">Registreer</a> | <a href="login.php?x=logout">Uitloggen</a></p>
</body>
</html>
