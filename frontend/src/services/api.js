import axios from "axios";

const API_BASE_URL = "http://localhost/markbook-project/backend/src/index.php";

export const fetchStudents = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/?action=fetchStudentsAndAssignments`);
    const { students, assignments } = response.data;
    return { students, assignments };
  } catch (error) {
    console.error("Error fetching students and assignments:", error);
    return { students: [], assignments: [] };
  }
};

export const fetchComponents = async (studentId) => {
  try {
    const response = await axios.get(`${API_BASE_URL}?action=fetchStudentAndComponents&assignmentId=${studentId}`);
    return response.data;
  } catch (error) {
    console.error("Error getting components:", error);
    throw error;
  }
};

export const assignAssignmentToStudent = async (data) => {
  try {
    const response = await axios.post(`${API_BASE_URL}?action=assignToStudent`, data);
    return response.data; // Assuming backend responds with an object { success: true/false }
  } catch (error) {
    console.error("Error assigning assignment:", error);
    throw error;
  }
};

export const assignMarks = async (data) => {
  try {
    const response = await axios.post(`${API_BASE_URL}?action=submitAssignmentData`, data);
    return response.data;
  } catch (error) {
    console.error("Error fetching grade scale:", error);
    throw error;
  }
};