# I3-Solutions-Payment-App
A Laravel-based product management application with REST API support, JWT authentication, and a web interface.
## Installation

1. **Clone the repository**

git clone https://github.com/web-blaster/I3SOLUTIONS-Payment-App.git
cd I3SOLUTIONS-Payment

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
DB_DATABASE=i3_solutions
DB_USERNAME=root
DB_PASSWORD=

#Run migrations:

php artisan migrate

#Optionally, seed the database:

php artisan db:seed


#Start Laravel development server

php artisan serve

#Assets

All project assets (database and API collection) are available inside the assets folder.

Make sure to run npm run dev to compile CSS/JS using Vite.

#User this email and password to access the system 
'email'  - admin@example.com
'password' - password123


Notes : 
Infrastructure & Cost Notes 
I deployed the app on Amazon ECS with self-managed EC2 instances (EC2 launch type) to keep infrastructure costs minimal and maintain full control.

I intentionally avoided AWS Fargate, since the Free Tier had expired and I wanted predictable, lower running costs.

AWS Security Practices

I never use the AWS root account for development or deployments.

For local development/testing, I create a temporary IAM user with limited permissions (only what’s needed for S3/SQS).

In ECS, the application relies only on IAM roles (task role / execution role), so no AWS access keys are stored in code or containers.

After review/testing, I delete the temporary IAM user to fully revoke access.

File Upload Handling

Current uploads work through the backend with access control.

For very large files or poor network conditions, the upload flow can be extended using S3 pre-signed URLs (client uploads directly to S3).

Exchange Rate & Scheduling

Laravel Scheduler runs daily commands to fetch exchange rates.

Exchange rates are stored daily in the database for consistency and auditing.

If a rate for the day is already stored, the system uses the stored value instead of calling the external API again.

Unit tests are included for:

exchange rate API calls

exchange rate calculation logic

Code Structure & Standards

The project uses a clean structure with:

Jobs (background processing)

Service classes (business logic)

Middleware (request filtering/security)

Support / Helper classes (reusable utilities)

Rate Limiting

Rate limiting is implemented for:

Login

File uploads
to reduce abuse and protect system resources.

CI/CD (AWS)

CI/CD is automated using AWS CodePipeline and AWS CodeBuild.

CodePipeline handles the end-to-end flow (source → build → deploy).

CodeBuild builds the Docker image, tags it (commit + latest), and pushes it to Amazon ECR.

The ECS service is then updated to deploy the new image automatically (rolling deployment).


