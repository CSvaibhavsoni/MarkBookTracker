import React, { useEffect, useState } from "react";
import { useParams, useLocation } from "react-router-dom";
import { fetchComponents, assignMarks, assignAssignmentToStudent } from "../services/api";
import { Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, TextField, Typography, Button, MenuItem, Select, InputLabel, FormControl } from "@mui/material";
import GradeDisplay from "./GradeDisplay"; // Import GradeDisplay

const AssignmentDetails = () => {
  const { title } = useParams();
  const location = useLocation();
  const assignmentId = location.state?.assignmentId;

  const [students, setStudents] = useState([]);
  const [marks, setMarks] = useState({});
  const [selectedStudentId, setSelectedStudentId] = useState("");
  const [allComponents, setAllComponents] = useState([]);
  const [submissionStatus, setSubmissionStatus] = useState("");
  const [assignmentAssigned, setAssignmentAssigned] = useState(false);
  // const [studentAssignmentId, setStudentAssignmentId] = useState(null);
  const [gradeData, setGradeData] = useState(null); // New state to store grade info

  useEffect(() => {
    const getData = async () => {
      try {
        const { Students, AllComponents } = await fetchComponents(assignmentId);
        setStudents(Students);
        setAllComponents(AllComponents);

        const initialMarks = {};
        Students.forEach(student => {
          initialMarks[student.StudentID] = {};
          student.Components.forEach(component => {
            initialMarks[student.StudentID][component.ComponentID] = component.MarkObtained || "";
          });
        });
        setMarks(initialMarks);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    };

    getData();
  }, [assignmentId]);

  const handleStudentChange = (event) => {
    const studentId = event.target.value;
    setSelectedStudentId(studentId);
  };

  const getStudentName = (studentId) => {
    const student = students.find((s) => s.StudentID === studentId);
    return student ? `${student.FirstName} ${student.LastName}` : "student";
  };

  const handleInputChange = (componentId, value, maxScore) => {
    if (value === "" || (parseFloat(value) >= 0 && parseFloat(value) <= parseFloat(maxScore))) {
      setMarks(prev => ({
        ...prev,
        [selectedStudentId]: {
          ...prev[selectedStudentId],
          [componentId]: value,
        },
      }));
    }
  };

  const isFormComplete = () => {
    return allComponents.every(component => {
      const mark = marks[selectedStudentId]?.[component.ComponentID];
      return mark !== "" && mark >= 0 && mark <= component.MaxScore;
    });
  };

  const assignAssignmentToStudentHandler = async () => {
    const data = {
      studentId: selectedStudentId,
      assignmentId: assignmentId
    };

    try {
      const response = await assignAssignmentToStudent(data);
      console.log("vaibhav ", response);
      if (response.success) {
        // const newStudentAssignmentId = response.studentAssignmentId;
        // setStudentAssignmentId(newStudentAssignmentId);
        setAssignmentAssigned(true);
        setSubmissionStatus("Assignment assigned successfully!");
      } else {
        setSubmissionStatus("Might be already assigned..!!");
      }
    } catch (error) {
      console.error("Error assigning assignment:", error);
      setSubmissionStatus("Error assigning assignment.");
    }
  };

  const handleSubmit = async () => {
    try {
      const filteredMarks = Object.fromEntries(
        Object.entries(marks).filter(([studentId, componentMarks]) => {
          return Object.keys(componentMarks).length > 0;
        })
      );

      let totalMarks = 0;
      allComponents.forEach((component) => {
        totalMarks += parseFloat(filteredMarks[selectedStudentId]?.[component.ComponentID] || 0);
      });

      const submissionData = {
        studentId: selectedStudentId,
        assignmentId: assignmentId,
        // studentAssignmentId: studentAssignmentId,
        marks: filteredMarks,
        totalMarks,
      };
      const response = await assignMarks(submissionData);
      console.log(response);

      if (response.insertion.success) {
        setSubmissionStatus("Grade data submitted successfully!");
        if (response.grades?.data) {
          setGradeData(response.grades.data);
        }

      } else {
        setSubmissionStatus("Failed to submit grade data.");
      }
    } catch (error) {
      console.error("Error submitting grade data:", error);
      setSubmissionStatus("Error submitting grade data.");
    }
  };

  return (
    <div>
      <Typography variant="h4" align="center" gutterBottom>
        {title} -
      </Typography>
      <Typography align="center" style={{ marginBottom: "20px" }}>
        The assignment <b>{title}</b> will be assigned to{" "}
        <b>{getStudentName(selectedStudentId)}</b> with grades.
      </Typography>
      <FormControl fullWidth style={{ marginBottom: "20px" }}>
        <InputLabel>Select Student</InputLabel>
        <Select
          value={selectedStudentId}
          onChange={handleStudentChange}
          label="Select Student"
          disabled={assignmentAssigned}  // Disable the Select element when assignment is assigned
        >
          {students.map((student) => (
            <MenuItem key={student.StudentID} value={student.StudentID}>
              {student.FirstName} {student.LastName}
            </MenuItem>
          ))}
        </Select>
      </FormControl>

      {/* Button to assign the assignment to the selected student */}
      {!assignmentAssigned && selectedStudentId && (
        <div style={{ textAlign: "center", marginTop: "20px" }}>
          <Button
            variant="contained"
            color="primary"
            onClick={assignAssignmentToStudentHandler}
            disabled={!selectedStudentId}
          >
            Assign Assignment
          </Button>
        </div>
      )}

      {/* Display components if assignment is successfully assigned */}
      {assignmentAssigned && selectedStudentId && (
        <div>
          <TableContainer component={Paper} elevation={3}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell><strong>Component Name</strong></TableCell>
                  <TableCell><strong>Marks</strong></TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {allComponents.map((component) => (
                  <TableRow key={component.ComponentID}>
                    <TableCell>{component.ComponentName}</TableCell>
                    <TableCell>
                      <TextField
                        type="number"
                        value={marks[selectedStudentId]?.[component.ComponentID] !== undefined
                          ? parseInt(marks[selectedStudentId]?.[component.ComponentID])
                          : ""}
                        onChange={(e) => handleInputChange(component.ComponentID, e.target.value, component.MaxScore)}
                        variant="outlined"
                        size="small"
                        style={{ width: "200px" }}
                        InputProps={{
                          inputProps: {
                            min: 0,
                            max: component.MaxScore,
                            step: "any",
                          },
                        }}
                        helperText={
                          marks[selectedStudentId]?.[component.ComponentID] < 0
                            ? "Marks cannot be negative."
                            : marks[selectedStudentId]?.[component.ComponentID] > component.MaxScore
                              ? `Marks cannot exceed ${component.MaxScore}.`
                              : `Max Score: ${component.MaxScore}`
                        }
                        error={parseInt(marks[selectedStudentId]?.[component.ComponentID]) < 0 ||
                          parseInt(marks[selectedStudentId]?.[component.ComponentID]) > component.MaxScore}
                      />
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>

          <div style={{ textAlign: "center", marginTop: "20px" }}>
            <Button
              variant="contained"
              color="primary"
              onClick={handleSubmit}
              disabled={!selectedStudentId || !isFormComplete()}
            >
              Submit Marks
            </Button>
          </div>
        </div>
      )}
      {gradeData && (
        <GradeDisplay grade={gradeData.Grade} percentage={gradeData.Percentage} totalMarks={gradeData.TotalMarks} />
      )}
      {submissionStatus && (
        <Typography variant="body1" color={submissionStatus.includes("successfully") ? "green" : "red"} align="center" style={{ marginTop: "20px" }}>
          {submissionStatus}
        </Typography>
      )}
    </div>
  );
};

export default AssignmentDetails;
