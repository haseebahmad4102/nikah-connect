# NikahConnect ☪

> **A Shariah-compliant Islamic matrimonial platform for Muslim families.**

NikahConnect is a web application designed with Islamic values at its core — facilitating halal matchmaking with Wali (guardian) supervision, modesty filters, and family-oriented communication.

---

## 🌟 Key Features

- **☪ Islamic Matchmaking:** Adheres strictly to Shariah guidelines for modesty and family involvement.
- **🛡 Wali (Guardian) System:** Every profile is linked to a guardian (parent/wali) who oversees interactions and approvals.
- **💬 Halal Chat System:** Modesty-focused messaging with supervision capabilities for guardians and administrators.
- **🎯 Preference-Based Matching:** Compatibility scoring algorithm based on religious practice, education, location, and lifestyle.
- **🔒 Privacy Controls:** Modest photo display and strict privacy protection for female profiles.
- **⚙ Admin Dashboard:** Comprehensive oversight, user management, profile approvals, and flagged content monitoring.

---

## 🛠 Tech Stack

- **Backend:** PHP 7.4+ / PHP 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Icons & Fonts:** Font Awesome 6, Google Fonts (Poppins & Amiri)
- **Local Server:** XAMPP / WAMP / LAMP

---

## 🚀 Getting Started

### 1. Requirements
- PHP 7.4 or higher
- MySQL 5.7+ / MariaDB
- Apache Web Server (e.g., via XAMPP)

### 2. Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/nikahconnect.git
   ```

2. **Move files to your web server root:**
   - For XAMPP (Windows): Copy the folder to `C:\xampp\htdocs\nikahconnect`
   - For Linux: Copy the folder to `/var/www/html/nikahconnect`

3. **Database Setup (Automatic):**
   - Start Apache and MySQL via the XAMPP Control Panel.
   - Open your browser and navigate to:
     ```
     http://localhost/nikahconnect/setup.php
     ```
   - Follow the setup screen. *Note: Delete `setup.php` after installation for security.*

4. **Database Setup (Manual - Alternative):**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a database named `nikahconnect`.
   - Import `database.sql`.

5. **Configuration:**
   - Check `includes/config.php` (or copy from `includes/config.sample.php`) and adjust database host, user, and password if different from default.

---

## 🔑 Default Demo Credentials

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@nikahconnect.com` | `admin123` |
| **User (Male)** | `ahmed@example.com` | `user123` |
| **User (Female)** | `fatima@example.com` | `user123` |
| **Guardian (Ahmed)** | `parent.ahmed@example.com` | `user123` |
| **Guardian (Fatima)** | `parent.fatima@example.com` | `user123` |

---

## 📁 Project Structure

```
nikahconnect/
├── admin/                  # Admin dashboard & management pages
│   ├── index.php
│   ├── interests.php
│   ├── messages.php
│   ├── profiles.php
│   ├── reports.php
│   └── users.php
├── assets/                 # Stylesheets & JavaScript
│   ├── css/style.css
│   └── js/main.js
├── includes/               # Core PHP includes & configuration
│   ├── config.php
│   ├── config.sample.php
│   ├── db.php
│   ├── footer.php
│   ├── header.php
│   └── upload.php
├── uploads/                # User uploaded avatars and gallery photos
│   ├── avatars/
│   └── gallery/
├── database.sql            # Initial database schema and sample data
├── migration_parents.sql   # Migration script for guardian system
├── setup.php               # One-click installation helper
└── ... (feature pages)
```

---

## 📄 License

This project is licensed under the [MIT License](LICENSE) — free for educational and personal use.
