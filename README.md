# NovaTrust Financial Services

NovaTrust is a premium, secure, and modern financial services web application built with raw PHP 8, focusing on high performance, security, and real-time features.

## Requirements

- PHP 8.2 or higher
- MySQL 8.0 or MariaDB equivalent
- Composer (for dependencies)

## Installation & Setup

1. **Clone the repository and install dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   Copy the example environment file and configure your local settings.
   ```bash
   cp .env.example .env
   ```
   *Make sure to configure your `DB_*` credentials, generate a secure `APP_ENCRYPTION_KEY`, and provide your `PUSHER_*` keys for real-time chat functionality.*

3. **Database Setup**
   Ensure your database server is running and the database specified in `.env` exists.

   **Run Migrations:**
   Executes all pending SQL migrations to build the database schema.
   ```bash
   php database/migrate.php migrate
   ```

   **Check Migration Status:**
   View which migrations have been applied.
   ```bash
   php database/migrate.php migrate:status
   ```

   **Seed the Database:**
   Populate the database with default administrator accounts and initial configuration rules (like the ChatBot rules).
   ```bash
   php database/migrate.php seed
   ```
   *(You can also run a specific seeder by appending the class name: `php database/migrate.php seed AdminSeeder`)*

4. **Start the Development Server**
   Start the built-in PHP development server, pointing the document root to the `public/` directory.
   ```bash
   php -S localhost:8000 -t public
   ```
   The application will now be accessible at `http://localhost:8000`.

## Architecture & Features

- **Progressive Web App (PWA):** Installable on Android/iOS via the browser with a network-first caching strategy.
- **Real-Time Live Chat:** Fully functional live chat widget for users and an agent dashboard for administrators, powered by Pusher WebSockets.
- **Server-Side Rendered:** Ultra-fast page loads using standard PHP output buffering and partial layouts.
- **Custom ORM:** Lightweight, custom database repository pattern using raw PDO.

## Directory Structure

- `app/` - Core application logic, Controllers, Models, Repositories, and Services.
- `database/` - Migrations and Seeders.
- `public/` - The document root containing `index.php`, CSS, JS, images, and PWA assets.
- `resources/` - HTML Views, layouts, and email templates.
- `routes/` - API and Web routing definitions.
- `storage/` - Logs and file uploads.
