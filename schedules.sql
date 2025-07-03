CREATE TABLE schedules (
    scheduleID INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    classroom VARCHAR(100) NOT NULL,
    dayOfWeek ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    courseCode VARCHAR(50) NOT NULL,
    lecturer VARCHAR(100) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploaded_schedules(id) ON DELETE CASCADE
);
