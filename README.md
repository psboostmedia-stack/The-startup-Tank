# The Startup Tank - PHP System

This is a complete student registration and portal system built with PHP and MySQL.

## Folder Structure
- `index.php`: Main landing page with registration modal.
- `login.php`: Student login portal.
- `profile.php`: Student personal feed and profile.
- `register_action.php`: Logic for handling student registration.
- `admin_login.php`: Admin login page.
- `admin_dashboard.php`: Admin area to manage students and post zoom links.
- `logout.php`: Session logout script.
- `db.php`: Database connection configuration.
- `database.sql`: SQL script to import into phpMyAdmin (MySQL).
- `assets/css/style.css`: Main stylesheet.

## Deployment Instructions (Hostinger)
1. **Database Setup:**
   - Log in to your Hostinger hPanel.
   - Go to **Databases** -> **MySQL Databases**.
   - Create a new database named `startup_tank_db`.
   - Open **phpMyAdmin** for that database.
   - Click on **Import** and select the `database.sql` file provided.

2. **File Upload:**
   - Go to **Files** -> **File Manager**.
   - Navigate to `public_html`.
   - Upload all the files and the `assets` folder.

3. **Configure Connection:**
   - Open `db.php`.
   - Update `$username` and `$password` with your Hostinger database credentials.
   - The `$dbname` should match the name you created.

4. **Default Admin:**
   - Username: `admin`
   - Password: `admin123`
   - (You can change this in the `admins` table using phpMyAdmin).

## Note on AI Studio Preview
The preview in AI Studio is a static representation of the layout. PHP logic (like database operations) requires a LAMP stack (like Hostinger) to function fully.
