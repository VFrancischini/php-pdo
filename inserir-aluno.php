<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;

require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();

$student = new Student(
    null,
    'Vinicius Francischini',
    new \DateTimeImmutable('2000-12-12')
);

$sqlInsert = 'INSERT INTO students (name, birth_date) VALUES (:name, :birth_date);';
$statement = $pdo->prepare($sqlInsert);
$statement->bindValue(':name', $student->name());
$statement->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));

if ($statement->execute()) {
    echo 'Aluno incluído';
}
