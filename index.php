<?php
session_start();
@
require_once __DIR__ . '/lib/db.php';

$errors = [];
$success = '';

if (isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	header('Location: index.php');
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
		if ($user) {
			if (hash('sha256', $password) === $user['password']) {
				$_SESSION['user'] = $email;
				$success = 'Inicio de sesión exitoso.';
			} else {
				$errors[] = 'Correo o contraseña incorrectos.';
			}
		} else {
			$errors[] = 'No se encontró usuario con ese correo.';
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
	<link rel="stylesheet" href="index.css"><!-- sigue cargándose por si se añaden overrides -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>
	<div class="container py-4">
		<h1 class="mb-4">Iniciar sesión</h1>

		<?php if (!empty($errors)): ?>
			<div class="alert alert-danger" role="alert">
				<ul class="m-0 ps-3">
					<?php foreach ($errors as $e): ?>
						<li><?php echo htmlspecialchars($e); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ($success): ?>
			<div class="alert alert-success" role="alert">
				<?php echo htmlspecialchars($success); ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($_SESSION['user'])): ?>
			<div class="card p-3 mb-3">
				<p class="mb-2">Conectado como: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
				<a class="btn btn-outline-secondary btn-sm" href="index.php?logout=1">Cerrar sesión</a>
			</div>
		<?php else: ?>
			<form class="card p-4 shadow-sm" method="post" id="loginForm" novalidate>
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
		<?php endif; ?>
	</div>
</body>
</html>