QNHS GUIDANCE CONSULTATION MANAGEMENT SYSTEM & CAREER PROGRESSION
=================================================================

REQUIREMENTS
- XAMPP (Apache + MySQL)
- PHP 8.0+ recommended
- MySQL / MariaDB
- Modern browser and internet connection for Bootstrap CDN and Google Fonts

INSTALLATION (LOCAL)
1. Extract the qnhs_guidance_system folder into:
   C:\xampp\htdocs\
2. Open XAMPP Control Panel. Start Apache and MySQL.
3. Open http://localhost/phpmyadmin
4. Select Import, choose database.sql from this folder, then click Import/Go.
   The SQL creates the qnhs_guidance database and tables.
5. If your MySQL username/password differs from XAMPP defaults, edit config.php:
   DB_HOST, DB_NAME, DB_USER, DB_PASS.
6. Open:
   http://localhost/qnhs_guidance_system/setup_admin.php
7. Create the first Administrator or Guidance Counselor account.
8. IMPORTANT: Delete setup_admin.php from the htdocs folder after creating the first staff account.
9. Open:
   http://localhost/qnhs_guidance_system/
10. Students can use Student Sign Up. Staff sign in using the account created in step 7.

FEATURES
- Student registration and sign in/sign out
- Password hashing and prepared SQL statements
- Role-based student, counselor, and administrator pages
- Student consultation request form
- Appointment status management and counselor notes
- Student directory for authorized staff
- My Career Plan with nine post-Senior High School planning questions; required and optional fields; saved answers can be updated anytime
- Career-interest reflection activity and saved career profile
- Responsive Bootstrap layout

IMPORTANT PRIVACY / DEPLOYMENT NOTES
- This is a functional starter system for local development and school review.
- Use fictional data while testing. Do not enter real counseling narratives or sensitive student details into a demo installation.
- Before real school use, obtain school approval and have the school IT administrator review privacy notices, consent, access controls, backups, retention/deletion rules, HTTPS, and applicable student-data protection requirements.
- For public hosting, configure HTTPS, secure database credentials, production error handling, backups, and a restricted staff account creation process. Do not expose setup_admin.php after initial setup.
- The career interest activity is a reflection tool, not a validated psychological assessment or a diagnosis.
- Email notifications, SMS, online video calls, and downloadable official reports are not included in this starter build.

LOCAL LINK
http://localhost/qnhs_guidance_system/

A public internet link is not created automatically by XAMPP. To make it accessible outside your computer, the school or site owner must deploy it to an approved PHP/MySQL hosting service and connect a domain.

UPDATED PORTAL LINKS (LOCAL XAMPP)
Homepage: http://localhost/qnhs_guidance_system/
Student registration: http://localhost/qnhs_guidance_system/signup.php
Student login: http://localhost/qnhs_guidance_system/student_login.php
Administrator/counselor login: http://localhost/qnhs_guidance_system/admin_login.php
Admin-only staff account creation: http://localhost/qnhs_guidance_system/admin_signup.php
Initial first staff account setup: http://localhost/qnhs_guidance_system/setup_admin.php

SECURITY NOTE:
Student registration is public. Staff registration is intentionally not public.
Use setup_admin.php only to create the first staff account, then remove that
file from the server. After signing in as an administrator, use admin_signup.php
to create additional staff accounts. Do not expose database credentials or
real student guidance records during development.

OFFICIAL SCHOOL LOGO
The supplied Quezon National High School seal is saved as assets/qnhs-logo.png and is displayed on the homepage, shared navigation bar, and student/staff login panels. Replace this file with another PNG using the same filename to update the logo.

ACCOUNT PROFILE UPDATES
- Students, administrators, and guidance counselors can open Profile from the top navigation.
- The Profile page allows updating full name and email address, changing password (requires current password), and updating/removing a profile picture.
- Email addresses must be unique. Passwords must be at least 10 characters.

ADMINISTRATOR AND GUIDANCE ACCESS
- Administrator accounts can access the administrative dashboard, Manage Requests, Students, and Add Staff.
- Guidance counselors can access the guidance dashboard, Manage Requests, and Students. They cannot create staff accounts.
- Students can access their own consultations, My Career Plan, and student career resources.
- Staff accounts cannot open student-only pages such as My Career Plan, student consultations, or student resource pages. Direct URL access is checked on the server as well as hidden from the navigation.
