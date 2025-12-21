# XESSGLOBAL-Productapp
A Laravel-based product management application with REST API support, JWT authentication, and a web interface.
## Installation

1. **Clone the repository**

git clone https://github.com/web-blaster/XESSGLOBAL-Productapp.git
cd XESSGLOBAL-Productapp

#Install PHP dependencies

composer install

#Install Node.js dependencies

npm install

#Copy .env file

cp .env.example .env

#set your application key:

php artisan key:generate

#Set your .env database credentials:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xess_global_product_app
DB_USERNAME=root
DB_PASSWORD=

#Run migrations:

php artisan migrate

#Optionally, seed the database:

php artisan db:seed

#Run Vite for assets

npm run dev


#Start Laravel development server

php artisan serve

#Assets

All project assets (database and API collection) are available inside the assets folder.

Make sure to run npm run dev to compile CSS/JS using Vite.

#User this email and password to access the system 
'email'  - admin@example.com
'password' - password123