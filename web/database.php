<?
$conn = sqlite_open('agataweb.db');
sqlite_query($conn, 'create table users (login, name, email, password)');
sqlite_query($conn, 'create table rights (login, report)');

sqlite_close($conn);
?>
