<?php
session_start();
require_once __DIR__ . '/../config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        } else {
            $stmt = db()->prepare("SELECT id, username, password FROM users WHERE username = ? AND password = ? LIMIT 1");
            $stmt->execute([$username, $password]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($u) {
                // Prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['username'] = $u['username'];
                session_write_close();
                header('Location: index.php');
                exit;
            }

            $error = 'Invalid username or password.';
        }
    } catch (Exception $e) {
        $error = 'Login error. Please try again.';
        error_log('Login error: ' . $e->getMessage());
    }
}
?>
<!doctype html><html><head><title>Login</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div class="login">
<h2>Anvica NMS Login</h2>
<form method="post">
<input name="username" placeholder="Username" required>
<input name="password" type="password" placeholder="Password" required>
<button>Login</button>
<p class="err"><?=$error?></p>
</form></div></body></html>
