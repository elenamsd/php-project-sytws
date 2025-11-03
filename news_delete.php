<?php
session_start();
require_once __DIR__ . '/lib/db.php';

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    delete_news($id);
}

header('Location: news.php?deleted=1');
exit;
