# AdminPanel-for-Helpdesk
Sterling Assurance IT Help Desk Admin Panel
A web-based Help Desk ticketing system for Sterling Assurance Nigeria Limited, built with PHP, JavaScript, HTML, and CSS. This application allows staff to submit IT support tickets and enables admins to manage, track, and resolve support requests.

Features
User Authentication: Login system for staff and admin users.
Ticket Submission: Staff can submit hardware/software support tickets.
Admin Dashboard: Admins can view, filter, and update all tickets.
Ticket Status Tracking: Track ticket status (Open, In Progress, Resolved, Closed) and estimated resolution time.
Branch & Priority Management: Tickets can be categorized by branch and priority.
Responsive UI: Modern, responsive design using custom CSS.
File Structure
index.php — Login page for admin users.
admin.php — Admin dashboard for managing tickets.
api.php — REST-style JSON API for authentication and ticket operations.
config.php — Database connection and utility functions.
script.js — Handles client-side logic for login, ticket submission, and dashboard.
style.css — Custom styles for the application.
API Endpoints
All API requests are made to api.php with an action parameter:

login — User login (POST: email, password)
create_ticket — Submit a new ticket (POST: subject, description, priority, type, branch)
fetch_tickets — Get tickets for the logged-in user
fetch_all_tickets — Admin: get all tickets (with optional filters)
update_status — Admin: update ticket status, estimated time, and resolver
logout — Log out the current user
me — Get current session user info
Database
Uses a SQL Server database (SA_HelpDesk).
Main tables: userlog (users), tickets (support tickets).
Setup
Requirements: PHP 7+, SQL Server, web server (e.g., XAMPP).
Configure Database: Update credentials in config.php.
Deploy Files: Place all files in your web server directory.
Access: Open index.php in your browser.
Security Notes
License
Proprietary — Sterling Assurance Nigeria Limited.


