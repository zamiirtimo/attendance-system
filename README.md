# 🎓 UniAttend - University Attendance Management System

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

<div align="center">
  <h3>✨ University Attendance Management System ✨</h3>
  <p>A complete web-based solution for tracking student attendance with modern dashboard design</p>
  
  ![Login Page](https://github.com/user-attachments/assets/b8b7bf56-6dbe-4d2c-baa2-4c4fd5b87e99)
</div>

---

## 📋 About The Project

This **Attendance Management System** was developed as my university graduation project. It digitizes the traditional paper-based attendance tracking process, providing a centralized platform for administrators and teachers to efficiently manage student attendance.

### 🎯 Project Objectives
- ✅ Eliminate manual paper-based attendance
- ✅ Reduce administrative workload
- ✅ Generate accurate attendance reports
- ✅ Provide real-time attendance analytics
- ✅ Create user-friendly interface for all users

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔐 **Secure Login** | Role-based authentication (Admin & Teacher) with session management |
| 📊 **Analytics Dashboard** | Real-time statistics cards and interactive charts using Chart.js |
| 👥 **Student Management** | Complete CRUD operations - Add, Edit, Delete, View students |
| 🏫 **Class Management** | Create classes, assign teachers, manage courses and semesters |
| 📝 **Attendance Module** | Mark Present/Absent/Late/Excused with date tracking |
| 📈 **Reports Generation** | Filter by date, class, student - Printable reports |
| 👤 **Profile Management** | Update profile information and change password |
| 🎨 **Modern UI** | Bootstrap 5 responsive design with professional dashboard layout |

---

## 📸 System Screenshots

<div align="center">
  
  ### 🔐 Login Page
  ![Login Page](https://github.com/user-attachments/assets/b8b7bf56-6dbe-4d2c-baa2-4c4fd5b87e99)
  *Secure login interface with role-based access (Admin/Teacher)*

  ---

  ### 📊 Admin Dashboard
  ![Admin Dashboard](https://github.com/user-attachments/assets/d5507be2-8f6b-4923-952c-5d7092828fb5)
  *Real-time statistics cards and attendance analytics charts*

  ---

  ### 👥 Student Management
  ![Student Management](https://github.com/user-attachments/assets/3de6f2e7-fba9-47d1-a523-baa9ca937a69)
  *Complete student records management with CRUD operations*

  ---

  ### 📝 Take Attendance
  ![Take Attendance](https://github.com/user-attachments/assets/f8fa5a6c-57bf-456b-9241-0ae711e1cee6)
  *Mark student attendance with status options: Present, Late, Absent, Excused*

  ---

  ### 📈 Reports Page
  ![Reports Page](https://github.com/user-attachments/assets/b67c1364-b7a7-4e1d-a7f0-552c9dc19d0d)
  *Generate and filter attendance reports by date, class, and student*

</div>

---

## 🛠️ Built With

### Frontend
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=flat-square&logo=chart.js&logoColor=white)
![DataTables](https://img.shields.io/badge/DataTables-1.11.5-1E88E5?style=flat-square)

### Backend
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=flat-square&logo=apache&logoColor=white)

### Tools
![Git](https://img.shields.io/badge/Git-F05032?style=flat-square&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/GitHub-100000?style=flat-square&logo=github)
![VS Code](https://img.shields.io/badge/VS_Code-007ACC?style=flat-square&logo=visual-studio-code&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat-square&logo=xampp&logoColor=white)

---

## 🚀 Quick Start Guide

### Prerequisites
- ✅ XAMPP/WAMP (PHP 8.0+)
- ✅ MySQL 5.7+
- ✅ Web Browser (Chrome, Firefox, Edge)

### Installation Steps

1. **Download Project**
   ```bash
   git clone https://github.com/zamiirtimo/attendance-system.git


Or download ZIP and extract to C:/xampp/htdocs/attendance-system

Database Setup

Open phpMyAdmin: http://localhost/phpmyadmin

Create new database: attendance_system

Import attendance_system.sql file

Configure Database Connection

Open includes/config.php

Update database credentials:

php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance_system');
Launch Application

Start Apache and MySQL in XAMPP Control Panel

Visit: http://localhost/attendance-system

👥 Default Users
Role	Username	Password	Permissions
Administrator	admin	demo123	Full system access - Manage students, classes, teachers, attendance, reports
Teacher	teacher1	demo123	Take attendance, view reports for assigned classes

📁 Project Structure

📦 attendance-system
├── 📂 assets
│   ├── 📂 css
│   │   └── style.css
│   ├── 📂 js
│   │   └── script.js
│   └── 📂 images
│       └── default-avatar.png
├── 📂 includes
│   ├── config.php      # Database configuration
│   ├── auth.php        # Authentication class
│   ├── header.php      # Header template
│   ├── sidebar.php     # Navigation sidebar
│   └── footer.php      # Footer template
├── 📂 modules
│   ├── 📂 admin        # Admin modules
│   │   ├── dashboard.php
│   │   ├── students.php
│   │   ├── manage-classes.php
│   │   ├── attendance.php
│   │   └── reports.php
│   └── 📂 teacher      # Teacher modules
│       ├── dashboard.php
│       ├── take-attendance.php
│       └── view-reports.php
├── 📄 index.php        # Entry point
├── 📄 login.php        # Login page
├── 📄 logout.php       # Logout handler
├── 📄 profile.php      # User profile
└── 📄 attendance_system.sql  # Database schema

💡 Key Learning Outcomes
Throughout this project, I gained practical experience in:

🔐 PHP Session Management - Role-based authentication & secure login

🗄️ MySQL Database Design - Foreign key relationships & normalized tables

🎨 Modern UI Development - Bootstrap 5 responsive dashboard design

📊 Data Visualization - Interactive charts with Chart.js

📱 Responsive Design - Mobile-friendly interface

🔧 CRUD Operations - Create, Read, Update, Delete functionality

📈 Reporting System - Dynamic filtering and printable reports

🚀 Version Control - Git & GitHub workflow

🎓 Graduation Project
This project was developed as part of my university graduation requirements. It demonstrates:

Criteria	Implementation
Full-stack Development	PHP + MySQL + Bootstrap + JavaScript
Database Design	4 tables with foreign key relationships
User Interface	Professional dashboard design
Functionality	Complete attendance management system
Documentation	Comprehensive README and code comments

🤝 Contributing
This is a graduation project, but feedback and suggestions are welcome!

Fork the repository

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request


📝 License
This project is for educational purposes only as part of my university graduation project.


👨‍💻 Author
Zamiirtimo

🎓 University Student

💻 Web Developer

whats app 252 063 3412658

📧 Email: zamiirtimo@gmail.com

🐙 GitHub: @zamiirtimo

<div align="center">
⭐ If you found this project helpful, please give it a star! ⭐
© 2026 UniAttend - Attendance Management System

</div> ```
