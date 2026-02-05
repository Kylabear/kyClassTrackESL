# Quick Start Guide - ESL Class Schedule Tracker

## Step-by-Step Instructions to Run the System

### Step 1: Start MySQL in XAMPP
1. Open **XAMPP Control Panel**
2. Click **"Start"** button next to **MySQL**
3. Wait until it shows "Running" (green)

### Step 2: Create the Database
**Option A: Using phpMyAdmin (Easiest)**
1. Open your browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **"New"** on the left sidebar
4. Database name: `kyclasstrack`
5. Click **"Create"**

**Option B: Using Command Line**
Open PowerShell/Command Prompt and run:
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS kyclasstrack;"
```

### Step 3: Run Database Migrations
Open PowerShell/Command Prompt in the project folder:
```bash
cd C:\xampp\htdocs\kyClassTrack-1
php artisan migrate
```

You should see:
```
INFO  Running migrations.
2026_02_02_000001_create_lessons_table  DONE
```

### Step 4: Start the Laravel Server
In the same terminal, run:
```bash
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000]
```

### Step 5: Open the Application
1. Open your web browser
2. Go to: **http://localhost:8000**
3. You should see the **Daily Schedule** page

---

## How to Check/Test the System

### ✅ Check 1: Daily Schedule Page
- **URL**: http://localhost:8000/schedule
- **What to see**:
  - Date picker at the top
  - Table with time slots from 2:00 PM to 11:30 PM
  - Each row shows: Time (12h/24h format), Student Name, Age, Notes
  - "Save Schedule" button at the bottom

### ✅ Check 2: Add a Test Schedule Entry
1. Fill in a student name (e.g., "John Doe")
2. Fill in age (e.g., "25")
3. Fill in notes (e.g., "Beginner level")
4. Click **"Save Schedule"**
5. You should see a green success message
6. Refresh the page - your entry should still be there

### ✅ Check 3: Monthly Report Page
- **URL**: http://localhost:8000/reports/monthly
- **What to see**:
  - Month picker at the top
  - Table showing each day of the month
  - Columns: Date, Day, Classes, Absent?, Daily Salary (PHP)
  - Totals at the bottom showing:
    - Total classes
    - Total absent days
    - Total monthly salary

### ✅ Check 4: Navigation
- Click **"Daily Schedule"** in the navbar → goes to schedule page
- Click **"Monthly Report"** in the navbar → goes to report page
- Click **"ESL Schedule"** logo → goes to schedule page

---

## Troubleshooting

### Error: "No connection could be made"
- **Solution**: Make sure MySQL is running in XAMPP Control Panel

### Error: "Database doesn't exist"
- **Solution**: Create the database `kyclasstrack` in phpMyAdmin

### Error: "Table doesn't exist"
- **Solution**: Run `php artisan migrate` again

### Page shows "404 Not Found"
- **Solution**: Make sure you're using `http://localhost:8000` (not port 80)

### Blank page or errors
- **Solution**: Check the terminal where `php artisan serve` is running for error messages

---

## Features to Test

1. **Change Date**: Use the date picker to view different days
2. **Save Multiple Entries**: Fill in several time slots and save
3. **View Monthly Report**: Go to monthly report and see your classes counted
4. **Salary Calculation**: Check that salary = number of classes × 60 PHP
5. **Absent Days**: Days with 0 classes should show "Yes" in Absent column

---

## System URLs

- **Home/Schedule**: http://localhost:8000
- **Daily Schedule**: http://localhost:8000/schedule
- **Monthly Report**: http://localhost:8000/reports/monthly

---

**That's it! Your ESL Class Schedule Tracker is ready to use! 🎉**
