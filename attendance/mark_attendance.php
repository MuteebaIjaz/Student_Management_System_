<?php
require_once "../includes/conn.php";
session_start();
date_default_timezone_set("Asia/Karachi");

if (!isset($_SESSION['user_id'])) {
    header("Location:../Login.php");
    exit();
}


if (isset($_POST['save_attendance'])) {
    $class_id = $_POST['class_id'];
    $teacher_id = $_SESSION['user_id'];
    $subject_id = $_POST['subject_id'];
    $date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');

    foreach ($_POST['status'] as $student_id => $status) {
        $check = mysqli_query(
            $conn,
          "SELECT * FROM attendance WHERE class_id='$class_id'
AND subject_id='$subject_id'
AND date='$attendance_date'"
        );

        if (mysqli_num_rows($check) == 0) {
            $query = "INSERT INTO `attendance`(`class_id`, `student_id`, `subject_id`, `teacher_id`, `date`, `status`) 
                      VALUES ('$class_id','$student_id','$subject_id','$teacher_id','$date','$status')";
            mysqli_query($conn, $query);
        }
    }

    $_SESSION['success'] = "Attendance marked successfully!";
    header("Location:mark_attendance.php");
    exit();
}

if (isset($_POST['edit_attendance'])) {
    $class_id = $_POST['class_id'];
    $teacher_id = $_SESSION['user_id'];
    $subject_id = $_POST['subject_id'];
    $date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');
    foreach ($_POST['status'] as $student_id => $status) {
        $update_query = "UPDATE `attendance` SET `status` = '$status' ,`teacher_id`= '$teacher_id' WHERE 
`student_id` = '$student_id' AND `subject_id` = '$subject_id' AND `date` = '$date'
";
        $update_result = mysqli_query($conn, $update_query);
       

    }
    $_SESSION['success'] = "Attendance updated successfully!";
header("Location: mark_attendance.php");
exit();
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <title>Mark Attendance | SMS</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/favicon.png?v=11" />
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/vendors/css/vendors.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/vendors/css/daterangepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/css/theme.min.css" />
    <link rel="stylesheet" href="../style.css">
</head>


<body>
    <?php
    include "../includes/navbar/teacher_navbar.php";
    include "../includes/header.php";

    ?>

    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Dashboard</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="page-header-right-items">
                        <div class="d-flex d-md-none">
                            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                                <i class="feather-arrow-left me-2"></i>
                                <span>Back</span>
                            </a>
                        </div>

                    </div>
                    <div class="d-md-none d-flex align-items-center">
                        <a href="javascript:void(0)" class="page-header-right-open-toggle">
                            <i class="feather-align-right fs-20"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="registration-container">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="registration-table-card">
                            <div class="registration-table-header">
                                <h4>Mark Attendance:</h4>
                            </div>

                            <div class="card mb-4  mx-4 mx-sm-0 position-relative">

                                <div class="card-body p-sm-5">
                                    <?php
                                    if (isset($_SESSION['error'])) {
                                        echo "<div class='w-100 mt-4 pt-2 text-danger'>" . $_SESSION['error'] . "</div>";
                                        unset($_SESSION['error']);
                                    }
                                    if (isset($_SESSION['success'])) {
                                        echo "<div class='w-100 mt-4 pt-2 text-success'>" . $_SESSION['success'] . "</div>";
                                        unset($_SESSION['success']);
                                    }

                                    ?>
                                    <form method="GET" class="mb-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Select Class</label>
                                                <select name="class_id" class="form-control" required
                                                    onchange="this.form.submit()">
                                                    <option disabled selected>Select Class</option>
                                                    <?php
                                                    $teacher_id = $_SESSION['user_id'];
                                                    $classes = mysqli_query($conn, "SELECT DISTINCT c.class_id,c.class_name,c.section 
                                                        FROM classes c 
                                                        JOIN class_subject_teacher cst ON cst.class_id = c.class_id
                                                        WHERE cst.teacher_id = '$teacher_id'");
                                                    while ($row = mysqli_fetch_assoc($classes)) {
                                                        $selected = (isset($_GET['class_id']) && $_GET['class_id'] == $row['class_id']) ? "selected" : "";
                                                        echo "<option value='{$row['class_id']}' $selected>{$row['class_name']} - {$row['section']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label>Select Subject</label>
                                                <select name="subject_id" class="form-control" required <?php echo isset($_GET['class_id']) ? "" : "disabled"; ?>>
                                                    <option disabled selected>Select Subject</option>
                                                    <?php
                                                    if (isset($_GET['class_id'])) {
                                                        $class_id = $_GET['class_id'];
                                                        $subjects = mysqli_query($conn, "SELECT s.subject_id,s.subject_name 
                                                             FROM subject s 
                                                             JOIN class_subject_teacher cst ON cst.subject_id = s.subject_id 
                                                             WHERE cst.teacher_id = '$teacher_id' 
                                                             AND cst.class_id = '$class_id'");
                                                        while ($row = mysqli_fetch_assoc($subjects)) {
                                                            $selected = (isset($_GET['subject_id']) && $_GET['subject_id'] == $row['subject_id']) ? "selected" : "";
                                                            echo "<option value='{$row['subject_id']}' $selected>{$row['subject_name']}</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label>Attendance Date</label>
                                                <?php
                                                $selected_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d');
                                                ?>
                                                <input type="date" name="attendance_date" class="form-control"
                                                    value="<?php echo ($selected_date); ?>"
                                                    max="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Load Students</button>
                                    </form>

                                    <?php
                                    if (isset($_GET['class_id']) && isset($_GET['subject_id'])) {
                                        $class_id = $_GET['class_id'];
                                        $subject_id = $_GET['subject_id'];

                                        $students = mysqli_query($conn, "SELECT student_id, Roll_no FROM students WHERE class_id='$class_id' ORDER BY Roll_no ASC");

                                        if (mysqli_num_rows($students) > 0) {
                                            $attendance_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d');

                                            $check_existing = mysqli_query($conn, "SELECT * FROM attendance WHERE subject_id='$subject_id' AND date='$attendance_date' LIMIT 1");
                                            $attendance_exists = (mysqli_num_rows($check_existing) > 0);

                                            echo '<form method="POST">';
                                            echo "<input type='hidden' name='class_id' value='$class_id'>";
                                            echo "<input type='hidden' name='subject_id' value='$subject_id'>";
                                            echo "<input type='hidden' name='attendance_date' value='$attendance_date'>";
                                            echo '<div class="table-responsive">';
                                            echo '<table class="table table-bordered">';
                                            echo '<thead><tr><th>S.No</th><th>Roll Number</th><th>Present</th><th>Absent</th></tr></thead><tbody>';

                                            $count = 1;
                                            while ($student = mysqli_fetch_assoc($students)) {
                                                $student_id = $student['student_id'];
                                                $present_checked = "";
                                                $absent_checked = "";

                                                if ($attendance_exists) {
                                                    $status_query = mysqli_query($conn, "SELECT status FROM attendance WHERE student_id='$student_id' AND subject_id='$subject_id' AND date='$attendance_date'");
                                                    if (mysqli_num_rows($status_query) > 0) {
                                                        $record = mysqli_fetch_assoc($status_query);
                                                        if ($record['Status'] == 'Present')
                                                            $present_checked = "checked";
                                                        if ($record['Status'] == 'Absent')
                                                            $absent_checked = "checked";
                                                    }
                                                }

                                                echo '<tr>';
                                                echo '<td>' . $count++ . '</td>';
                                                echo '<td>' . $student['Roll_no'] . '</td>';
                                                echo '<td><input type="radio" name="status[' . $student_id . ']" value="Present" required ' . $present_checked . '></td>';
                                                echo '<td><input type="radio" name="status[' . $student_id . ']" value="Absent" required ' . $absent_checked . '></td>';
                                                echo '</tr>';
                                            }

                                            echo '</tbody></table></div>';

                                            if ($attendance_exists) {
                                                echo '<button type="submit" name="edit_attendance" class="btn btn-warning mt-3">Update Attendance</button>';
                                            } else {
                                                echo '<button type="submit" name="save_attendance" class="btn btn-success mt-3">Save Attendance</button>';
                                            }

                                            echo '</form>';
                                        } else {
                                            echo '<div class="alert alert-info">No students found in this class.</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <script src="../assets/vendors/js/vendors.min.js"></script>
    <script src="../assets/vendors/js/daterangepicker.min.js"></script>
    <script src="../assets/vendors/js/apexcharts.min.js"></script>
    <script src="../assets/vendors/js/circle-progress.min.js"></script>
    <script src="../assets/js/common-init.min.js"></script>
    <script src="../assets/js/dashboard-init.min.js"></script>
    <script src="../assets/js/theme-customizer-init.min.js"></script>
</body>

</html>
