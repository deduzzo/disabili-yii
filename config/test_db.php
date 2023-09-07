<?php
$db = require __DIR__ . '/db_example.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=localhost;dbname=yii2basic_test';

return $db;
