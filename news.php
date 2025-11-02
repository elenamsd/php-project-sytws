<?php
session_start();
require_once __DIR__ . '/lib/db.php';

function paginate(int $total, int $perPage, int $page, int $window = 2): array {
	$perPage = max(1, $perPage);
	$pages = max(1, (int)ceil($total / $perPage));
	$page = max(1, min($page, $pages));
	$offset = ($page - 1) * $perPage;
	$start = max(1, $page - $window);
	$end = min($pages, $page + $window);
	return [
		'total' => $total,
		'perPage' => $perPage,
		'pages' => $pages,
		'page' => $page,
		'offset' => $offset,
		'window_start' => $start,
		'window_end' => $end
	];
}

if (empty($_SESSION['user'])) {
	header('Location: index.php');
	exit;
}

$search = trim($_GET['s'] ?? '');
$pageInput = (int)($_GET['page'] ?? 1);
$total = get_news_count($search);
$pagination = paginate($total, 25, $pageInput);
$rows = get_news($pagination['perPage'], $pagination['offset'], $search);
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
		Total: <?= (int)$pagination['total'] ?> | Página <?= (int)$pagination['page'] ?> de <?= (int)$pagination['pages'] ?>
	</p>

	<div class="table-responsive">
		<table class="table table-striped table-sm align-middle">
			<thead class="table-dark">
			<tr>
				<th>Título</th>
				<th>Fecha</th>
				<th>Enlace</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($rows as $r): ?>
				<tr>
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
			<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$pagination['page']-1]) ?>">«</a>
			</li>
			<?php for ($i = $pagination['window_start']; $i <= $pagination['window_end']; $i++): ?>
				<li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
					<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$i]) ?>"><?= $i ?></a>
				</li>
			<?php endfor; ?>
			<li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'page'=>$pagination['page']+1]) ?>">»</a>
			</li>
		</ul>
	</nav>
</div>
</body>
</html>
