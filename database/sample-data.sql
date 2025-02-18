
-- Insert Sample Data
-- Staff Data
INSERT INTO Staff (StaffID, StaffNumber, FirstName, LastName, Email) VALUES
    ('11111111-1111-1111-1111-111111111111', 'STAFF001', 'John', 'Doe', 'john.doe@university.edu'),
    ('22222222-2222-2222-2222-222222222222', 'STAFF002', 'Jane', 'Smith', 'jane.smith@university.edu');

-- Student Data
INSERT INTO Students (StudentID, StudentNumber, FirstName, LastName, Email) VALUES
    ('33333333-3333-3333-3333-333333333333', 'STU001', 'Alice', 'Johnson', 'alice.j@student.edu'),
    ('44444444-4444-4444-4444-444444444444', 'STU002', 'Bob', 'Wilson', 'bob.w@student.edu'),
    ('55555555-5555-5555-5555-555555555555', 'STU003', 'Carol', 'Brown', 'carol.b@student.edu');

-- Assignment Data
INSERT INTO Assignments (AssignmentID, Title, Description, TotalPoints, DueDate, CreatedBy) VALUES
    ('66666666-6666-6666-6666-666666666666', 'Programming Basics', 'Introduction to Programming Concepts', 100.00, '2025-03-15', '11111111-1111-1111-1111-111111111111'),
    ('77777777-7777-7777-7777-777777777777', 'Database Design', 'Fundamentals of Database Management', 100.00, '2025-03-20', '22222222-2222-2222-2222-222222222222');

-- Assignment Components Data
INSERT INTO AssignmentComponents (ComponentID, AssignmentID, ComponentName, MaxScore, Weight) VALUES
    ('88888888-8888-8888-8888-888888888888', '66666666-6666-6666-6666-666666666666', 'Code Quality', 40.00, 40),
    ('99999999-9999-9999-9999-999999999999', '66666666-6666-6666-6666-666666666666', 'Documentation', 30.00, 30),
    ('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '66666666-6666-6666-6666-666666666666', 'Testing', 30.00, 30),
    ('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', '77777777-7777-7777-7777-777777777777', 'Schema Design', 50.00, 50),
    ('cccccccc-cccc-cccc-cccc-cccccccccccc', '77777777-7777-7777-7777-777777777777', 'Queries', 50.00, 50);

-- Student Assignments Data
INSERT INTO StudentAssignments (StudentAssignmentID, StudentID, AssignmentID, SubmissionDate, Status) VALUES
    ('dddddddd-dddd-dddd-dddd-dddddddddddd', '33333333-3333-3333-3333-333333333333', '66666666-6666-6666-6666-666666666666', '2025-03-14', 'submitted'),
    ('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee', '44444444-4444-4444-4444-444444444444', '66666666-6666-6666-6666-666666666666', '2025-03-15', 'submitted'),
    ('ffffffff-ffff-ffff-ffff-ffffffffffff', '55555555-5555-5555-5555-555555555555', '77777777-7777-7777-7777-777777777777', '2025-03-19', 'submitted');

-- Grade Scale Data
INSERT INTO GradeScale (Grade, MinPercentage, MaxPercentage) VALUES
    ('A+', 95.00, 100.00),
    ('A', 85.00, 94.99),
    ('B+', 80.00, 84.99),
    ('B', 70.00, 79.99),
    ('C+', 65.00, 69.99),
    ('C', 55.00, 64.99),
    ('D', 40.00, 54.99),
    ('F', 0.00, 39.99);
