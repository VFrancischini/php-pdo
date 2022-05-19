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
            $studentList[] = new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date'])
            );
        }
        return $studentList;
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

    public function studentsWithPhones(): array
    {
        $sqlQuery = 'SELECT students.id,
                            students.name,
                            students.birth_date,
                            phones.id AS phone_id,
                            phones.area_code,
                            phones.number
                    FROM students
                    JOIN phones ON students.id = phones.student_id;';
        $statement = $this->connection->query($sqlQuery);
        $result = $statement->fetchAll();
        $studentList = [];

        foreach ($result as $row) {
            if (!array_key_exists($row['id'], $studentList)) {
                $studentList[$row['id']] = new Student(
                    $row['id'],
                    $row['name'],
                    new \DateTimeImmutable($row['birth_date'])
                );
            }
            $phone = new Phone(
                $row['phone_id'],
                $row['area_code'],
                $row['number']
            );
            $studentList[$row['id']]->addPhone($phone);
        }

        return $studentList;
    }
}
