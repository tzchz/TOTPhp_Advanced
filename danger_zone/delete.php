<?php
if (!isset($_GET['id'])) {
    die('Type ?id=[Account ID] to Continue');
}

$id = $_GET['id'];

$db = new SQLite3('../sqlite.db');

$stmt = $db->prepare('DELETE FROM tab0 WHERE id = :id');
$stmt->bindValue(':id', $id, SQLITE3_TEXT);
$result = $stmt->execute();

if ($db->changes() > 0) {
    echo 'ID ' . $id . ' deleted';
} else {
    echo 'No record found with ID ' . $id;
}

$db->close();
exit;