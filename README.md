# I3-Solutions-Payment-App
A Laravel-based product management application with REST API support, JWT authentication, and a web interface.
## Installation

1. **Clone the repository**

git clone https://github.com/web-blaster/I3SOLUTIONS-Payment-App.git
cd I3SOLUTIONS-Payment

# Install PHP dependencies

composer install

# Install Node.js dependencies

npm install

### Environment Configuration

This project uses environment variables for configuration.
Create a `.env` file based on `.env.example`.

#set your application key:

php artisan key:generate

#Set your .env database credentials:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=i3_solutions
DB_USERNAME=root
DB_PASSWORD=

#Run migrations:

php artisan migrate

#Optionally, seed the database:

php artisan db:seed


# Start Laravel development server

php artisan serve

#Assets

All project assets (database and API collection) are available inside the assets folder.

Make sure to run npm run dev to compile CSS/JS using Vite.

#User this email and password to access the system 
'email'  - admin@example.com
'password' - password123

