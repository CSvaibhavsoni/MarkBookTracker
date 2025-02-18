<?php
namespace App\Api;

use App\Services\StudentService;

class StudentApi {
    private $studentService;

    public function __construct() {
        $this->studentService = new StudentService();
    }

    // Method to fetch all students
    public function getAllStudentsAndAssignments() {
        $students = $this->studentService->fetchAllStudentsAssignments();
        return ($students);
    }

    public function getAllStudentsAndComponents($assignmentId) {
        $students = $this->studentService->fetchAllStudentsAndComponents($assignmentId);  // Fetch data via service
        return ($students);
    }

    public function submitAssignmentData($data) {
        $students = $this->studentService->insertData($data);
        return ($students);
    }

    public function assignToStudent($data) {
        $students = $this->studentService->assignToStudent($data);
        return ($students);
    }
}

// API's here

