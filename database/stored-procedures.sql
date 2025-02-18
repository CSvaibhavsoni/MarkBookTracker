CREATE PROCEDURE sp_CalculateFinalGrade
    @StudentAssignmentID UNIQUEIDENTIFIER
AS
BEGIN
    DECLARE @TotalMarks DECIMAL(5,2)
    DECLARE @Percentage DECIMAL(5,2)
    DECLARE @Grade CHAR(2)

    -- Calculate total marks
    SELECT @TotalMarks = SUM(cm.Score)
    FROM ComponentMarks cm
    WHERE cm.StudentAssignmentID = @StudentAssignmentID

    -- Calculate percentage
    SELECT @Percentage = (@TotalMarks / a.TotalPoints) * 100
    FROM StudentAssignments sa
    INNER JOIN Assignments a ON sa.AssignmentID = a.AssignmentID
    WHERE sa.StudentAssignmentID = @StudentAssignmentID

    -- Determine grade
    SELECT TOP 1 @Grade = Grade
    FROM GradeScale
    WHERE @Percentage BETWEEN MinPercentage AND MaxPercentage
    ORDER BY MinPercentage DESC

    -- Return results
    SELECT 
        @TotalMarks AS TotalMarks,
        @Percentage AS Percentage,
        @Grade AS Grade
END;
GO