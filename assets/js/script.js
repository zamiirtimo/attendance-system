// Toggle Sidebar
document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.getElementById("menu-toggle");
    if (menuToggle) {
        menuToggle.addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("wrapper").classList.toggle("toggled");
        });
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Show Loading Spinner
function showLoading() {
    document.querySelector('.spinner-wrapper').style.display = 'flex';
}

// Hide Loading Spinner
function hideLoading() {
    document.querySelector('.spinner-wrapper').style.display = 'none';
}

// Format Date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Export Table to Excel
function exportToExcel(tableId, filename) {
    var table = document.getElementById(tableId);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(wb, filename || 'export.xlsx');
}

// Print Function
function printDiv(divId) {
    var printContents = document.getElementById(divId).innerHTML;
    var originalContents = document.body.innerHTML;
    
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}

// Mark Attendance
function markAttendance(studentId, classId, status) {
    var date = new Date().toISOString().split('T')[0];
    var time = new Date().toLocaleTimeString();
    
    fetch('ajax/save-attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            student_id: studentId,
            class_id: classId,
            date: date,
            status: status,
            check_in_time: time
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var btn = document.querySelector(`.attendance-btn[data-student-id="${studentId}"]`);
            btn.classList.add('active');
            setTimeout(function() {
                btn.classList.remove('active');
            }, 1000);
            
            // Show success message
            var toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Search Students
function searchStudents() {
    var input = document.getElementById('studentSearch');
    var filter = input.value.toUpperCase();
    var table = document.getElementById('studentsTable');
    var tr = table.getElementsByTagName('tr');
    
    for (var i = 0; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td')[2]; // Student name column
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Bulk Attendance
function markBulkAttendance(className, status) {
    var checkboxes = document.querySelectorAll(`.${className}`);
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = (status === 'all' ? true : 
                           (status === 'none' ? false : 
                           (status === 'present' ? true : false)));
    });
}

// Form Validation
function validateForm(formId) {
    var form = document.getElementById(formId);
    var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    var isValid = true;
    
    inputs.forEach(function(input) {
        if (!input.value) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Dynamic Class Selection
document.addEventListener('DOMContentLoaded', function() {
    var classSelect = document.getElementById('class_id');
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            var classId = this.value;
            var studentSelect = document.getElementById('student_id');
            
            if (classId && studentSelect) {
                fetch(`ajax/get-students.php?class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        studentSelect.innerHTML = '<option value="">Select Student</option>';
                        data.forEach(function(student) {
                            studentSelect.innerHTML += `<option value="${student.student_id}">${student.full_name} (${student.student_code})</option>`;
                        });
                    });
            }
        });
    }
});

// Dark Mode Toggle
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    var isDarkMode = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
}

// Initialize Dark Mode from localStorage
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
});

// Dark Mode CSS (add to your style.css)
var darkModeStyle = `
.dark-mode {
    background-color: #1a1a2c;
    color: #fff;
}

.dark-mode .navbar {
    background-color: #16213e !important;
}

.dark-mode .card {
    background-color: #16213e;
    color: #fff;
}

.dark-mode .table {
    color: #fff;
}

.dark-mode .modal-content {
    background-color: #16213e;
    color: #fff;
}
`;