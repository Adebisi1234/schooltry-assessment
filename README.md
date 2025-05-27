# SchoolTry Assessment - Ed-tech Platform

This repository contains the Ed-tech platform, an AI-powered learning platform for teachers and students to create, share, and learn from each other.

## Features

- Document management system
- User authentication (teachers and students)
- AI-powered learning experience with personalized content recommendations
- Chatbot assistance

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+ and npm
- MySQL or PostgreSQL

## Installation

### Clone the repository

```bash
git clone https://github.com/yourusername/schooltry-assessment.git
cd schooltry-assessment
```

### Install dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Environment Configuration

Copy the example environment file and configure it for your local setup:

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials and other configurations:

### Database Setup

```bash
# Run migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed
```

The seeder will create a test user with the following credentials:

- Email: test@example.com
- Password: password
- Role: teacher

### File Storage Setup

```bash
# Create symbolic link for storage
php artisan storage:link
```

## Running the Application

### Development Mode

Start the development server:

```bash
composer run dev

```

Access the application at http://localhost:8000

### Production Build

For production deployment:

```bash
# Build assets
npm run build

# Start the server
php artisan serve
```

## Project Structure

- `/resources/js/pages` - Vue.js pages
- `/resources/js/components` - Vue.js components
- `/app/Models` - Laravel models
- `/app/Http/Controllers` - Laravel controllers
- `/database/seeders` - Database seeders
- `/content` - Default content files for document seeding

## Testing

```bash
# Run PHP tests
php artisan test

# Run JavaScript tests (if applicable)
npm test
```
