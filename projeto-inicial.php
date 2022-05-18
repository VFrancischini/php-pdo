<?php

use Alura\Pdo\Domain\Model\Student;

require_once 'vendor/autoload.php';

$student = new Student(
    null,
    'Vinicius Francischini',
    new \DateTimeImmutable('2000-12-12')
);

echo $student->age();
