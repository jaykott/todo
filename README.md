# Task Management Application

A modern task management application built with Laravel and Vue.js (Inertia.js). Manage your tasks with drag-and-drop reordering, due dates, completion tracking, and more.

## Features

- ✅ Create, edit, and delete tasks
- 📅 Set due dates for tasks (required field)
- ✅ Mark tasks as complete/incomplete
- 🎨 Visual indicators:
  - Light green background for completed tasks
  - Light pink background for overdue tasks
  - Light blue background while dragging
  - Yellow background for drop targets
- 🔄 Drag and drop to reorder tasks
- 📱 Responsive design with Tailwind CSS

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x and npm
- MySQL or compatible database
- Laravel 11.x

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/jaykott/todo.git
   cd task
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Update database configuration in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```
   This will create the database tables and seed 10 sample tasks.

## Running the Application

### Development Mode

To test this site, run the development server:

```bash
composer run dev
```

This command will start both the Laravel development server and the Vite development server for hot module replacement.

Alternatively, you can run them separately:

**Terminal 1 - Laravel server:**
```bash
php artisan serve
```

**Terminal 2 - Vite dev server:**
```bash
npm run dev
```

### Production Build

For production, build the assets:

```bash
npm run build
```

Then serve the application:
```bash
php artisan serve
```

## Accessing the Application

Once the servers are running, visit:
- **Application**: http://localhost:8000
- The application will display the Tasks page by default

## Database Seeding

To populate the database with sample tasks:

```bash
php artisan db:seed --class=TaskSeeder
```

Or to reset and reseed:

```bash
php artisan migrate:fresh --seed
```

## Project Structure

- `app/Http/Controllers/Api/TaskController.php` - API endpoints for tasks
- `app/Models/Task.php` - Task model
- `database/migrations/` - Database migrations
- `database/seeders/TaskSeeder.php` - Task seeder
- `resources/js/Pages/Tasks.vue` - Main tasks page component
- `routes/api.php` - API routes
- `routes/web.php` - Web routes

## API Endpoints

- `GET /api/tasks` - Get all tasks
- `POST /api/tasks` - Create a new task
- `PATCH /api/tasks/{id}` - Update a task
- `DELETE /api/tasks/{id}` - Delete a task
- `POST /api/tasks/reorder` - Reorder tasks

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
