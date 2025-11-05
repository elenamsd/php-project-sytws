<?php
$mysqli = new mysqli('localhost', 'root', '', 'php-project');
if ($mysqli->connect_errno) {
    die('Error de conexión a la base de datos');
}

function get_user_by(string $email): ?array {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT password FROM usuarios WHERE correo = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($password);
    $user = $stmt->fetch() ? ['email' => $email, 'password' => $password] : null;
    $stmt->close();
    return $user;
}

function get_areas(): array {
    global $mysqli;
    $res = $mysqli->query('SELECT id, area FROM areas ORDER BY area');
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function build_news_filters(string $search = '', int $areaId = 0): array {
    $sql = '';
    $types = '';
    $bindValues = [];

    if ($search !== '') {
        $sql .= ' AND n.titulo LIKE ?';
        $types .= 's';
        $bindValues[] = '%' . $search . '%';
    }
    if ($areaId > 0) {
        $sql .= ' AND n.idarea = ?';
        $types .= 'i';
        $bindValues[] = $areaId;
    }
    return [$sql, $types, $bindValues];
}

function get_news_count(string $search = '', int $areaId = 0): int {
    global $mysqli;

    $sql = 'SELECT COUNT(*) FROM noticias n WHERE 1';
    list($filterSql, $types, $bindValues) = build_news_filters($search, $areaId);
    $sql .= $filterSql;

    $stmt = $mysqli->prepare($sql);
    if ($bindValues) {
        $stmt->bind_param($types, ...$bindValues);
    }
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return (int)$total;
}

function get_news(int $limit, int $offset, string $search = '', int $areaId = 0): array {
    global $mysqli;

    $sql = 'SELECT n.id, n.titulo, n.url, n.fecha, n.idarea, a.area 
            FROM noticias n 
            LEFT JOIN areas a ON a.id = n.idarea 
            WHERE 1';
    list($filterSql, $types, $bindValues) = build_news_filters($search, $areaId);
    $sql .= $filterSql;
    $sql .= ' ORDER BY n.fecha DESC LIMIT ? OFFSET ?';
    $types .= 'ii';
    $bindValues[] = $limit;
    $bindValues[] = $offset;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$bindValues);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function get_news_by_id(int $id): ?array {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT n.id, n.titulo, n.url, n.fecha, n.idarea, a.area 
                              FROM noticias n LEFT JOIN areas a ON a.id = n.idarea
                              WHERE n.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function create_news(string $titulo, string $url, int $idarea): bool {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO noticias (titulo, url, fecha, idarea) VALUES (?, ?, NOW(), ?)');
    $stmt->bind_param('ssi', $titulo, $url, $idarea);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function update_news(int $id, string $titulo, string $url, int $idarea): bool {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE noticias SET titulo = ?, url = ?, fecha = NOW(), idarea = ? WHERE id = ?');
    $stmt->bind_param('ssii', $titulo, $url, $idarea, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function delete_news(int $id): bool {
    global $mysqli;
    $stmt = $mysqli->prepare('DELETE FROM noticias WHERE id = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
