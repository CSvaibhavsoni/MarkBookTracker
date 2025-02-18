<?php
namespace App\Services;
use App\Repositories\StudentRepository;

class StudentService {
    private $studentRepository;

    public function __construct() {
        $this->studentRepository = new StudentRepository();
    }

    public function fetchAllStudentsAssignments() {
        $studentsData = $this->studentRepository->getAllStudents();
        $assignmentData = $this->studentRepository->getAllAssignments();
        $students = [];

        foreach ($studentsData as $row) {
            if (!isset($students[$row['StudentID']])) {
                $students[$row['StudentID']] = [
                    'StudentID' => $row['StudentID'],
                    'StudentNumber' => $row['StudentNumber'],
                    'FirstName' => $row['FirstName'],
                    'LastName' => $row['LastName'],
                    'assignments' => []
                ];
            }

            $students[$row['StudentID']]['assignments'][] = [
                'AssignmentID' => $row['AssignmentID'],
                'Grade' => $row['Grade'],
                'Status' => $row['AssignmentStatus'],
            ];
        }

        return ['students' => array_values($students), 'assignments' => $assignmentData];
    }

    public function fetchAllStudentsAndComponents($assignmentId) {
        if (!isset($assignmentId)) {
            throw new InvalidArgumentException("studentAssignmentId is required.");
        }
        return $this->studentRepository->getAllStudentsAndComponents($assignmentId);
    }

    public function insertData($data) {
        if (!isset($data['studentId'])  && !$data['assignmentId']) {
            throw new InvalidArgumentException("Student id ad Assignmnet Id is needed is required.");
        }
        
        // Get AssignmentStudent ID
        $data['studentAssignmentId'] = $this->studentRepository->fetchStudentAssignmentID($data['studentId'], $data['assignmentId']);
        $studentAssignmentId = $data['studentAssignmentId'];

        
        if (!isset($data['studentAssignmentId'])) {
            throw new InvalidArgumentException("studentAssignmentId is required.");
        }

        $returnData = [];

        // insert data
        $returnData['insertion'] = $this->studentRepository->insertAssignmentData($data);


        $returnData['grades'] = $this->studentRepository->calculateFinalGrade($studentAssignmentId);
        $gradesData = isset($returnData['grades']['data']) ? $returnData['grades']['data'] : [];
        $finalGradeData = array_merge($data, $gradesData);
        $this->studentRepository->insertIntoFinalGrade($finalGradeData);

        return $returnData;
    }

    // Assign student
    public function assignToStudent($data) {
        return $this->studentRepository->assignToStudent($data);
    }
}

