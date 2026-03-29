<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-8993be?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Chart.js-4.x-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
</p>

# 🔗 Quicklink — Self-Hosted UTM Link Tracker

A **premium, self-hosted UTM link tracking** platform built with PHP & MySQL. Create short links with UTM parameters, track every click with country flags, device detection, and real-time analytics — all from a beautiful dark-mode dashboard.

**Zero dependencies. No third-party APIs. 100% self-hosted. Free forever.**

<img width="1920" height="912" alt="image" src="https://github.com/user-attachments/assets/e6385dc5-f398-4d50-94d6-8d241947191a" />


---

## ✨ Features

### 🔗 Link Management
- **Custom Short Links** — Create branded short URLs (e.g., `go.yourdomain.com/summer-sale`)
- **UTM Parameter Builder** — Full UTM support: Source, Medium, Campaign, Term, Content
- **Auto UTM Injection** — UTM params automatically appended to destination URL on redirect
- **One-click Copy** — Copy generated links instantly with visual feedback

### 📊 Real-Time Analytics Dashboard
- **Clicks Over Time** — 7-day line chart with smooth gradients
- **Traffic Sources** — Doughnut chart showing top referral sources
- **Top Campaigns** — Bar chart comparing campaign performance
- **Top Countries** — Country list with real flag images 🇮🇳 🇺🇸 🇬🇧 and progress bars
- **Device Breakdown** — Desktop / Mobile / Tablet detection with colored badges
- **Mediums** — Pie chart comparing traffic mediums (CPC, email, social, etc.)

### 🌍 Advanced Tracking
- **IP Geolocation** — Automatic country detection via ip-api.com (no API key needed)
- **Country Flags** — Real flag images from flagcdn.com for 180+ countries
- **Device Detection** — Identifies Desktop, Mobile, and Tablet from User-Agent
- **Referer Tracking** — See exactly where each click came from
- **Click Logger** — Paginated log of every single click with all details

### 🎨 Premium UI/UX
- **Dark Mode** — Sleek glassmorphic dark theme
- **Animated Cards** — Shimmer effects and hover glow on stat cards
- **Toast Notifications** — Custom slide-in toasts (no browser alerts!)
- **Glassmorphic Modals** — Beautiful confirm/success dialogs
- **Fully Responsive** — Works on desktop, tablet, and mobile
- **Live Indicator** — Pulsing green dot showing real-time tracking status

### ⚙️ Easy Customization
- **One-File Branding** — Change name, logo, and favicon from a single `branding.php` file
- **Clean URLs** — `.htaccess` removes `.php` extensions automatically
- **Password Management** — Change admin password from the Settings page
- **No Frameworks** — Pure PHP, vanilla CSS, plain JavaScript. Easy to modify.

---

## 🏗️ Architecture

```
Two-subdomain setup:

┌─────────────────────────────┐     ┌──────────────────────────────┐
│   app.yourdomain.com        │     │   go.yourdomain.com          │
│   (Dashboard & Analytics)   │     │   (Redirect Engine)          │
│                             │     │                              │
│   • Login / Auth            │     │   • Intercepts short links   │
│   • Link Builder            │     │   • Logs IP, country, device │
│   • Analytics Charts        │     │   • Appends UTM params       │
│   • Click Log               │     │   • 301 redirects to target  │
│   • Settings                │     │                              │
└─────────────┬───────────────┘     └──────────────┬───────────────┘
              │                                    │
              └──────────┬─────────────────────────┘
                         │
                ┌────────┴────────┐
                │  MySQL Database │
                │  (Shared)       │
                └─────────────────┘
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+ with PDO & cURL
- MySQL 5.7+
- Apache with `mod_rewrite` enabled
- A domain with DNS access

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/trackflow.git
cd trackflow
```

### 2. Create the Database

Create a MySQL database and run the schema:

```bash
mysql -u root -p your_database < schema.sql
```

Or paste the contents of `schema.sql` into **phpMyAdmin → SQL tab**.

> **Default login:** `admin` / `password` — change this immediately after setup!

### 3. Configure Database Credentials

Update credentials in **two files**:

**`app/config.php`** (lines 4-7):
```php
$host = '127.0.0.1';          // DB host (127.0.0.1 for most shared hosts)
$db   = 'your_db_name';       // Your database name
$user = 'your_db_user';       // Your database username
$pass = 'your_db_password';   // Your database password
```

**`go/index.php`** (lines 4-7):
```php
$host = '127.0.0.1';
$db   = 'your_db_name';       // Same credentials as above
$user = 'your_db_user';
$pass = 'your_db_password';
```

### 4. Deploy

Upload files to your web server:

| Folder | Upload To |
|--------|-----------|
| `app/*` | `app.yourdomain.com` public_html |
| `go/*` | `go.yourdomain.com` public_html |

### 5. Customize Branding (Optional)

Edit `app/branding.php`:

```php
$brand_name    = 'Quicklink';                      // Tool name (everywhere)
$brand_logo    = 'https://example.com/logo.png';    // Logo URL or null
$brand_favicon = 'https://example.com/fav.png';     // Favicon or null
```

### 6. Visit Your Dashboard

Open `https://app.yourdomain.com` and log in with `admin` / `password`.

---

## 📁 Project Structure

```
Quicklink/
├── schema.sql                    # Database tables + default user
├── app/                          # Dashboard (app.yourdomain.com)
│   ├── .htaccess                 # Clean URLs + API protection
│   ├── index.php                 # Login page
│   ├── config.php                # DB connection
│   ├── branding.php              # Name, logo, favicon config
│   ├── helpers.php               # Country flag helpers
│   ├── sidebar.php               # Shared navigation
│   ├── dashboard.php             # Analytics overview
│   ├── create.php                # UTM link builder
│   ├── links.php                 # Link management
│   ├── clicks.php                # Full click log
│   ├── link_analytics.php        # Per-link analytics
│   ├── settings.php              # Account settings
│   ├── logout.php                # Session logout
│   ├── api/
│   │   ├── analytics.php         # Dashboard data endpoint
│   │   ├── create_link.php       # Link creation endpoint
│   │   └── delete_link.php       # Link deletion endpoint
│   └── assets/
│       ├── css/style.css         # Full stylesheet
│       └── js/main.js            # Chart.js dashboard logic
└── go/                           # Redirect engine (go.yourdomain.com)
    ├── .htaccess                 # Route all paths to index.php
    └── index.php                 # Click tracker + redirect logic
```

---

## 🌐 Hosting on Hostinger

<details>
<summary><strong>Click to expand full Hostinger setup guide</strong></summary>

### Step 1: Create Subdomains
1. hPanel → **Domains → Subdomains**
2. Create `app.yourdomain.com` and `go.yourdomain.com`

### Step 2: Create MySQL Database
1. hPanel → **Databases → MySQL Databases**
2. Create database + user, note the credentials

### Step 3: Import Schema
1. hPanel → **Databases → phpMyAdmin**
2. Select your DB → **SQL tab** → paste `schema.sql` → **Go**

### Step 4: Update Credentials
- Edit `app/config.php` lines 4-7
- Edit `go/index.php` lines 4-7
- Use `127.0.0.1` as the host on Hostinger

### Step 5: Upload Files
- Upload `app/` contents to `app.yourdomain.com` public_html
- Upload `go/` contents to `go.yourdomain.com` public_html

### Step 6: Create go/.htaccess
In `go.yourdomain.com` public_html, create `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php?code=$1 [QSA,L]
```
### Step 7: Create app/.htaccess
In `app.yourdomain.com` public_html, create `.htaccess`:
```apache
RewriteEngine On

# Don't touch real files, directories, or the api folder
RewriteRule ^api/ - [L]

# Serve .php files when accessed without extension
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.*)$ $1.php [L]

```

### Step 8: Test
- Visit `https://app.yourdomain.com` → login → create a link → click it → check analytics

</details>

---

## 🔧 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.0+ (PDO) |
| **Database** | MySQL 5.7+ |
| **Frontend** | Vanilla HTML, CSS, JavaScript |
| **Charts** | Chart.js 4.x |
| **Icons** | Font Awesome 6 |
| **Geo Location** | ip-api.com (free, no API key) |
| **Country Flags** | flagcdn.com |
| **Fonts** | Inter (Google Fonts) |

---

## 🛡️ Security

- Passwords hashed with `password_hash()` (bcrypt)
- All database queries use PDO prepared statements (SQL injection safe)
- Session-based authentication
- Input validation and sanitization on all endpoints

> **⚠️ Important:** Change the default admin password immediately after deployment.

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

## 💬 Support

If you find this useful, give it a ⭐ on GitHub!

Found a bug? [Open an issue](https://github.com/YOUR_USERNAME/trackflow/issues).

---

<p align="center">
  Built with ❤️ for marketers who want full control over their tracking data.
</p>
