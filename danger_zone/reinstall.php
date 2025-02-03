<?php
if($_GET['confirm']!=true)die('Type ?confirm=true to Continue');

unlink('../sqlite.db');
$db = new SQLite3('../sqlite.db');

$db->exec('
    CREATE TABLE IF NOT EXISTS tab0 ( 
            id TEXT PRIMARY KEY, 
            key TEXT
    );
');

$db->close();
echo 'Reinstalled';
exit;