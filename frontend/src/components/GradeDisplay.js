// GradeDisplay.js
import React from "react";
import { Typography } from "@mui/material";

const GradeDisplay = ({ totalMarks, percentage, grade }) => {
    return (
        <div style={{ marginTop: "20px", textAlign: "center" }}>
            <Typography variant="h6">Total Marks: {totalMarks}</Typography>
            <Typography variant="h6">Percentage: {percentage}%</Typography>
            <Typography variant="h6">Grade: {grade}</Typography>
        </div>
    );
};

export default GradeDisplay;
