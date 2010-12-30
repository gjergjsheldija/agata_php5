<?php

$conn = sqlite_open('agatausers.db');
sqlite_query($conn, 'drop table permissions');
sqlite_query($conn, 'create table permissions (id, login, report)');
?> 
