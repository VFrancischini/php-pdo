<?php

use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;

require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();

$preparedStatement = $pdo->prepare('DELETE FROM students WHERE id = :id');
$preparedStatement->bindValue(':id', 2, PDO::PARAM_INT);
print_r($preparedStatement->execute());
