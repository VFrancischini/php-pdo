<?php

namespace Alura\Pdo\Infrastructure\Repository;

use Alura\Pdo\Domain\Model\Phone;
use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Domain\Repository\StudentRepository;
use PDO;

class PdoStudentRepository implements StudentRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allStudents(): array
    {
        $sqlQuery = 'SELECT * FROM students;';
        $statement = $this->connection->query($sqlQuery);

        return $this->hydrateStudentsList($statement);
    }

    public function studentsBirthAt(\DateTimeInterface $birthDate): array
    {
        $sqlQuery = 'SELECT * FROM students WHERE birth_date = :birth_date;';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(':birth_date', $birthDate->format('Y-m-d'));
        $statement->execute();

        return $this->hydrateStudentsList($statement);
    }

    private function hydrateStudentsList(\PDOStatement $statement): array
    {
        $studentDataList = $statement->fetchAll();
        $studentList = [];

        foreach ($studentDataList as $studentData) {
            $studentList[] = $student = new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date'])
            );

            $this->fillPhonesOf($student);
        }

        return $studentList;
    }

    private function fillPhonesOf(Student $student): void
    {
        $sqlQuery = 'SELECT id, area_code, number FROM phones WHERE student_id = :id;';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(':id', $student->id(), PDO::PARAM_INT);
        $statement->execute();

        $phoneDataList = $statement->fetchAll();

        foreach ($phoneDataList as $phoneData) {
            $phone = new Phone(
                $phoneData['id'],
                $phoneData['area_code'],
                $phoneData['number']
            );

            $student->addPhone($phone);
        }
    }

    public function save(Student $student): bool
    {
        if (is_null($student->id())) {
            return $this->insert($student);
        }

        return $this->update($student);
    }

    private function insert(Student $student): bool
    {
        $sqlQuery = 'INSERT INTO students (name, birth_date) VALUES (:name, :birth_date);';
        $statement = $this->connection->prepare($sqlQuery);

        $success = $statement->execute([
            ':name' => $student->name(),
            'birth_date' => $student->birthDate()->format('Y-m-d')
        ]);

        if ($success) {
            $student->defineId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(Student $student): bool
    {
        $sqlQuery = 'UPDATE students SET name = :name, birth_date = :birth_date WHERE id = :id;';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(':name', $student->name());
        $statement->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));
        $statement->bindValue(':id', $student->id(), PDO::PARAM_INT);

        return $statement->execute();
    }

    public function remove(Student $student): bool
    {
        $sqlQuery = 'DELETE FROM students WHERE id = :id';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(':id', $student->id(), PDO::PARAM_INT);

        return $statement->execute();
    }
}
