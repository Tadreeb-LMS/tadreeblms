

# 🚀 TadreebLMS

TadreebLMS is a modern, open-source **Learning Management System (LMS)** built to support educational institutions, training organizations, and professional development programs. It enables seamless delivery of digital learning through structured courses, assessments, progress tracking, and certification.

**Our commitment is to develop future-ready leaders through advanced and innovative learning frameworks. We empower students, professionals, and executives with the strategic knowledge and adaptable skills essential for success in today’s fast-changing environment.**

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

# 📦 Installation Guide

> **Recommended OS:** Ubuntu 20.04 / 22.04  
> **Web Server:** Apache 2.x  
> **PHP Version:** 8.2  
> **Composer Version (Required):** 2.7.8  

---

## 1️⃣ Update Server

```bash
sudo apt update && sudo apt upgrade -y
```

---

## 2️⃣ Install Apache

```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
```

---

## 3️⃣ Install PHP 8.2

```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

```bash
sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-mysql php8.2-bcmath php8.2-gd -y
```

---

## 4️⃣ Install Composer

```bash
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --version=2.7.8
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 5️⃣ Clone TadreebLMS

```bash
cd /var/www
sudo git clone https://github.com/Tadreeb-LMS/tadreeblms.git
```

```bash
sudo chown -R www-data:www-data /var/www/tadreeblms
sudo find /var/www/tadreeblms -type d -exec chmod 755 {} \;
sudo find /var/www/tadreeblms -type f -exec chmod 644 {} \;
```

```bash
cd tadreeblms
```

---

## 6️⃣ Setup Storage & Cache 

```bash
sudo mkdir -p bootstrap/cache

sudo mkdir -p storage/framework/views
sudo mkdir -p storage/framework/cache/data
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/logs

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

sudo chown -R www-data:www-data public
sudo chmod -R 775 public
```

---

## 7️⃣ Install & Configure MySQL

```bash
sudo apt install mysql-server -y
```

```bash
sudo mysql
```

```sql
CREATE DATABASE laravel_db;
CREATE USER 'laravel'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON laravel_db.* TO 'laravel'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 8️⃣ Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/tadreeblms.conf
```

```apache
<VirtualHost *:80>
    ServerName YOUR_DOMAIN
    DocumentRoot /var/www/tadreeblms/public

    <Directory /var/www/tadreeblms/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tadreeblms_error.log
    CustomLog ${APACHE_LOG_DIR}/tadreeblms_access.log combined
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2ensite tadreeblms.conf
sudo systemctl reload apache2
```

---

## 9️⃣ Access Application

Open in browser:

```
http://YOUR_DOMAIN
```

Complete the web-based onboarding to finish setup.

---

## 📄 License

Licensed under the **GNU Affero General Public License (AGPLv3)**.
