<?php
session_start();
require_once __DIR__ . '/lib/db.php';

if (empty($_SESSION['user'])) {
	header('Location: index.php');
	exit;
}

$errors = [];
$titulo = '';
$url = '';
$idarea = 0;

$areas = get_areas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$titulo = trim($_POST['titulo'] ?? '');
	$url = trim($_POST['url'] ?? '');
	$idarea = (int)($_POST['idarea'] ?? 0);

	if ($titulo === '') {
		$errors[] = 'El título es obligatorio.';
	}
	if ($idarea <= 0) {
		$errors[] = 'Seleccione un área.';
	}

	if (!$errors) {
		if (create_news($titulo, $url, $idarea)) {
			header('Location: news.php?created=1');
			exit;
		} else {
			$errors[] = 'Error al crear la noticia.';
		}
	}
}
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Nueva noticia</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
	<div class="container-fluid">
		<a class="navbar-brand" href="news.php">Noticias</a>
		<div class="d-flex ms-auto align-items-center gap-3">
			<span class="text-light small"><?= htmlspecialchars($_SESSION['user']) ?></span>
			<a class="btn btn-outline-light btn-sm" href="news.php">Volver</a>
			<a class="btn btn-outline-warning btn-sm" href="index.php?logout=1">Salir</a>
		</div>
	</div>
</nav>
<div class="container py-4">
	<h1 class="h4 mb-3">Nueva noticia</h1>
	<?php if ($errors): ?>
		<div class="alert alert-danger py-2 mb-3">
			<ul class="m-0 ps-3">
				<?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<form method="post" class="card p-3 shadow-sm">
		<div class="mb-3">
			<label class="form-label" for="titulo">Título</label>
			<input class="form-control" id="titulo" name="titulo" required value="<?= htmlspecialchars($titulo) ?>">
		</div>
		<div class="mb-3">
			<label class="form-label" for="url">URL</label>
			<input class="form-control" id="url" name="url" type="url" value="<?= htmlspecialchars($url) ?>">
		</div>
		<div class="mb-3">
			<label class="form-label" for="idarea">Área</label>
			<select class="form-select" id="idarea" name="idarea" required>
				<option value="">Seleccione...</option>
				<?php foreach ($areas as $a): ?>
					<option value="<?= (int)$a['id'] ?>" <?= $idarea===(int)$a['id']?'selected':'' ?>>
						<?= htmlspecialchars($a['area']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="d-flex gap-2">
			<button class="btn btn-success" type="submit">Crear</button>
			<a class="btn btn-secondary" href="news.php">Cancelar</a>
		</div>
	</form>
</div>
</body>
</html>
