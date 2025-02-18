import React from "react";
import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import StudentList from "./components/StudentList";
import Navigation from "./components/Navigation";
import AssignmentDetails from "./components/AssignAssignments";
import AddAssignment from "./pages/AddAssignment";

function App() {
  return (
    <Router>
      <div style={{ maxWidth: "1000px", margin: "0 auto" }}>
        <Navigation />
        <Routes>
          <Route path="/" element={<StudentList />} />
          {/* <Route path="/assign" element={<AssignAssignments />} /> */}
          <Route path="/assignments/:title" element={<AssignmentDetails />} />
          <Route path="/add-assignment" element={<AddAssignment />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;