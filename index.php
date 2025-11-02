<?php
session_start();
@
require_once __DIR__ . '/lib/auth.php';

$users = [
	'user@example.com' => password_hash('Password1!', PASSWORD_DEFAULT)
];

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

	if (!isValidPassword($password)) {
		$errors[] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un signo de puntuación.';
	}

	if (empty($errors)) {
		if (isset($users[$email]) && password_verify($password, $users[$email])) {
			$_SESSION['user'] = $email;
			$success = 'Inicio de sesión exitoso.';
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
	<style>
		body { font-family: Arial, sans-serif; max-width:600px; margin:2rem auto; padding:1rem; }
		.form { border:1px solid #ddd; padding:1rem; border-radius:6px; }
		.input { display:block; width:100%; padding:0.5rem; margin-bottom:0.75rem; }
		.btn { padding:0.5rem 1rem; }
		.error { color:#a00; }
		.success { color:#080; }
	</style>
</head>
<body>
	<h1>Iniciar sesión</h1>

	<?php if (!empty($errors)): ?>
		<div class="error">
			<ul>
				<?php foreach ($errors as $e): ?>
					<li><?php echo htmlspecialchars($e); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ($success): ?>
		<div class="success"><?php echo htmlspecialchars($success); ?></div>
	<?php endif; ?>

	<?php if (!empty($_SESSION['user'])): ?>
		<p>Conectado como: <?php echo htmlspecialchars($_SESSION['user']); ?></p>
		<p><a href="index.php?logout=1">Cerrar sesión</a></p>
	<?php else: ?>
		<form class="form" method="post" id="loginForm" novalidate>
			<label for="email">Correo electrónico</label>
			<input class="input" id="email" name="email" type="email" required>

			<label for="password">Contraseña</label>
			<input class="input" id="password" name="password" type="password" required
				pattern="(?=^.{8,}$)(?=.*[A-Z])(?=.*\d)(?=.*\W).*">

			<small>La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un signo de puntuación.</small>
			<br><br>
			<button class="btn" type="submit">Entrar</button>
		</form>

		<script>
		(function(){
			const form = document.getElementById('loginForm');
			const password = document.getElementById('password');

			function validPassword(p) {
				return /^(?=.{8,}$)(?=.*[A-Z])(?=.*\d)(?=.*\W).*/.test(p);
			}

			form.addEventListener('submit', function(e){
				const pw = password.value || '';
				if (!validPassword(pw)) {
					e.preventDefault();
					alert('La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un signo de puntuación.');
					password.focus();
				}
			});
		})();
		</script>
		<p style="margin-top:1rem;"><strong>Usuario de prueba:</strong> user@example.com — Contraseña: Password1!</p>
	<?php endif; ?>
</body>
</html>