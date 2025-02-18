<?php
namespace App\Controllers;

use App\Api\StudentApi;

class StudentController {
    private $studentApi;

    public function __construct() {
        $this->studentApi = new StudentApi();  // Create instance of the API class
    }

    public function fetchStudentsAndAssignments() {
        return $this->studentApi->getAllStudentsAndAssignments();
    }

    public function fetchStudentsAndComponents($assignmentId) {
        if (!$this->isValidUUID($assignmentId)) {
            throw new \InvalidArgumentException("Invalid Assignment ID format.");
        }
        return $this->studentApi->getAllStudentsAndComponents($assignmentId);
    }

    public function submitAssignmentData($data) {
        return $this->studentApi->submitAssignmentData($data);
    }

    public function assignToStudent($data) {
        return $this->studentApi->assignToStudent($data);
    }

    private function isValidUUID($assignmentId) {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $assignmentId);
    }
}
