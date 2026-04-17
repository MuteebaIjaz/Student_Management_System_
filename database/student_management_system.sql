SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0; -- Yeh line add ki hai taake dummy data par error na aaye
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `sender_id` int NOT NULL,
  `sender_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `target_audience` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `announcements` (`id`, `title`, `message`, `sender_id`, `sender_role`, `target_audience`, `created_at`) VALUES
(1, 'Software Engineering Quiz', 'Your quiz will be taken on Monday 15-03-2026. Be Prepared!', 5, 'teacher', '2', '2026-03-11 20:56:32');

CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Leave') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `attendance` (`attendance_id`, `student_id`, `class_id`, `subject_id`, `teacher_id`, `date`, `status`) VALUES
(1, 9, 1, 1, 5, '2026-03-03', 'Present'),
(3, 9, 1, 1, 5, '2026-03-08', 'Present'),
(4, 9, 2, 2, 6, '2026-03-09', 'Present'),
(5, 9, 2, 1, 5, '2026-03-10', 'Absent'),
(6, 9, 2, 1, 5, '2026-03-09', 'Present');

CREATE TABLE `classes` (
  `class_id` int NOT NULL,
  `class_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `section` enum('A','B','C') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `class_subject_teacher` (
  `id` int NOT NULL,
  `class_id` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL,
  `teacher_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `class_subject_teacher` (`id`, `class_id`, `subject_id`, `teacher_id`) VALUES
(1, 3, 1, 5),
(2, 2, 1, 5),
(3, 3, 1, 5),
(4, 1, 1, 5),
(5, 2, 2, 5),
(6, 3, 2, 5),
(7, 2, 3, 5),
(8, 3, 3, 5),
(9, 2, 2, 6);

CREATE TABLE `fee_payments` (
  `payment_id` int NOT NULL,
  `student_id` int NOT NULL,
  `fee_type_id` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('Paid','Unpaid','Partial') DEFAULT 'Unpaid',
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `fee_payments` (`payment_id`, `student_id`, `fee_type_id`, `amount_paid`, `payment_date`, `status`, `remarks`, `recorded_by`, `created_at`) VALUES
(1, 9, 1, 2000.00, '2026-04-02', 'Paid', '', 3, '2026-04-08 18:04:58');

CREATE TABLE `fee_types` (
  `fee_type_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `class_id` int DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `fee_types` (`fee_type_id`, `name`, `amount`, `class_id`, `due_date`, `created_at`) VALUES
(1, 'Examination Fee', 2000.00, 2, '2026-04-06', '2026-04-08 17:53:19');

CREATE TABLE `marks` (
  `marks_id` int NOT NULL,
  `marks` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `total_marks` int DEFAULT '100',
  `exam_type` enum('Quiz','Mid','Final','Assignment') COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `marks` (`marks_id`, `marks`, `subject_id`, `student_id`, `total_marks`, `exam_type`, `date`) VALUES
(1, 23, 1, 9, 100, 'Quiz', '2026-03-17'),
(2, 14, 1, 9, 15, 'Quiz', '2026-03-19');

CREATE TABLE `students` (
  `student_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `Roll_no` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `Profile_Image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `subject` (
  `subject_id` int NOT NULL,
  `subject_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(25) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('core','elective') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `subject` (`subject_id`, `subject_name`, `code`, `type`) VALUES
(1, 'Applied Physics', 'AP-01', 'core'),
(2, 'Mathematics', 'MATH-02', 'elective'),
(3, 'English', 'ENG-04', 'elective'),
(4, 'Science', 'SCI-06', 'core');

CREATE TABLE `subjects` (
  `subject_id` int NOT NULL,
  `subject_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `teacher_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `teachers` (
  `teacher_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `qualification` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Role` enum('admin','teacher','student') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Status` enum('Approved','Pending','Rejected') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `profile_status` tinyint(1) DEFAULT '0',
  `is_first_login` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `Name`, `Email`, `Password`, `Role`, `Status`, `profile_status`, `is_first_login`) VALUES
(3, 'Admin', 'Admin@gmail.com', '$2y$10$97w079wHtcNEQFhwPgxbduwlvVkVWlWVYm19KY9MsFfB1k/oIy3Tu', 'admin', 'Approved', 0, NULL);

ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`subject_id`,`date`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

ALTER TABLE `class_subject_teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_type_id` (`fee_type_id`),
  ADD KEY `recorded_by` (`recorded_by`);

ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`fee_type_id`),
  ADD KEY `class_id` (`class_id`);

ALTER TABLE `marks`
  ADD PRIMARY KEY (`marks_id`),
  ADD UNIQUE KEY `unique_mark` (`student_id`, `subject_id`, `exam_type`, `date`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `student_id` (`student_id`);

ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `Email` (`Email`);

ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `attendance`
  MODIFY `attendance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `classes`
  MODIFY `class_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `class_subject_teacher`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `fee_payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `fee_types`
  MODIFY `fee_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `marks`
  MODIFY `marks_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `students`
  MODIFY `student_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `subject`
  MODIFY `subject_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `subjects`
  MODIFY `subject_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `teachers`
  MODIFY `teacher_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`);

ALTER TABLE `class_subject_teacher`
  ADD CONSTRAINT `class_subject_teacher_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `class_subject_teacher_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `class_subject_teacher_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`fee_type_id`),
  ADD CONSTRAINT `fee_payments_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `fee_types`
  ADD CONSTRAINT `fee_types_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);

ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`),
  ADD CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);

ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

SET FOREIGN_KEY_CHECKS = 1; -- Rules wapas on kar diye taake aage database secure rahe
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;