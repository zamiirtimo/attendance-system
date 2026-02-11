<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$page = 'dashboard';
$page_title = 'Dashboard';

// Get statistics
$stats = [];
$today = date('Y-m-d');

// Total Students
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE status = 'active'");
$stats['total_students'] = mysqli_fetch_assoc($result)['total'];

// Total Classes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM classes");
$stats['total_classes'] = mysqli_fetch_assoc($result)['total'];

// Today's Attendance
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE date = '$today'");
$stats['today_attendance'] = mysqli_fetch_assoc($result)['total'];

// Absent Students Today
$result = mysqli_query($conn, 
    "SELECT COUNT(DISTINCT s.student_id) as total 
     FROM students s 
     LEFT JOIN attendance a ON s.student_id = a.student_id AND a.date = '$today'
     WHERE a.attendance_id IS NULL AND s.status = 'active'");
$stats['absent_today'] = mysqli_fetch_assoc($result)['total'];

// Get attendance data for chart
$attendance_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day = date('D', strtotime("-$i days"));
    
    $result = mysqli_query($conn, 
        "SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
            COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
            COUNT(CASE WHEN status = 'late' THEN 1 END) as late
         FROM attendance 
         WHERE date = '$date'");
    
    $row = mysqli_fetch_assoc($result);
    $attendance_data[] = [
        'date' => $day,
        'present' => $data['present'] ?? 0,
        'absent' => $data['absent'] ?? 0,
        'late' => $data['late'] ?? 0
    ];
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Dashboard Content -->
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Students</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_students']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Classes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_classes']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Today's Attendance</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['today_attendance']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Absent Today</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['absent_today']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Bar Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Attendance Overview (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="attendanceBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Today's Status</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4">
                    <canvas id="attendancePieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Present
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Absent
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Late
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="recentAttendance" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, s.full_name, c.class_name 
                                     FROM attendance a
                                     JOIN students s ON a.student_id = s.student_id
                                     JOIN classes c ON a.class_id = c.class_id
                                     ORDER BY a.created_at DESC LIMIT 10";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['class_name']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $row['status'] == 'present' ? 'success' : 
                                               ($row['status'] == 'late' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['check_in_time'] ?? 'N/A'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = '
<script>
// Bar Chart
var ctxBar = document.getElementById("attendanceBarChart").getContext("2d");
var barChart = new Chart(ctxBar, {
    type: "bar",
    data: {
        labels: ' . json_encode(array_column($attendance_data, 'date')) . ',
        datasets: [{
            label: "Present",
            backgroundColor: "#28a745",
            data: ' . json_encode(array_column($attendance_data, 'present')) . '
        }, {
            label: "Absent",
            backgroundColor: "#dc3545",
            data: ' . json_encode(array_column($attendance_data, 'absent')) . '
        }, {
            label: "Late",
            backgroundColor: "#ffc107",
            data: ' . json_encode(array_column($attendance_data, 'late')) . '
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                stacked: false
            },
            y: {
                beginAtZero: true
            }
        }
    }
});

// Pie Chart
var ctxPie = document.getElementById("attendancePieChart").getContext("2d");
var pieChart = new Chart(ctxPie, {
    type: "pie",
    data: {
        labels: ["Present", "Absent", "Late", "Excused"],
        datasets: [{
            data: [65, 15, 10, 10],
            backgroundColor: ["#28a745", "#dc3545", "#ffc107", "#17a2b8"]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    }
});

// Initialize DataTable
$(document).ready(function() {
    $("#recentAttendance").DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        order: [[0, "desc"]]
    });
});
</script>';

include '../../includes/footer.php';
?>