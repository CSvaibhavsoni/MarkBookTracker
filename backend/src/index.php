<?php
// index.php - Backend Entry Point
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\StudentController;
$action = $_GET['action'] ?? '';
$controller = new StudentController();


switch ($action) {
    case 'fetchStudentsAndAssignments':
        echo json_encode($controller->fetchStudentsAndAssignments());
        break;

    case 'fetchStudentAndComponents':
        $assignmentId = $_GET['assignmentId'];
        echo json_encode($controller->fetchStudentsAndComponents($assignmentId));
        break;

    case 'submitAssignmentData':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            echo json_encode(['error' => 'Invalid JSON data']);
            break;
        }
        echo json_encode($controller->submitAssignmentData($data));
        break;

    case 'assignToStudent':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            echo json_encode(['error' => 'Invalid JSON data']);
            break;
        }
        echo json_encode($controller->assignToStudent($data));
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
