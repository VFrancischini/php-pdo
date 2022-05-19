<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Alura\Pdo\Infrastructure\Repository\PdoStudentRepository;

require_once 'vendor/autoload.php';

$connection = ConnectionCreator::createConnection();
$repository = new PdoStudentRepository($connection);

/**
 * @var Student[] $studentList
 */
$studentList = $repository->studentsWithPhones();

echo $studentList[2]->phones()[0]->formattedPhone() . PHP_EOL;

print_r($studentList);
