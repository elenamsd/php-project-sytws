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

function get_news_count(string $search = '', int $areaId = 0): int {
    global $mysqli;
    $where = [];
    $params = '';
    $types = '';
    if ($search !== '') {
        $where[] = 'n.titulo LIKE ?';
        $params .= '%' . $search . '%';
        $types .= 's';
    }
    if ($areaId > 0) {
        $where[] = 'n.idarea = ?';
        $params .= chr(0); 
        $types .= 'i';
    }
    $sql = 'SELECT COUNT(*) FROM noticias n';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $stmt = $mysqli->prepare($sql);
    if ($where) {
        $bindValues = [];
        $pieces = explode(chr(0), $params);
        $i = 0;
        if ($search !== '') { $bindValues[] = $pieces[$i++]; }
        if ($areaId > 0) { $bindValues[] = $areaId; }
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
    $where = [];
    $types = '';
    $bind = [];
    if ($search !== '') {
        $where[] = 'n.titulo LIKE ?';
        $types .= 's';
        $bind[] = '%' . $search . '%';
    }
    if ($areaId > 0) {
        $where[] = 'n.idarea = ?';
        $types .= 'i';
        $bind[] = $areaId;
    }
    $sql = 'SELECT n.id, n.titulo, n.url, n.fecha, n.idarea, a.area 
            FROM noticias n 
            LEFT JOIN areas a ON a.id = n.idarea';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY n.fecha DESC LIMIT ? OFFSET ?';
    $types .= 'ii';
    $bind[] = $limit;
    $bind[] = $offset;
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$bind);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function create_news(string $titulo, string $url, int $idarea): bool {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO noticias (titulo, url, fecha, idarea) VALUES (?, ?, NOW(), ?)');
    $stmt->bind_param('ssi', $titulo, $url, $idarea);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}



