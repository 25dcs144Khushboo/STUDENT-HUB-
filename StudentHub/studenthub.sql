CREATE DATABASE IF NOT EXISTS studenthub;

USE studenthub;

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15),
    course VARCHAR(100),
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (student_id, event_id),

    FOREIGN KEY (student_id)
    REFERENCES students(student_id)
    ON DELETE CASCADE,

    FOREIGN KEY (event_id)
    REFERENCES events(event_id)
    ON DELETE CASCADE
);

INSERT INTO students
(name, email, phone, course, year)
VALUES
('Khushboo Yadav', 'khushboo@example.com', '9876543210', 'BTech CSE', 2),
('Rahul Sharma', 'rahul@example.com', '9876543211', 'BTech CSE', 2);

INSERT INTO events
(title, event_date, event_time, venue, category, description)
VALUES
(
    'Coding Competition',
    '2026-09-12',
    '10:00:00',
    'Computer Lab',
    'Technical',
    'Test your programming skills and compete with students from different departments.'
),
(
    'AI Workshop',
    '2026-09-18',
    '11:00:00',
    'Seminar Hall',
    'Technical',
    'Learn Artificial Intelligence, Machine Learning, and Generative AI from industry experts.'
),
(
    'Sports Meet',
    '2026-09-28',
    '09:00:00',
    'College Ground',
    'Sports',
    'Participate in football, cricket, volleyball, badminton and athletics.'
),
(
    'Cultural Fest',
    '2026-10-05',
    '16:00:00',
    'Auditorium',
    'Cultural',
    'Showcase your talent in dance, music, drama and fashion show.'
),
(
    'Robotics Workshop',
    '2026-10-10',
    '10:30:00',
    'Robotics Lab',
    'Technical',
    'Learn the basics of robotics, sensors and autonomous systems.'
),
(
    'Cricket Tournament',
    '2026-10-15',
    '08:00:00',
    'College Ground',
    'Sports',
    'Inter-department cricket tournament for college students.'
),
(
    'Dance Competition',
    '2026-10-20',
    '17:00:00',
    'Auditorium',
    'Cultural',
    'Participate in solo and group dance competitions.'
),
(
    'Web Development Seminar',
    '2026-10-25',
    '12:00:00',
    'Seminar Hall',
    'Technical',
    'Learn modern HTML, CSS, JavaScript and web development techniques.'
);