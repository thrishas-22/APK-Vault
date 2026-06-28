# Product Requirements Document (PRD)
## Full-Stack APK Distribution & Management Platform

---

## 1. Executive Summary
website name apkvault

A web-based APK distribution platform that allows developers to upload Android applications, users to discover and download them, and admins to manage the ecosystem. The system is architected for simplicity: each page is a single PHP file containing both frontend and backend logic, deployable identically on localhost (XAMPP) and cPanel production without any code changes.

---

## 2. Project Philosophy & Core Constraints

**Single-File Architecture**
Every page of the application is one `.php` file that handles its own HTTP requests, database queries, business logic, and HTML rendering. There are no separate controllers, routes, or API layers.

**Zero-Change Deployment**
The project runs on XAMPP during development and deploys to cPanel production by uploading the same files and updating one configuration file. No build tools, no environment variables, no CI/CD pipeline required.

**DRY via PHP Includes**
Reusable UI (header, footer, navigation, cards, modals) and backend logic (auth checks, database connection, utility functions) are extracted into include files and pulled into each page with `require_once`. These are not pages — they are pure building blocks.

**No Framework Dependency**
Vanilla PHP, MySQL, HTML5, CSS3, and plain JavaScript. Optional use of a CDN-loaded library (e.g., Chart.js for analytics, SweetAlert2 for modals) is permitted but never a build-time dependency.

---

## 3. Technology Stack

| Layer | Technology |
|---|---|
| Server Language | PHP 7.4+ |
| Database | MySQL 5.7+ / MariaDB |
| Frontend | HTML5, CSS3, Vanilla JS |
| Local Server | XAMPP (Apache + MySQL) |
| Production Server | cPanel Shared/VPS Hosting |
| File Storage | Local `/uploads/` directory |
| Session Management | PHP native sessions |
| Charts | Chart.js (CDN) |
| Icons | Font Awesome (CDN) |

---

## 4. Central Configuration File

**File:** `config/config.php`

This is the single source of truth for the entire application. After uploading to cPanel, only this file needs to be edited.

**Sections it manages:**

**Database Credentials**
- Database host, name, username, password, charset, and port

**Website Settings**
- Site name, site URL (switches between localhost and live domain), admin email, max APK upload size, allowed file types, pagination limit, maintenance mode toggle

**Global Content**
- Default meta description, footer copyright text, social media profile URLs (Facebook, Twitter, Instagram, LinkedIn, GitHub, YouTube), support email, contact phone number

**Environment Detection**
A simple flag or hostname check determines if the environment is local or production. This flag can control debug error display (on for local, off for production) without touching any page file.

**Database Connection**
`config/config.php` also establishes the PDO or MySQLi connection object and stores it in a globally accessible variable so every page simply includes config and gets a ready-to-use connection.

---

## 5. User Roles & Permissions

### 5.1 Guest (Unauthenticated)
- Browse and search APKs
- View APK detail pages and developer profiles
- Read reviews and ratings
- View Privacy Policy and Terms & Conditions
- Access login and registration pages

### 5.2 User (Authenticated)
All guest permissions, plus:
- Download APKs (with download count tracking)
- Submit ratings and reviews
- Edit personal profile (avatar, bio, contact info)
- Manage personal download history
- Bookmark/wishlist APKs
- Report inappropriate content

### 5.3 Developer (Authenticated)
All user permissions, plus:
- Upload APK files with metadata (name, description, version, category, screenshots, icon)
- Manage own APK listings (edit, update version, unpublish)
- View per-APK analytics (downloads, ratings, views)
- Manage developer profile (portfolio, website, social links)
- Respond to reviews on own APKs
- Access developer-specific dashboard

### 5.4 Admin (Authenticated)
All developer permissions, plus:
- Approve or reject APK submissions
- Manage all users and developers (ban, verify, promote roles)
- Manage categories and tags
- View platform-wide analytics dashboard
- Manage site content (featured APKs, banners)
- Moderate reviews and handle reports
- Access and export system logs
- Manage Privacy Policy and Terms content

---

## 6. File & Folder Structure

```
project-root/
│
├── config/
│   └── config.php                  ← ONLY file edited between environments
│
├── includes/
│   ├── header.php                  ← <head> tag, meta, CSS links
│   ├── navbar.php                  ← Top navigation bar (role-aware)
│   ├── footer.php                  ← Footer with social links, copyright
│   ├── sidebar.php                 ← Dashboard sidebar (shared by all roles)
│   ├── auth_check.php              ← Redirect if not logged in
│   ├── admin_check.php             ← Redirect if not admin
│   ├── developer_check.php         ← Redirect if not developer or admin
│   ├── functions.php               ← Global utility functions
│   ├── db.php                      ← DB connection (if separated from config)
│   └── components/
│       ├── apk_card.php            ← Reusable APK listing card
│       ├── review_card.php         ← Reusable review display block
│       ├── star_rating.php         ← Star rating input/display component
│       ├── pagination.php          ← Reusable pagination bar
│       ├── search_bar.php          ← Search input with filter dropdowns
│       ├── alert_box.php           ← Success/error message display
│       └── user_avatar.php         ← Avatar display with fallback
│
├── assets/
│   ├── css/
│   │   ├── global.css              ← Reset, variables, typography, utilities
│   │   ├── layout.css              ← Grid, flexbox layout patterns
│   │   ├── components.css          ← Cards, buttons, forms, modals
│   │   ├── dashboard.css           ← Dashboard-specific styles
│   │   └── responsive.css          ← All media queries
│   ├── js/
│   │   ├── global.js               ← Common JS (toggles, alerts, etc.)
│   │   ├── upload.js               ← APK upload validation and progress
│   │   ├── search.js               ← Live search and filter logic
│   │   ├── dashboard.js            ← Chart initialization
│   │   └── rating.js               ← Interactive star rating handler
│   └── images/
│       ├── logo.png
│       ├── default-avatar.png
│       └── default-apk-icon.png
│
├── uploads/
│   ├── apks/                       ← Uploaded .apk files
│   ├── icons/                      ← APK icon images
│   ├── screenshots/                ← APK screenshot images
│   └── avatars/                    ← User profile pictures
│
├── pages/
│   │
│   ├── ── PUBLIC PAGES ──
│   ├── index.php                   ← Homepage (featured + recent APKs)
│   ├── browse.php                  ← Browse all APKs with filters
│   ├── apk-detail.php              ← Single APK page (details + reviews)
│   ├── developer-profile.php       ← Public developer portfolio page
│   ├── search.php                  ← Search results page
│   ├── category.php                ← APKs filtered by category
│   ├── privacy-policy.php          ← Privacy Policy page
│   ├── terms.php                   ← Terms & Conditions page
│   ├── contact.php                 ← Contact form page
│   ├── about.php                   ← About platform page
│   │
│   ├── ── AUTHENTICATION ──
│   ├── login.php                   ← Login form + POST handler
│   ├── register.php                ← Registration form + POST handler
│   ├── logout.php                  ← Session destroy + redirect
│   ├── forgot-password.php         ← Password reset request
│   └── reset-password.php          ← Password reset with token
│   │
│   ├── ── USER DASHBOARD ──
│   ├── user/
│   │   ├── dashboard.php           ← User home (recent downloads, activity)
│   │   ├── profile.php             ← View & edit profile
│   │   ├── downloads.php           ← Download history
│   │   ├── bookmarks.php           ← Saved/bookmarked APKs
│   │   └── reviews.php             ← User's submitted reviews
│   │
│   ├── ── DEVELOPER DASHBOARD ──
│   ├── developer/
│   │   ├── dashboard.php           ← Developer home (stats overview)
│   │   ├── my-apps.php             ← List of uploaded APKs
│   │   ├── upload.php              ← Upload new APK form + handler
│   │   ├── edit-app.php            ← Edit APK details (receives ?id=)
│   │   ├── analytics.php           ← Per-app stats (downloads, views, ratings)
│   │   └── profile.php             ← Developer profile editor
│   │
│   └── ── ADMIN PANEL ──
│       └── admin/
│           ├── dashboard.php       ← Platform overview (KPI cards + charts)
│           ├── users.php           ← Manage all users
│           ├── developers.php      ← Manage developer accounts
│           ├── apps.php            ← All APKs (approve/reject/remove)
│           ├── categories.php      ← Add/edit/delete categories
│           ├── reviews.php         ← Moderate reviews and reports
│           ├── analytics.php       ← Full platform analytics
│           └── settings.php        ← Site settings editor (writes to config or DB)
│
└── database/
    └── schema.sql                  ← Full DB schema for initial setup
```

---

## 7. Database Schema Overview

### `users`
Stores all accounts regardless of role. Key fields: `id`, `username`, `email`, `password_hash`, `role` (enum: guest/user/developer/admin), `avatar`, `bio`, `is_verified`, `is_banned`, `created_at`, `last_login`

### `apks`
Core APK listing. Key fields: `id`, `developer_id`, `category_id`, `title`, `slug`, `description`, `version`, `package_name`, `file_path`, `icon_path`, `status` (enum: pending/approved/rejected/unpublished), `download_count`, `view_count`, `avg_rating`, `created_at`, `updated_at`

### `apk_screenshots`
One-to-many with `apks`. Fields: `id`, `apk_id`, `image_path`, `sort_order`

### `categories`
Flat category list. Fields: `id`, `name`, `slug`, `icon`, `description`, `apk_count`

### `reviews`
Fields: `id`, `apk_id`, `user_id`, `rating` (1–5), `review_text`, `is_flagged`, `created_at`

### `downloads`
Tracks each download event. Fields: `id`, `apk_id`, `user_id` (nullable for guests), `ip_address`, `downloaded_at`

### `bookmarks`
Fields: `id`, `user_id`, `apk_id`, `created_at`

### `reports`
User-submitted content reports. Fields: `id`, `reporter_id`, `target_type` (apk/review/user), `target_id`, `reason`, `status`, `created_at`

### `developer_profiles`
Extended info for developer role. Fields: `user_id`, `website`, `github`, `portfolio`, `company`, `social_links` (JSON)

### `password_resets`
Fields: `id`, `email`, `token`, `expires_at`, `used`

### `site_settings` *(optional)*
Key-value table for settings editable from admin panel without touching config.php. Fields: `setting_key`, `setting_value`, `updated_at`

---

## 8. Page-Level Architecture Pattern

Every `.php` page follows this strict top-to-bottom pattern:

```
1. require_once config/config.php       ← DB connection + settings
2. require_once includes/functions.php  ← Utility functions
3. require_once includes/auth_check.php ← If page requires login
4. [POST handler block]                 ← Process form submissions at top
5. [Data fetching block]                ← Run SELECT queries, prepare variables
6. require_once includes/header.php     ← Outputs <head> and opening <body>
7. require_once includes/navbar.php     ← Top navigation
8. [Page-specific HTML output]          ← Main content using PHP variables
9. require_once includes/footer.php     ← Footer and closing </body></html>
```

POST handling always happens before any HTML output to allow redirects and `header()` calls. All data passed to the HTML section is pre-fetched into PHP variables. No database queries are mixed inline inside HTML loops at the bottom of the file.

---

## 9. Authentication & Session Workflow

**Registration Flow**
1. User submits form on `register.php`
2. Server validates: email uniqueness, password strength, required fields
3. Password hashed with `password_hash()` using `PASSWORD_BCRYPT`
4. Row inserted into `users` table with default role `user`
5. Session started, user redirected to `user/dashboard.php`

**Login Flow**
1. User submits form on `login.php`
2. Server fetches user by email, verifies password with `password_verify()`
3. Checks `is_banned` flag — if banned, shows error and halts
4. Session variables set: `$_SESSION['user_id']`, `$_SESSION['role']`, `$_SESSION['username']`
5. Role-based redirect: admin → `admin/dashboard.php`, developer → `developer/dashboard.php`, user → `user/dashboard.php`

**Auth Guard (includes/auth_check.php)**
Every protected page starts with this include. It checks if `$_SESSION['user_id']` is set. If not, it stores the intended URL in session and redirects to `login.php`. After login, the user is sent back to their original destination.

**Role Guard Files**
- `admin_check.php` — verifies `$_SESSION['role'] === 'admin'`, else 403
- `developer_check.php` — verifies role is `developer` or `admin`, else redirect

---

## 10. APK Upload Workflow

1. Developer accesses `developer/upload.php`
2. Form collects: title, description, version, package name, category, icon (image), screenshots (multiple images), APK file
3. On POST, server validates: file extension `.apk`, file size within configured max, image dimensions/size for icon and screenshots
4. Files saved to `/uploads/apks/`, `/uploads/icons/`, `/uploads/screenshots/` with UUID-based filenames to prevent collisions
5. Database row inserted into `apks` with `status = 'pending'`
6. Admin sees the new submission in `admin/apps.php` pending approval queue
7. Admin approves → `status` updated to `'approved'` → APK appears publicly
8. Developer is notified (on-site notification or status visible in `developer/my-apps.php`)

**Download Flow**
1. User clicks download on `apk-detail.php`
2. POST or GET request sent to `download.php?id=X`
3. Server verifies APK status is `approved`, file exists on disk
4. Row inserted into `downloads` table
5. `download_count` incremented in `apks` table
6. Server sends file with appropriate headers (`Content-Type: application/vnd.android.package-archive`, `Content-Disposition: attachment`)

---

## 11. Search & Filter System

The `search.php` and `browse.php` pages share the same logic pattern.

**Search Parameters (GET)**
- `q` — keyword (searches title, description, package name)
- `category` — category slug filter
- `sort` — options: newest, oldest, most_downloaded, top_rated
- `page` — pagination offset

**Query Construction**
A single SQL SELECT is dynamically built based on which GET parameters are present. Each condition is appended to a `WHERE` clause array that is joined with `AND` before execution. Pagination uses `LIMIT` and `OFFSET` calculated from `$_GET['page']` and the config-defined items-per-page value.

**Live Search (Frontend)**
A debounced `fetch()` call in `search.js` sends the query to a lightweight handler endpoint (`ajax/search-suggest.php`) which returns JSON of matching APK titles and icons for a dropdown preview. This file follows the same single-file pattern but outputs JSON instead of HTML.

---

## 12. Analytics Dashboard

### Developer Analytics (`developer/analytics.php`)
- Total downloads per app (bar chart)
- Download trend over last 30 days (line chart)
- Average rating per app
- Total views vs downloads conversion
- Review count and breakdown by star rating (donut chart)

### Admin Analytics (`admin/analytics.php`)
- Total users, developers, APKs, downloads (KPI cards)
- New user registrations over time (line chart)
- Most downloaded APKs (top 10 table)
- Category distribution (pie chart)
- Platform activity heatmap (downloads by day/hour)
- Pending approvals count
- Flagged reviews count
- Storage used by uploads

All charts rendered client-side using Chart.js loaded from CDN. Data is prepared server-side as PHP arrays, encoded as JSON, and injected into `<script>` tags as JavaScript variables consumed by the chart initialization code in `dashboard.js`.

---

## 13. Rating & Review System

- Each authenticated user may submit one review per APK
- Review form on `apk-detail.php` includes: star rating (1–5) via interactive UI, text review (optional, min/max character enforced)
- On submission, server checks for existing review by same user for same APK; if exists, updates instead of inserts
- After insert/update, a query recalculates `avg_rating` for the APK and updates the `apks` table
- Reviews display with user avatar, username, rating, date, and text
- Developers can reply to reviews on their own apps
- Users can flag reviews; flagged reviews enter moderation queue in admin panel

---

## 14. Reusable Component Specifications

### `includes/components/apk_card.php`
Accepts a `$apk` associative array as a variable set before the include. Renders: icon, title, developer name, category badge, average star rating, download count, and a download/view button. Used on: homepage, browse, search results, category pages, developer my-apps.

### `includes/components/pagination.php`
Accepts `$total_items`, `$current_page`, `$items_per_page`, `$base_url`. Renders numbered pagination with prev/next arrows and ellipsis for large page counts.

### `includes/navbar.php`
Role-aware: renders different navigation links and dropdown menus based on `$_SESSION['role']`. Unauthenticated users see Login/Register. Each role sees links to their respective dashboard.

### `includes/components/star_rating.php`
Dual-mode: display mode (renders filled/half/empty stars from a numeric value) and input mode (renders interactive clickable stars that set a hidden form field). Mode selected by passing a `$mode` variable before including.

### `includes/components/alert_box.php`
Reads from `$_SESSION['flash_message']` set by previous page actions. Displays styled success, error, or warning box and clears the session variable. All POST handlers set flash messages before redirecting, following the POST-Redirect-GET pattern.

---

## 15. Responsive Design Strategy

All layout is built with CSS Flexbox and Grid defined in `layout.css`. Breakpoints are defined as CSS custom properties in `global.css` and used consistently in `responsive.css`:

- **Mobile:** < 576px — single column, hamburger nav, stacked cards
- **Tablet:** 576px–991px — two-column grid, collapsible sidebar
- **Desktop:** 992px+ — full multi-column layout, expanded sidebar

The hamburger menu toggle is handled by a small function in `global.js` that adds/removes a CSS class — no JavaScript frameworks involved.

---

## 16. Social Media Integration

Social links are defined once in `config/config.php` under the global content section. The `footer.php` include reads these config values and renders the icon links dynamically. If a social URL is left empty in config, its icon is automatically hidden via a PHP conditional. This means the footer never shows broken or empty social icons.

---

## 17. Legal Pages

`privacy-policy.php` and `terms.php` are static-content pages that pull their text content from either the `site_settings` database table (allowing admin to edit via `admin/settings.php`) or from PHP string variables in a dedicated content include file. The admin panel includes a basic textarea editor for updating this content without FTP access.

---

## 18. Security Baseline

**SQL Injection Prevention**
All database queries use PDO prepared statements with bound parameters. Raw `$_GET`/`$_POST` values are never concatenated into SQL strings.

**XSS Prevention**
All user-generated content output to HTML is wrapped in `htmlspecialchars()`. This is enforced as a rule in the functions utility include with a helper function `e($value)` that wraps this call.

**CSRF Protection**
All forms include a hidden CSRF token field. The token is generated per-session and stored in `$_SESSION['csrf_token']`. Every POST handler validates this token before processing.

**File Upload Security**
Uploaded files are validated by MIME type (server-side, not just extension), renamed to random UUIDs, stored outside web root where possible or with `.htaccess` blocking direct execution, and size is validated against the config-defined maximum.

**Password Security**
Passwords stored only as bcrypt hashes. Plain text is never logged or stored.

**Session Security**
Sessions use `session_regenerate_id(true)` on login to prevent session fixation. Session cookies are set with `HttpOnly` and `Secure` flags where HTTPS is available.

---

## 19. Localhost to cPanel Deployment Workflow

**Development (XAMPP)**
1. Place project in `htdocs/project-name/`
2. Import `database/schema.sql` via phpMyAdmin
3. Edit `config/config.php`: set `DB_HOST=localhost`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SITE_URL=http://localhost/project-name`
4. Done — application runs at `http://localhost/project-name`

**Production (cPanel)**
1. Upload all files to `public_html/` (or subdirectory) via File Manager or FTP — exact same files, zero modifications
2. Create MySQL database and user via cPanel MySQL Databases tool
3. Import `database/schema.sql` via cPanel phpMyAdmin
4. Edit only `config/config.php`: update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SITE_URL` to production values
5. Set `uploads/` folder permissions to `755` via File Manager
6. Application is live — no other changes needed

---

## 20. Feature Checklist Cross-Reference

| Feature | Primary File(s) |
|---|---|
| User/Developer/Admin roles | `users` table, `auth_check.php`, role guards |
| Sign in / Login | `login.php`, `register.php`, `logout.php` |
| Profile management | `user/profile.php`, `developer/profile.php` |
| APK upload & download | `developer/upload.php`, `download.php` |
| Rating & review system | `apk-detail.php`, `reviews` table |
| Analytics dashboard | `admin/analytics.php`, `developer/analytics.php` |
| Category management | `admin/categories.php`, `category.php` |
| Search with filters | `search.php`, `browse.php`, `ajax/search-suggest.php` |
| Responsive design | `responsive.css`, `layout.css`, `global.js` |
| Privacy Policy & Terms | `privacy-policy.php`, `terms.php` |
| Social media integration | `config/config.php` → `footer.php` |

---

*PRD Version 1.0 — This document defines architecture, data model, workflows, and component contracts. It does not contain source code and is intended to guide implementation from the ground up.*