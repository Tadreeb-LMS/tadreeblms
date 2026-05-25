# 🚀 TadreebLMS

TadreebLMS is a modern, open-source **Learning Management System (LMS)** built to support educational institutions, training organizations, and professional development programs. It enables seamless delivery of digital learning through structured courses, assessments, progress tracking, and certification.

**Our commitment is to develop future-ready leaders through advanced and innovative learning frameworks. We empower students, professionals, and executives with the strategic knowledge and adaptable skills essential for success in today’s fast-changing environment.**

<img width="1000" height="632" alt="hero1" src="https://github.com/user-attachments/assets/68ca111f-8214-48c8-a0db-187f67e20334" />


---

## 🌍 About TadreebLMS

TadreebLMS is designed to be **flexible, scalable, and customizable**, making it suitable for:

- Academic learning  
- Corporate & professional training  
- Skill development programs  
- Online & blended learning  

As an **open-source platform**, TadreebLMS gives organizations full control over their learning infrastructure.

---

## 📚 Key Features

- User & Role Management (Admin, Instructor, Learner)  
- Course & Enrollment Management  
- Assessments & Evaluations  
- Progress Tracking & Reports  
- Certificate Generation  
- Resource Library  
- Multi-language Support (English, Arabic)  
- Responsive & Secure Design  

---

## 🛠 Technology Stack

- **Backend:** PHP / Laravel  
- **Frontend:** HTML, CSS, JavaScript  
- **Database:** MySQL  
- **Web Server:** Apache  
- **License:** GNU AGPLv3  

---

## 📦 Installation Guide

For complete step-by-step instructions, please refer to our official documentation:
👉 **[TadreebLMS Installation Guide](https://tadreeblms.com/docs/installation)**

### CLI Installation (Recommended)

> **Note:** Browser-based installer files (`install.php`, `install_ajax.php`, `install-b.php`) have been removed for security. All installation is now CLI-only.

**Prerequisites:**
- PHP 8.0+
- Composer
- MySQL database
- Required PHP extensions: `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `gd`, `zip`, `fileinfo`

---

#### Option A — Interactive (recommended for manual setup)

The installer will prompt you for each value and confirm destructive steps. DB passwords are hidden on input.

```bash
# 1. Install Composer dependencies
composer install --no-dev

# 2. Run the installer (no flags needed)
php artisan app:install
```

You will be prompted for:
| Prompt | What to enter |
|---|---|
| `DB host` | MySQL host (default `127.0.0.1`) |
| `DB port` | MySQL port (default `3306`) |
| `Database name` | Name of your database |
| `DB username` | MySQL user |
| `DB password` | *(hidden input)* |
| `Application URL` | Public URL of your app (default `http://localhost`) |

Before running migrations and seeding, the installer asks for confirmation. Migrations default to `yes`, seeding to `yes`.

#### Option B — Non-interactive (recommended for CI/CD / automation)

Pass all values as CLI flags. Add `-n` to skip all prompts (defaults will be used for any missing flag).

```bash
php artisan app:install -n \
    --db-host=127.0.0.1 \
    --db-port=3306 \
    --db-database=your_database \
    --db-username=your_user \
    --db-password=your_password \
    --app-url=http://your-domain.com
```

> **Security note:** Using `--db-password` on the command line exposes it in process listings (`ps aux`). For interactive sessions, prefer Option A (no flags) so the password stays hidden.

---

#### Common options

| Flag | Purpose |
|---|---|
| `--skip-composer` | Skip `composer install` (if already run) |
| `--force` | Reinstall (resets the `/installed` flag). Prompts for confirmation unless `-n` is set. |
| `-n` / `--no-interaction` | Non-interactive mode — use all defaults for missing flags |

**Examples:**
```bash
# Reinstall non-interactively
php artisan app:install -n --force --db-host=... --db-database=... --db-username=... --db-password=...

# Install without running composer again
php artisan app:install --skip-composer
```

---

#### Post-install

```bash
# Fix storage permissions (if needed)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Start the dev server
php artisan serve
```

---

### Production deployment

```bash
# Install dependencies (never --ignore-platform-reqs in production)
composer install --no-dev --prefer-dist --optimize-autoloader

# Run the installer non-interactively
php artisan app:install -n \
    --db-host=127.0.0.1 \
    --db-database=your_database \
    --db-username=your_user \
    --db-password=your_password \
    --app-url=https://your-domain.com

# Laravel production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure debug mode is OFF
# Verify APP_DEBUG=false in .env
# Verify APP_ENV=production in .env
```

> **Production checklist:**
> - `APP_DEBUG` must be `false` — never expose debug output on a public server
> - `APP_ENV` must be `production`
> - Log files must only exist under `storage/logs/` — never under `public/`
> - Only `public/index.php` should be web-executable; configure your server to block other `.php` files in `public/`

## FAQ

For FAQ kindly refer to  : https://tadreeblms.com/faqs/faq

## 📄 License

Licensed under the **GNU Affero General Public License (AGPLv3)**.


## 🤝 Contributors

Thank you to everyone who has contributed to Tadreeb LMS — your efforts make this project better every day.

We appreciate contributions in:
- Code
- Bug reports
- Documentation
- Testing and QA
- UX feedback

👉 Full list of contributors:  
https://github.com/Tadreeb-LMS/tadreeblms/graphs/contributors