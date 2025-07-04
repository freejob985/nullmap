# NullMap - Geographical Location Management System

A comprehensive web application for managing geographical locations, places, and users with an interactive map interface.

## Features

- User authentication and authorization
- Interactive map with custom markers for different location types
- CRUD operations for countries, places, and users
- RTL Arabic interface
- Role-based access control
- Responsive design
- Form validation and sanitization
- Error logging
- API endpoints

## Technologies Used

- PHP for backend
- MySQL for database
- Bootstrap 5 & Material Design for UI
- Leaflet.js for map integration
- DataTables for data management
- SweetAlert2 for confirmations
- Toastify for notifications
- Cairo & Tajawal fonts for Arabic interface

## Installation

1. Clone the repository
2. Import the database schema from `schema.sql`
3. Configure database settings in `config/database.php`
4. Ensure PHP and MySQL are installed and running
5. Access the application through your web server

## Project Structure

```
nullmap/
├── config/
│   └── database.php
├── helpers/
│   ├── database.php
│   ├── auth.php
│   └── validation.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── api/
│   ├── countries.php
│   └── places.php
├── modals/
│   ├── countries.php
│   ├── places.php
│   └── users.php
├── schema.sql
├── index.php
└── login.php
```

## Security

- Input validation and sanitization
- Prepared statements for database queries
- Password hashing
- CSRF protection
- XSS prevention
- Role-based access control

## License

MIT License 