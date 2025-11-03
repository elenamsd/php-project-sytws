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
$areaId = (int)($_GET['area'] ?? 0);
$pageInput = (int)($_GET['page'] ?? 1);
$updated = (int)($_GET['updated'] ?? 0);
$deleted = (int)($_GET['deleted'] ?? 0);
$created = (int)($_GET['created'] ?? 0);

$areas = get_areas();
$total = get_news_count($search, $areaId);
$pagination = paginate($total, 25, $pageInput);
$rows = get_news($pagination['perPage'], $pagination['offset'], $search, $areaId);
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Noticias</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="index.css">
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
		<div class="col-sm-5 col-md-4">
			<input class="form-control" type="text" name="s" placeholder="Buscar por título..." value="<?= htmlspecialchars($search) ?>">
		</div>
		<div class="col-sm-4 col-md-3">
			<select name="area" class="form-select">
				<option value="0">Todas las áreas</option>
				<?php foreach ($areas as $a): ?>
					<option value="<?= (int)$a['id'] ?>" <?= $areaId===(int)$a['id']?'selected':'' ?>>
						<?= htmlspecialchars($a['area']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-sm-auto d-flex gap-2">
			<button class="btn btn-primary" type="submit">Buscar</button>
			<?php if ($search || $areaId): ?>
				<a class="btn btn-outline-secondary" href="news.php">Limpiar</a>
			<?php endif; ?>
			<a class="btn btn-success" href="news_new.php">Nueva noticia</a>
		</div>
	</form>

	<?php if ($created): ?>
		<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
			Noticia creada correctamente.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
		</div>
	<?php endif; ?>
	<?php if ($updated): ?>
		<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
			Noticia actualizada correctamente.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
		</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
			Noticia eliminada correctamente.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
		</div>
	<?php endif; ?>

	<p class="text-muted small mb-2">
		Total: <?= (int)$pagination['total'] ?> | Página <?= (int)$pagination['page'] ?> de <?= (int)$pagination['pages'] ?>
	</p>

	<div class="table-responsive">
		<table class="table table-striped table-sm align-middle">
			<thead class="table-dark">
			<tr>
				<th>Título</th>
				<th>Área</th>
				<th>Fecha</th>
				<th>Acciones</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($rows as $r): ?>
				<tr>
					<td class="text-truncate" style="max-width: 320px;"><?= htmlspecialchars($r['titulo']) ?></td>
					<td><?= htmlspecialchars($r['area'] ?? '') ?></td>
					<td><?= htmlspecialchars($r['fecha']) ?></td>
					<td>
						<div class="btn-group btn-group-sm">
							<?php if (!empty($r['url'])): ?>
								<a class="btn btn-outline-primary" target="_blank" rel="noopener" href="<?= htmlspecialchars($r['url']) ?>">Abrir</a>
							<?php endif; ?>
							<a class="btn btn-outline-secondary" href="news_edit.php?id=<?= (int)$r['id'] ?>">Editar</a>
							<a class="btn btn-outline-danger" href="news_delete.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('¿Eliminar esta noticia?');">Eliminar</a>
						</div>
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
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'area'=>$areaId,'page'=>$pagination['page']-1]) ?>">«</a>
			</li>
			<?php for ($i=$pagination['window_start']; $i <= $pagination['window_end']; $i++): ?>
				<li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
					<a class="page-link" href="?<?= http_build_query(['s'=>$search,'area'=>$areaId,'page'=>$i]) ?>"><?= $i ?></a>
				</li>
			<?php endfor; ?>
			<li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
				<a class="page-link" href="?<?= http_build_query(['s'=>$search,'area'=>$areaId,'page'=>$pagination['page']+1]) ?>">»</a>
			</li>
		</ul>
	</nav>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
