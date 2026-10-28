
# WhatNow

WhatNow is a web application built with Laravel 13 and Vue.js that provides disaster preparedness guidance. This document explains how to install and configure the project in your development environment.

## Prerequisites

Make sure the following tools are installed on your system:

- **PHP (version 8.3.36 or later, with the security patch for CVE-2026-109618 and CVE-2026-109619):**
  ```bash
  php -v
  ```
- **Composer (PHP dependency manager):**
  ```bash
  composer --version
  ```
- **Node.js (recommended version 12 or later):**
  ```bash
  node -v
  ```
- **npm (Node.js package manager):**
  ```bash
  npm -v
  ```
- **MySQL (version 5.7 or later):**
  ```bash
  mysql --version
  ```

## Project Installation

### Clone the Repository

```bash
git clone https://github.com/YourUsername/repository.git
cd repository
```

### Install PHP Dependencies

```bash
composer install
```

### Install JavaScript Dependencies

```bash
npm install
```

### Environment Configuration

1. Copy the example environment file to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Generate a Laravel application key:
   ```bash
   php artisan key:generate
   ```

### Database Configuration

1. Create the MySQL databases, for example `redcross-local` and `redcross_log`.

2. Open `.env` and update the database credentials for your environment.

3. Run the database migrations:
   ```bash
   php artisan migrate
   ```

4. Optional: Run the following command to populate the database with initial sample data:
   ```bash
   php artisan db:seed
   ```

### Start the Development Server

Run the following command to start the Laravel development server:
```bash
php artisan serve --port=8001
```

## Additional Configuration

1. If the `storage/framework` directory or its `cache` and `sessions` subdirectories are missing, create them so Laravel can run correctly.

2. Update the following settings in `.env` to connect the API and the backoffice:

   ```dotenv 
   RCN_API_VERSION=v1
   RCN_API_URL=http://127.0.0.1:8001
   RCN_API_USER=admin
   RCN_API_PASSWORD=password
   ```

3. In the backoffice, run the following commands to build and serve the application:

   ```bash
   npm run hot
   php artisan serve
   ```

The API and the backoffice are now connected and ready to use.
