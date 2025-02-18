<?php // TODO use singleton method
namespace App\Repositories;

use App\Database\Database;
use PDO;

class StudentRepository {
    private $db, $staff;

    public function __construct() {
        $this->db = Database::getInstance();
        // For now keeping he staff hardcoded, will update to login session later
        // Later based on session login details, will fetch staff ID.
        $this->staff = '11111111-1111-1111-1111-111111111111';
    }

    public function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public function getAllStudents() {
        $stmt = $this->db->getConnection()->prepare("
        SELECT 
            s.StudentID,
            s.StudentNumber,
            s.FirstName,
            s.LastName,
            s.Email,
            a.AssignmentID,
            a.Title AS AssignmentTitle,
            a.Description AS AssignmentDescription,
            a.TotalPoints,
            a.DueDate AS AssignmentDueDate,
            sa.Status AS AssignmentStatus,
            sa.SubmissionDate,
            fg.TotalMarks,
            fg.Percentage,
            fg.Grade
        FROM 
            Students s
        LEFT JOIN 
            StudentAssignments sa ON s.StudentID = sa.StudentID
        LEFT JOIN 
            Assignments a ON sa.AssignmentID = a.AssignmentID
        LEFT JOIN 
            FinalGrades fg ON sa.StudentAssignmentID = fg.StudentAssignmentID
        ORDER BY 
            s.StudentID, a.DueDate;
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAssignments() {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM Assignments");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllStudentsAndComponents($assignmentId)
    {
        $db = $this->db->getConnection();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting

        $componentQuery = "SELECT ComponentID, ComponentName, MaxScore, Weight 
                        FROM AssignmentComponents 
                        WHERE AssignmentID = :assignmentId";
        $componentStmt = $db->prepare($componentQuery);
        $componentStmt->execute(['assignmentId' => $assignmentId]);
        $allComponents = $componentStmt->fetchAll(PDO::FETCH_ASSOC);

        $studentQuery = "SELECT StudentID, StudentNumber, FirstName, LastName FROM Students";
        $studentStmt = $db->prepare($studentQuery);
        $studentStmt->execute();
        $students = [];

        while ($row = $studentStmt->fetch(PDO::FETCH_ASSOC)) {
            $students[$row["StudentID"]] = [
                "StudentID" => $row["StudentID"],
                "StudentNumber" => $row["StudentNumber"],
                "FirstName" => $row["FirstName"],
                "LastName" => $row["LastName"],
                "Components" => []
            ];
        }

        $marksQuery = "SELECT 
                            s.StudentID, 
                            ac.ComponentID, 
                            ac.ComponentName, 
                            ac.MaxScore, 
                            ac.Weight, 
                            cm.Score AS MarkObtained
                        FROM Students s
                        INNER JOIN StudentAssignments sa ON s.StudentID = sa.StudentID 
                            AND sa.AssignmentID = :assignmentId
                        INNER JOIN ComponentMarks cm ON sa.StudentAssignmentID = cm.StudentAssignmentID
                        INNER JOIN AssignmentComponents ac ON cm.ComponentID = ac.ComponentID
                        WHERE ac.AssignmentID = :assignmentId2
                        ORDER BY s.StudentID, ac.ComponentID";

        $marksStmt = $db->prepare($marksQuery);
        $marksStmt->execute(['assignmentId' => $assignmentId, 'assignmentId2' => $assignmentId]);

        while ($row = $marksStmt->fetch(PDO::FETCH_ASSOC)) {
            $studentId = $row['StudentID'];

            if (isset($students[$studentId])) {
                $students[$studentId]["Components"][] = [
                    "ComponentID" => $row["ComponentID"],
                    "ComponentName" => $row["ComponentName"],
                    "MaxScore" => $row["MaxScore"],
                    "Weight" => $row["Weight"],
                    "MarkObtained" => $row["MarkObtained"]
                ];
            }
        }

        return [
            "AllComponents" => $allComponents,
            "Students" => array_values($students)
        ];
    }

    public function fetchStudentAssignmentID($studentId, $assignmentId) {
        $db = $this->db->getConnection();
        try {
            $checkQuery = "SELECT StudentAssignmentID FROM StudentAssignments WHERE StudentID = :studentId AND AssignmentID = :assignmentId";
            $stmt = $db->prepare($checkQuery);
            $stmt->execute([
                ':studentId' => $studentId,
                ':assignmentId' => $assignmentId,
            ]);
            return $stmt->fetchColumn(); // Returns the actual StudentAssignmentID
        } catch (PDOException $e) {
            error_log("Error fetching assignment ID: " . $e->getMessage());
            return false; // Return false instead of an array to indicate failure
        }
    }

    public function assignToStudent($data) {
        $db = $this->db->getConnection();

        try {
            // Ensure the studentId and assignmentId are in valid UUID format
            $studentId = $this->validateUUID($data['studentId']);
            $assignmentId = $this->validateUUID($data['assignmentId']);

            $existingRecord = $this->fetchStudentAssignmentID( $studentId, $assignmentId );

            // If the record already exists, return a message
            if ( $existingRecord ) {
                return ['success' => false, 'message' => 'Assignment already assigned to this student.'];
            }

            $submissionDate = date('Y-m-d H:i:s'); // Current timestamp

            // Prepare the SQL Insert query
            $insertStudentAssignmentQuery = "INSERT INTO StudentAssignments 
                                ( StudentID, AssignmentID, Status, SubmissionDate) 
                                VALUES ( :studentId, :assignmentId, :status, :submissionDate)";
            
            // Prepare and execute the statement
            $stmt = $db->prepare($insertStudentAssignmentQuery);
            $stmt->execute([
                ':studentId' => $studentId,
                ':assignmentId' => $assignmentId,
                ':status' => 'pending', // Default status
                ':submissionDate' => $submissionDate, // Today's date
            ]);

            return ['success' => true];

        } catch (PDOException $e) {
            // If an error occurs, log the error and return false
            error_log("Error assigning student: " . $e->getMessage());
            return ['success' => false];
        }
    }

    public function insertAssignmentData($data) {
        // return $data;
        $db = $this->db->getConnection();

        try {
            $db->beginTransaction();

            $studentAssignmentId = $this->validateUUID($data['studentAssignmentId']);

            $gradedBy = $this->staff;
            foreach ($data['marks'] as $studentId => $components) {
                foreach ($components as $componentId => $score) {
                    $insertComponentMarksQuery = "INSERT INTO ComponentMarks 
                                                (StudentAssignmentID, ComponentID, Score, Feedback, GradedBy, GradedAt, CreatedAt, UpdatedAt) 
                                                VALUES (:studentAssignmentId, :componentId, :score, :feedback, :gradedBy, GETDATE(), GETDATE(), GETDATE())";

                    $feedback = isset($data['components'][$componentId]['feedback']) ? $data['components'][$componentId]['feedback'] : '';
                    $stmt = $db->prepare($insertComponentMarksQuery);
                    $stmt->execute([
                        ':studentAssignmentId' => $studentAssignmentId,
                        ':componentId' => $componentId,
                        ':score' => $score,
                        ':feedback' => $feedback,
                        ':gradedBy' => $gradedBy,
                    ]);
                }
            }

            $db->commit();

            // Return success if insertion is successful
            return ['success' => true, 'message' => 'Component marks inserted successfully'];

        } catch (PDOException $e) {
            // If an error occurs, roll back the transaction
            $db->rollBack();

            // Log the error and return failure
            error_log("Error inserting component marks: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inserting component marks'];
        }
    }

    public function calculateFinalGrade($studentAssignmentId) {
        // Connect to the database
        $db = $this->db->getConnection();

        try {
            // Prepare the stored procedure call
            $query = "EXEC sp_CalculateFinalGrade :StudentAssignmentID";

            // Prepare the statement
            $stmt = $db->prepare($query);
            
            // Bind the parameter
            $stmt->bindParam(':StudentAssignmentID', $studentAssignmentId, PDO::PARAM_STR);

            // Execute the query
            $stmt->execute();

            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Return the result to the frontend
                return [
                    'success' => true,
                    'data' => $result // This will include TotalMarks, Percentage, and Grade
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No data found for the given StudentAssignmentID'
                ];
            }
        } catch (PDOException $e) {
            // Handle any errors
            error_log("Error executing stored procedure: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error calculating final grade'
            ];
        }
    }

    public function insertIntoFinalGrade($data) {
        $db = $this->db->getConnection();
        $finalizedBy = $this->staff; // Hardcoded, replace if needed

        try {
            $db->beginTransaction();

            if (!isset($data['studentAssignmentId'], $data['totalMarks'], $data['Percentage'])) {
                throw new InvalidArgumentException("Invalid grade data. Missing required fields.");
            }
            $studentAssignmentId = $this->validateUUID($data['studentAssignmentId']);
            $totalMarks = $this->sanitizeInput($data['totalMarks']); // Use 'totalMarks' instead of 'TotalMarks'
            $percentage = $this->sanitizeInput($data['Percentage']);
            $grade = isset($data['Grade']) ? $this->sanitizeInput($data['Grade']) : null; // Handle null Grade

            $insertFinalGradeQuery = "INSERT INTO FinalGrades 
                                        (StudentAssignmentID, TotalMarks, Percentage, Grade, FinalizedBy, FinalizedAt, CreatedAt, UpdatedAt)
                                    VALUES 
                                        (:studentAssignmentId, :totalMarks, :percentage, :grade, :finalizedBy, GETDATE(), GETDATE(), GETDATE())";

            $stmt = $db->prepare($insertFinalGradeQuery);
            $stmt->execute([
                ':studentAssignmentId' => $studentAssignmentId,
                ':totalMarks' => $totalMarks,
                ':percentage' => $percentage,
                ':grade' => $grade,
                ':finalizedBy' => $finalizedBy,
            ]);

            $db->commit();
            return ['success' => true, 'message' => 'Final grades inserted successfully'];

        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Error inserting final grades: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inserting final grades'];
        }
    }

    private function validateUUID($uuid) {
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
            return $uuid;
        } else {
            throw new InvalidArgumentException('Invalid UUID format');
        }
    }

    // TODO existing might cause error bro
    private function generateUUID() {
        return sprintf('%s-%s-%s-%s-%s', 
                    bin2hex(random_bytes(4)), 
                    bin2hex(random_bytes(2)), 
                    bin2hex(random_bytes(2)), 
                    bin2hex(random_bytes(2)), 
                    bin2hex(random_bytes(6)));
    }

}
