# PHP_Laravel12_Generate_Daily_Database_backup_Automatically

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel">
  <img src="https://img.shields.io/badge/Database-MySQL-blue?style=for-the-badge&logo=mysql">
  <img src="https://img.shields.io/badge/Backup-Automatic-success?style=for-the-badge">
  <img src="https://img.shields.io/badge/Windows-Task%20Scheduler-informational?style=for-the-badge&logo=windows">
</p>

---

##  Overview

This documentation explains how to **automatically create a daily MySQL database backup** in **Laravel 12**
using:

- Custom Artisan Command  
- Laravel Scheduler  
- Windows Task Scheduler (cron alternative for Windows)

This setup is specially useful for **Windows + XAMPP users**.

---

##  Features

- Automatic daily database backup  
- Custom Artisan command  
- Laravel Scheduler support  
- Windows Task Scheduler integration  
- Secure database access using `.env`  

---

##  Folder Structure

```
app/
├── Console/
│   └── Commands/
│       └── DatabaseBackUp.php

routes/
└── console.php

storage/
└── app/
    └── backup/
        └── backup-YYYY-MM-DD.sql

.env
README.md
```

---

##  Step 1 — Install Laravel 12

Open terminal / PowerShell and run:

```bash
composer create-project laravel/laravel laravel-backup
```

---

##  Step 2 — Configure Database (.env)

Open `.env` file and add your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

 These values will be used by `mysqldump` to take the backup.

---

##  Step 3 — Create Backup Command

Create a custom Artisan command:

```bash
php artisan make:command DatabaseBackUp
```

This will create the file:

```
app/Console/Commands/DatabaseBackUp.php
```

---

##  Step 4 — Add Backup Logic (Windows + XAMPP)

Update `DatabaseBackUp.php` with the following code:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackUp extends Command
{
    /**
     * Command name used in terminal
     * Example: php artisan database:backup
     */
    protected $signature = 'database:backup';

    /**
     * Command description
     */
    protected $description = 'Create daily database backup';

    /**
     * Execute the console command
     */
    public function handle()
    {
        // Backup file name
        $filename = 'backup-' . now()->format('Y-m-d') . '.sql';

        // Backup directory
        $backupPath = storage_path('app/backup');

        // Create directory if not exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // mysqldump path (update as per your XAMPP version)
        $mysqldumpPath = 'C:\\xampp8.2\\mysql\\bin\\mysqldump.exe';

        // Build Windows compatible command
        $command = ""{$mysqldumpPath}" --user=" . env('DB_USERNAME') .
                   " --password=" . env('DB_PASSWORD') .
                   " --host=" . env('DB_HOST') .
                   " " . env('DB_DATABASE') .
                   " > "{$backupPath}\\{$filename}"";

        // Execute command
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Database backup created successfully!');
        } else {
            $this->error('Database backup failed!');
        }
    }
}
```

---

##  Step 5 — Create Backup Folder

If not created automatically, create it manually:

```bash
mkdir storage/app/backup
```

Folder structure:

```
storage/
└── app/
    └── backup/
```

---

##  Step 6 — Test Backup Command Manually

Run:

```bash
php artisan database:backup
```

 Expected output:

```
Database backup created successfully!
```
<img width="842" height="121" alt="Screenshot 2025-12-15 122043" src="https://github.com/user-attachments/assets/68cdfbb4-b884-4978-8232-3f5624d18ebd" />


Backup file location:

```
storage/app/backup/backup-YYYY-MM-DD.sql
```

---

##  Step 7 — Schedule Command in Laravel

Open:

```
routes/console.php
```

Add:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('database:backup')->daily();
```

 This tells Laravel to run the backup command daily.

---

##  Important (Windows Users)

- `crontab -e`  Not used on Windows  
- Cron works only on Linux servers  

 On Windows, we use **Windows Task Scheduler**

---

##  Step 8 — Setup Windows Task Scheduler

### 🔹 Open Task Scheduler

Press **Win + R**  
Type:

```
taskschd.msc
```
<img width="397" height="204" alt="Screenshot 2025-12-15 124639" src="https://github.com/user-attachments/assets/ef248bcc-7d95-4f7f-8dee-f653e4280906" />


Press **OK**

---

### 🔹 Create New Task

Click **Create Basic Task…**

<img width="781" height="561" alt="Screenshot 2025-12-15 122956" src="https://github.com/user-attachments/assets/a8827ac5-52da-4e04-829c-70c4db3b6fdc" />


**Task Name**
```
Laravel Daily Database Backup
```

**Description**
```
Automatically creates daily MySQL database backup using Laravel Artisan
```
<img width="695" height="487" alt="Screenshot 2025-12-15 123051" src="https://github.com/user-attachments/assets/a90af70b-3538-4309-ac40-d256cf04817b" />

---

### 🔹 Trigger

Choose:
```
Daily
```
<img width="694" height="484" alt="Screenshot 2025-12-15 123134" src="https://github.com/user-attachments/assets/2cb7327e-78ba-4161-856c-1d38014897d6" />


Set time example:
```
12:40 PM
```
<img width="694" height="489" alt="Screenshot 2025-12-15 123233" src="https://github.com/user-attachments/assets/0b7a2ef7-dfeb-49f9-8ce7-f4121fef7e03" />

---

### 🔹 Action

Choose:
```
Start a program
```
<img width="697" height="489" alt="Screenshot 2025-12-15 123301" src="https://github.com/user-attachments/assets/c1106c00-1cf6-4cde-b327-c953c545696d" />

---

### 🔹 Program & Arguments (MOST IMPORTANT)

**Program / Script**
```
C:\xampp8.2\php\php.exe
```

**Add arguments**
```
artisan database:backup
```

**Start in**
```
C:\xampp8.2\htdocs\PHP_Laravel12_Generate_Daily_Database_backup_Automatically
```
<img width="696" height="486" alt="Screenshot 2025-12-15 123422" src="https://github.com/user-attachments/assets/fa8c655f-684d-4915-92b0-5b4b1c379516" />

---

### 🔹 Finish

✔ Review details  
✔ Click **Finish**  

---
<img width="694" height="499" alt="Screenshot 2025-12-15 123454" src="https://github.com/user-attachments/assets/4caa0ddc-e4a9-43d3-a2eb-bc6be5737d2e" />


##  Test Task Manually

Right-click the task → **Run**

<img width="787" height="563" alt="Screenshot 2025-12-15 123558" src="https://github.com/user-attachments/assets/49feacb9-c3bf-495d-8b44-33b9d592520c" />


###  Expected Result

- No error popup  
- New backup file created in:

```
storage/app/backup/
```
<img width="242" height="84" alt="Screenshot 2025-12-15 124109" src="https://github.com/user-attachments/assets/7c73d03a-69bc-4e01-861c-0c57b70cad0e" />

---

##  How This Works (Simple)

1. Windows Task Scheduler runs PHP  
2. PHP executes Artisan command  
3. Artisan runs `mysqldump`  
4. Database backup saved safely  

---

##  Conclusion

✔ Fully automated daily database backup  
✔ No Linux cron required  
✔ Perfect for Windows + XAMPP  
✔ Safe & reliable solution  

---
