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

function get_news_count(string $search = ''): int {
    global $mysqli;
    if ($search !== '') {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM noticias WHERE titulo LIKE ?');
        $like = '%' . $search . '%';
        $stmt->bind_param('s', $like);
    } else {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM noticias');
    }
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return (int)$total;
}

function get_news(int $limit, int $offset, string $search = ''): array {
    global $mysqli;
    if ($search !== '') {
        $stmt = $mysqli->prepare('SELECT id, titulo, url, fecha FROM noticias WHERE titulo LIKE ? ORDER BY fecha DESC LIMIT ? OFFSET ?');
        $like = '%' . $search . '%';
        $stmt->bind_param('sii', $like, $limit, $offset);
    } else {
        $stmt = $mysqli->prepare('SELECT id, titulo, url, fecha FROM noticias ORDER BY fecha DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $limit, $offset);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
