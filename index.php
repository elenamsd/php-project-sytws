<?php
session_start();
require_once __DIR__ . '/lib/db.php';

$errors = [];
$success = '';

if (isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	header('Location: index.php');
	exit;
}

if (!empty($_SESSION['user'])) {
	header('Location: news.php');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Correo electrónico no válido.';
	}

	if (empty($errors)) {
		$user = get_user_by($email);
		if ($user && hash('sha256', $password) === $user['password']) {
			$_SESSION['user'] = $email;
			header('Location: news.php');
			exit;
		} else {
			$errors[] = 'Correo o contraseña incorrectos.';
		}
	}
}

?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Login</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
	<h1 class="mb-4">Iniciar sesión</h1>
	<?php if ($errors): ?>
		<div class="alert alert-danger">
			<ul class="m-0 ps-3">
				<?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<form class="card p-4 shadow-sm" method="post" novalidate>
		<div class="mb-3">
			<label for="email" class="form-label">Correo electrónico</label>
			<input class="form-control" id="email" name="email" type="email" required>
		</div>
		<div class="mb-3">
			<label for="password" class="form-label">Contraseña</label>
			<input class="form-control" id="password" name="password" type="password" required>
		</div>
		<button class="btn btn-primary w-100" type="submit">Entrar</button>
	</form>
</div>
</body>
</html>