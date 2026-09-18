# .tmp Project Management

**Departmental SDLC Project Tracking & Application Catalog Governance System**

---

> **English** | [Versi Bahasa Indonesia](README-IDN.md)

---

## Executive Summary

**.tmp Project Management** is an enterprise-grade web application designed to automate the oversight of the Software Development Life Cycle (SDLC), centralize departmental application catalogs, monitor SLA deadline compliance, and objectively evaluate team productivity within IT departments.

The platform is architected on **CodeIgniter 4 (PHP 8.2+)**, interfaces directly with **Microsoft SQL Server (MSSQL)** utilizing an in-database binary document storage pattern (`VARBINARY(MAX)`), and is styled with the modern **Mazer Admin Dashboard (Bootstrap 5)** featuring full light and dark mode compatibility.

---

## Core Features

### 1. Dual-Layer Analytics Dashboard
* **Personal Dashboard (`/dashboard`):** Delivers individual workload summaries (Active, Completed, On-Time, Delayed, Overdue, and Risk/Urgent metrics), priority task trackers, monthly completion trend charts, and immediate deadline status indicators.
* **Executive Team Performance Dashboard (`/kinerja-tim`):** High-level managerial dashboard reserved for Department Heads (*Kepala Departemen*) to measure SLA deadline compliance, analyze team SDLC stage distribution, apply quarterly shortcuts (Q1–Q4) or custom calendar ranges, and drill down into individual member project assignments (`/projects/user/{id}`).

### 2. Project Tracker & SDLC Management (`/projects`)
* **Primary PIC Creator Lock:** Project creators are automatically assigned and locked as the primary Person in Charge (PIC) to guarantee accountability, with support for multi-PIC team assignments.
* **Automated Deadline Status Calculation:** The engine dynamically evaluates remaining timeline days against target completion dates:
  * *On Track* (> 7 days remaining)
  * *Risk* (4–7 days remaining)
  * *Urgent* (2–3 days remaining)
  * *Critical / Tomorrow* (<= 1 day remaining)
  * *Overdue* (Target date exceeded without promotion)
* **SQL Server Binary Document Storage:** Technical specifications and supporting documents (PDF, Word, Excel up to 5 MB) are converted and securely stored directly within MSSQL using `VARBINARY(MAX)`.
* **Advanced Multi-Filtering & Exporting:** Comprehensive filtering by SDLC stage, completion status (*Completed / Not Completed / All*), date range, and keyword search, paired with one-click **Excel (.xlsx)** and **PDF** report generation.

### 3. Application Catalog & Governance (`/aplikasi`)
* **18 Technical Parameters:** Centralized documentation of component identity, architectural style (*Monolith / Microservices*), platform and language stack, authentication methods, environment URLs (*Production, UAT, Development*), business/system owners, and licensing schemes.
* **Criticality & Disaster Recovery Tiers:**
  * **C1:** *Mission Critical* (Lowest downtime tolerance, < 1 hour)
  * **C2:** *Business Critical*
  * **C3:** *Business Operational*
  * **C4:** *Non-Critical*
* **Catalog Exporting:** Instant export of entire application portfolios to Excel and PDF formats.

### 4. User Management & RBAC Governance (`/users` - Department Head Only)
* **Centralized Account Administration:** User onboarding, profile management, account suspension/activation, and password resets.
* **Inherited Employee Categories:** Automatically synchronizes employee category from assigned roles (*Organic* for Department Heads & Staff, *NonOrganic* for Contracted / Manmonth personnel).
* **Self-Deactivation Protection:** Hardware- and backend-enforced guards prevent active Department Heads from deactivating their own accounts, eliminating administrative self-lockout risks.
* **Individual Staff Performance KPIs:** Dedicated user detail views (`/users/detail/{id}`) displaying personal KPI summaries and comprehensive assignment histories.

### 5. Security & System Resilience
* **15-Minute Idle Session Timeout:** Automatic server-side session termination after 15 minutes of inactivity, synchronized via client-side background heartbeats.
* **High-Risk Action Safeguards:** Destructive actions (Delete Project, Delete Application, Suspend Account, Reset Password) are strictly guarded with contextual confirmation modals explicitly naming target entities. Native browser `confirm()` prompts are prohibited.
* **Resilient Standalone Error Pages:** Custom-styled, standalone error views for HTTP **403 (Forbidden)**, **404 (Not Found)**, and **500 (Internal Server Error)** status codes featuring intelligent browser history fallbacks. Direct testing routes are available at `/test-error/{code}`.

---

## User Access Matrix (RBAC)

| Module / Feature | Department Head (`role_id: 1`) | Staff (`role_id: 2`) | Manmonth (`role_id: 3`) |
| :--- | :---: | :---: | :---: |
| **Employee Category** | Organic | Organic | NonOrganic |
| **Personal Dashboard (`/dashboard`)** | Full Access | Full Access | Full Access |
| **Project Tracker (View & Create)** | Full Access | Full Access | Full Access |
| **Project Tracker (Edit Project)** | All Projects | Assigned Projects Only | Assigned Projects Only |
| **Application Catalog (`/aplikasi`)** | Full Access | Full Access | Full Access |
| **Team Performance (`/kinerja-tim`)** | Full Access | Blocked (HTTP 403) | Blocked (HTTP 403) |
| **User Management (`/users`)** | Full Access | Blocked (HTTP 403) | Blocked (HTTP 403) |
| **Self-Service Profile (`/profile`)** | Full Access | Full Access | Full Access |

---

## Technology Stack

* **Backend:** PHP 8.2+ with CodeIgniter 4 (MVC Framework)
* **Database:** Microsoft SQL Server (MSSQL) via ODBC / `sqlsrv` driver
* **Frontend:** Mazer Admin Dashboard, Bootstrap 5.3, Bootstrap Icons, FontAwesome 6
* **Visualizations & Analytics:** ApexCharts, Chart.js
* **UI Components:** Flatpickr (Date Range Picker), SweetAlert2
* **Reporting Engines:** DOMPDF (PDF generation), PhpSpreadsheet (Excel generation)

---

## System Requirements

Ensure the server environment meets the following specifications before running the application:
* **PHP Version:** 8.2 or higher
* **Required PHP Extensions:**
  * `php_sqlsrv` and `php_pdo_sqlsrv` (Microsoft SQL Server drivers)
  * `php_intl`
  * `php_mbstring`
  * `php_fileinfo`
  * `php_gd`
* **Web Server:** Apache / Nginx (Recommended via Laragon or XAMPP)
* **Database Server:** Microsoft SQL Server 2016 or newer

---

## Installation & Quick Start Guide

### 1. Clone the Repository
```bash
git clone https://github.com/username/.tmp-dashboard-app.git
cd .tmp-dashboard-app
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration (.env)
Copy the template environment file:
```bash
cp env .env
```
Open `.env` and configure your Microsoft SQL Server database parameters:
```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = SQLSRV
database.default.port     = 1433
```

### 4. Start the Local Server
Run CodeIgniter's development server:
```bash
php spark serve
```
Open your browser and navigate to:
```
http://localhost:8080
```

---

## Default Test Personas

For local testing, the following seeded accounts are available:

| Role | Username | Default Password | Category |
| :--- | :--- | :--- | :--- |
| **Department Head** | `kadept` | `user123` | Organic |
| **Staff** | `shafiq` | `user123` | Organic |
| **Manmonth** | `manmonth` | `user123` | NonOrganic |

---

## Project Directory Structure

```
.tmp-dashboard-app/
├── app/
│   ├── Config/          # Application configuration, routing, and filters
│   ├── Controllers/     # Core module controllers (Auth, Projects, Users, etc.)
│   ├── Filters/         # Security guards (AuthFilter, NoCacheFilter)
│   ├── Helpers/         # Helper functions (deadline calculations, quarter dates)
│   ├── Models/          # SQL Server database query models
│   └── Views/           # Mazer UI presentation templates
│       ├── application/ # Application catalog views
│       ├── auth/        # Authentication views
│       ├── dashboard/   # Personal dashboard views
│       ├── errors/      # Custom standalone error views (403, 404, 500)
│       ├── layouts/     # Master layout, navbar, sidebar, and footer
│       ├── profile/     # Self-service profile & password views
│       ├── projects/    # Project tracker views
│       ├── team_perf/   # Executive team performance views
│       └── users/       # User management views
├── public/              # Web entry point (index.php), CSS/JS assets, and images
├── tests/               # Unit and integration test suites
├── README.md            # Primary repository documentation (English)
└── README-IDN.md        # Indonesian repository documentation
```

---

## License & Governance

This project is developed and maintained for internal departmental operations. All rights reserved.
