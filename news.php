<?php
session_start();
require_once __DIR__ . '/lib/db.php';

if (empty($_SESSION['user'])) {
	header('Location: index.php');
	exit;
}

$perPage = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$search = trim($_GET['s'] ?? '');

$where = '';
$params = [];
$types = '';

if ($search !== '') {
	$where = 'WHERE titulo LIKE ?';
	$params[] = '%' . $search . '%';
	$types .= 's';
}


$sqlCount = "SELECT COUNT(*) FROM noticias $where";
$stmt = $mysqli->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$pages = $total ? (int)ceil($total / $perPage) : 1;
if ($page > $pages) { $page = $pages; $offset = ($page - 1) * $perPage; }


$sql = "SELECT id, titulo, url, fecha FROM noticias $where ORDER BY fecha DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($types ? "$sql" : $sql);
if ($types) {
	$paramsWithLimits = $params;
	$typesWithLimits = $types . 'ii';
	$paramsWithLimits[] = $perPage;
	$paramsWithLimits[] = $offset;
	$stmt->bind_param($typesWithLimits, ...$paramsWithLimits);
} else {
	$stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Noticias</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<a class="navbar-brand" href="news.php">Noticias</a>
		<div class="d-flex ms-auto align-items-center gap-3">
			<span class="text-light small"><?= htmlspecialchars($_SESSION['user']) ?></span>
			<a class="btn btn-outline-light btn-sm" href="index.php">Inicio</a>
			<a class="btn btn-outline-warning btn-sm" href="index.php?logout=1">Salir</a>
		</div>
	</div>
</nav>

<div class="container py-4">
	<h1 class="h3 mb-4">Listado de noticias</h1>

	<form class="row g-2 mb-3" method="get">
		<div class="col-sm-8 col-md-6">
			<input class="form-control" type="text" name="s" placeholder="Buscar por título..." value="<?= htmlspecialchars($search) ?>">
		</div>
		<div class="col-sm-auto">
			<button class="btn btn-primary" type="submit">Buscar</button>
			<?php if ($search): ?>
				<a class="btn btn-outline-secondary" href="news.php">Limpiar</a>
			<?php endif; ?>
		</div>
	</form>

	<p class="text-muted small mb-2">
		Total: <?= (int)$total ?> | Página <?= (int)$page ?> de <?= (int)$pages ?>
	</p>

	<div class="table-responsive">
		<table class="table table-striped table-sm align-middle">
			<thead class="table-dark">
			<tr>
				<th>ID</th>
				<th>Título</th>
				<th>Fecha</th>
				<th>Enlace</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($rows as $r): ?>
				<tr>
					<td><?= (int)$r['id'] ?></td>
					<td class="text-truncate" style="max-width: 420px;">
						<?= htmlspecialchars($r['titulo']) ?>
					</td>
					<td><?= htmlspecialchars($r['fecha']) ?></td>
					<td>
						<?php if (!empty($r['url'])): ?>
							<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?= htmlspecialchars($r['url']) ?>">Abrir</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$rows): ?>
				<tr><td colspan="4" class="text-center text-muted">Sin resultados</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<nav aria-label="Paginación">
		<ul class="pagination pagination-sm">
			<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$page-1]) ?>">«</a>
			</li>
			<?php
			$start = max(1, $page - 2);
			$end = min($pages, $page + 2);
			for ($i = $start; $i <= $end; $i++): ?>
				<li class="page-item <?= $i === $page ? 'active' : '' ?>">
					<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$i]) ?>"><?= $i ?></a>
				</li>
			<?php endfor; ?>
			<li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$page+1]) ?>">»</a>
			</li>
		</ul>
	</nav>
</div>
</body>
</html>
