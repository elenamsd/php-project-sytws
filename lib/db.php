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
