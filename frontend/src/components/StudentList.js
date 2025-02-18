import React, { useEffect, useState } from "react";
import { fetchStudents } from "../services/api";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  TextField,
  TableSortLabel,
} from "@mui/material";
import { Link } from "react-router-dom";

const StudentList = () => {
  const [students, setStudents] = useState([]);
  const [assignments, setAssignments] = useState([]);
  const [search, setSearch] = useState("");
  const [sortBy, setSortBy] = useState("StudentNumber");
  const [sortOrder, setSortOrder] = useState("asc");

  useEffect(() => {
    const getData = async () => {
      try {
        const { students, assignments } = await fetchStudents();

        // Make sure students data contains assignments field for each student
        setStudents(students.map(student => ({
          ...student,
          assignments: student.assignments || [], // Ensure there's always an empty array if not found
        })));
        setAssignments(assignments);
        console.log("vaiba students", students);
        console.log("vaiba assignments", assignments);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    };

    getData();
  }, []);

  // Search filter
  const filteredStudents = students.filter((student) =>
    `${student.FirstName} ${student.LastName}`
      .toLowerCase()
      .includes(search.toLowerCase())
  );

  // Sorting function
  const sortedStudents = [...filteredStudents].sort((a, b) => {
    const valueA = a[sortBy];
    const valueB = b[sortBy];

    if (valueA < valueB) return sortOrder === "asc" ? -1 : 1;
    if (valueA > valueB) return sortOrder === "asc" ? 1 : -1;
    return 0;
  });

  const handleSort = (column) => {
    const isAsc = sortBy === column && sortOrder === "asc";
    setSortBy(column);
    setSortOrder(isAsc ? "desc" : "asc");
  };

  return (
    <div style={{ padding: "20px" }}>

      {/* Search Bar */}
      <TextField
        label="Search Student"
        variant="outlined"
        fullWidth
        margin="normal"
        onChange={(e) => setSearch(e.target.value)}
      />

      {/* Table */}
      <TableContainer component={Paper} elevation={3} sx={{ borderRadius: 2 }}>
        <Table aria-label="students assignments table">
          <TableHead>
            <TableRow>
              <TableCell>
                <TableSortLabel
                  active={sortBy === "StudentNumber"}
                  direction={sortOrder}
                  onClick={() => handleSort("StudentNumber")}
                >
                  <strong>Student ID</strong>
                </TableSortLabel>
              </TableCell>
              <TableCell>
                <TableSortLabel
                  active={sortBy === "FirstName"}
                  direction={sortOrder}
                  onClick={() => handleSort("FirstName")}
                >
                  <strong>Name</strong>
                </TableSortLabel>
              </TableCell>
              {assignments.map((assignment) => (
                <TableCell key={assignment.AssignmentID} align="center">
                  <Link
                    to={`/assignments/${encodeURIComponent(assignment.Title)}`}
                    state={{ assignmentId: assignment.AssignmentID }}  // Pass ID in state
                    style={{ textDecoration: "none", color: "blue", fontWeight: "bold" }}
                  >
                    {assignment.Title}
                  </Link>
                </TableCell>
              ))}
            </TableRow>
          </TableHead>
          <TableBody>
            {sortedStudents.map((student) => (
              <TableRow key={student.StudentID} hover>
                <TableCell>{student.StudentNumber}</TableCell>
                <TableCell>{`${student.FirstName} ${student.LastName}`}</TableCell>

                {assignments.map((assignment) => {
                  const studentAssignment = student.assignments?.find(
                    (sa) => sa.AssignmentID === assignment.AssignmentID
                  );

                  return (
                    <TableCell key={`${student.StudentID}-${assignment.AssignmentID}`} align="center">
                      {studentAssignment ? (
                        <>
                          <div>{studentAssignment.Grade || "-"}</div>
                          <div style={{ fontSize: "0.8rem", color: "gray" }}>
                            {studentAssignment.Status || "No Status"}
                          </div>
                        </>
                      ) : (
                        "-"
                      )}
                    </TableCell>
                  );
                })}
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
    </div>
  );
};

export default StudentList;
