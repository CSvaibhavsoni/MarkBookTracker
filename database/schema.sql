-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'MarkbookTracker')
BEGIN
    CREATE DATABASE MarkbookTracker;
END
GO

USE MarkbookTracker;
GO

-- Create Tables
-- Students Table
CREATE TABLE Students (
    StudentID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    StudentNumber VARCHAR(20) NOT NULL UNIQUE,
    FirstName NVARCHAR(50) NOT NULL,
    LastName NVARCHAR(50) NOT NULL,
    Email NVARCHAR(100) NOT NULL UNIQUE,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT CK_Email CHECK (Email LIKE '%_@__%.__%')
);

-- Staff Table
CREATE TABLE Staff (
    StaffID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    StaffNumber VARCHAR(20) NOT NULL UNIQUE,
    FirstName NVARCHAR(50) NOT NULL,
    LastName NVARCHAR(50) NOT NULL,
    Email NVARCHAR(100) NOT NULL UNIQUE,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT CK_StaffEmail CHECK (Email LIKE '%_@__%.__%')
);

-- Assignments Table
CREATE TABLE Assignments (
    AssignmentID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    Title NVARCHAR(100) NOT NULL,
    Description NVARCHAR(MAX),
    TotalPoints DECIMAL(5,2) NOT NULL,
    DueDate DATETIME2 NOT NULL,
    CreatedBy UNIQUEIDENTIFIER NOT NULL,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_Assignments_Staff FOREIGN KEY (CreatedBy) REFERENCES Staff(StaffID),
    CONSTRAINT CK_TotalPoints CHECK (TotalPoints > 0)
);

-- Assignment Components Table
CREATE TABLE AssignmentComponents (
    ComponentID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    AssignmentID UNIQUEIDENTIFIER NOT NULL,
    ComponentName NVARCHAR(100) NOT NULL,
    MaxScore DECIMAL(5,2) NOT NULL,
    Weight DECIMAL(5,2) NOT NULL,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_Components_Assignment FOREIGN KEY (AssignmentID) REFERENCES Assignments(AssignmentID),
    CONSTRAINT CK_MaxScore CHECK (MaxScore > 0),
    CONSTRAINT CK_Weight CHECK (Weight > 0 AND Weight <= 100)
);

-- Student Assignments Table
CREATE TABLE StudentAssignments (
    StudentAssignmentID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    StudentID UNIQUEIDENTIFIER NOT NULL,
    AssignmentID UNIQUEIDENTIFIER NOT NULL,
    SubmissionDate DATETIME2,
    Status VARCHAR(20) NOT NULL DEFAULT 'pending',
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_StudentAssignments_Student FOREIGN KEY (StudentID) REFERENCES Students(StudentID),
    CONSTRAINT FK_StudentAssignments_Assignment FOREIGN KEY (AssignmentID) REFERENCES Assignments(AssignmentID),
    CONSTRAINT CK_Status CHECK (Status IN ('pending', 'submitted', 'graded'))
);

-- Component Marks Table
CREATE TABLE ComponentMarks (
    MarkID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    StudentAssignmentID UNIQUEIDENTIFIER NOT NULL,
    ComponentID UNIQUEIDENTIFIER NOT NULL,
    Score DECIMAL(5,2) NOT NULL,
    Feedback NVARCHAR(MAX),
    GradedBy UNIQUEIDENTIFIER NOT NULL,
    GradedAt DATETIME2 DEFAULT GETDATE(),
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_ComponentMarks_StudentAssignment FOREIGN KEY (StudentAssignmentID) REFERENCES StudentAssignments(StudentAssignmentID),
    CONSTRAINT FK_ComponentMarks_Component FOREIGN KEY (ComponentID) REFERENCES AssignmentComponents(ComponentID),
    CONSTRAINT FK_ComponentMarks_Staff FOREIGN KEY (GradedBy) REFERENCES Staff(StaffID)
);

-- Grade Scale Table
CREATE TABLE GradeScale (
    GradeID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    Grade CHAR(2) NOT NULL,
    MinPercentage DECIMAL(5,2) NOT NULL,
    MaxPercentage DECIMAL(5,2) NOT NULL,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT CK_Percentage CHECK (MinPercentage >= 0 AND MaxPercentage <= 100 AND MinPercentage < MaxPercentage)
);

-- Final Grades Table
CREATE TABLE FinalGrades (
    FinalGradeID UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    StudentAssignmentID UNIQUEIDENTIFIER NOT NULL,
    TotalMarks DECIMAL(5,2) NOT NULL,
    Percentage DECIMAL(5,2) NOT NULL,
    Grade CHAR(2) NOT NULL,
    FinalizedBy UNIQUEIDENTIFIER NOT NULL,
    FinalizedAt DATETIME2 DEFAULT GETDATE(),
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_FinalGrades_StudentAssignment FOREIGN KEY (StudentAssignmentID) REFERENCES StudentAssignments(StudentAssignmentID),
    CONSTRAINT FK_FinalGrades_Staff FOREIGN KEY (FinalizedBy) REFERENCES Staff(StaffID)
);

-- Create Indexes
CREATE INDEX IX_StudentAssignments_Status ON StudentAssignments(Status);
CREATE INDEX IX_ComponentMarks_GradedAt ON ComponentMarks(GradedAt);
CREATE INDEX IX_FinalGrades_Grade ON FinalGrades(Grade);
