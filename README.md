## Project Title:

Fit Progresso App: “Your Ultimate Fitness Tracking Companion"

## Project Description

The Fit Progresso App is a digital fitness companion designed to help users track their workouts, monitor their progress, and achieve their fitness goals. Whether you're a beginner or a seasoned athlete, this app provides tools to log workouts, manage exercises, and track physical measurements. With features like workout templates, detailed progress tracking, and integration with Google OAuth, Fit Progresso aims to support and enhance every step of your fitness journey.

## Table of Contents

1.  Project Overview

2.  Features

3.  Technologies Used

4.  Requirements

5.  Installation Instructions

6.  Running the Project

7.  Usage Instructions

8.  API Documentation

9.  Contributing

10.  Credits

11.  License

12.  Conclusion


## Project Overview

The Fit Progresso App is built using Laravel for the backend and MySQL for the database. It supports user authentication via JWT and Google OAuth. The app includes features for workout tracking, exercise management, and measurement tracking. Admins have additional privileges to manage content and users.



## Features

-   User Authentication: Sign up, log in, and authenticate via JWT tokens, Google OAuth.

-   Profile Management: View and update personal information and fitness data.

-   Exercise Management: Browse, create, update, delete and search exercise, view the workouts history, records for a particular exercise.

-   Workout Tracking: Log workouts, including multiple exercises and sets. Track performance metrics, best sets and personal records.

-   Workout Templates: Use or create workout templates tailored to specific fitness goals.

-   Measurement Tracking: Record and monitor physical measurements over time.

-   Admin Functions: Manage users, exercises, and templates.


For detailed Feature List, refer to [Feature List Documentation](https://docs.google.com/document/d/1sELVDJI9iLoh_VYopjCZolji0wKA7aZy0EJFb4YUMAY/)

## Technologies Used

-   Backend: Laravel (PHP)

-   Database: MySQL

-   Authentication: JWT, Google OAuth 2.0

-   Frontend: (To be implemented soon)




## Requirements

-   PHP: 7.4 or higher

-   Composer: Dependency manager for PHP

-   MySQL: 5.7 or higher

-   Laravel: 8.x or higher


## Installation Instructions

1.  Clone the Repository  
    Clone the project repository to your local machine: `git clone https://github.com/Shahriar075/Fit-Progresso-App.git`

2.  Navigate to the Project Directory  
    Change into the project directory: `cd Fit-Progresso-App`

3.  Install Dependencies  
    Install the necessary PHP dependencies: `composer install`

4.  Create and Configure the .env File  
    Copy the `.env.example` file to `.env` and configure your environment variables according to your setup.

5.  Generate Application Key  
    Generate a new application key. This key is used for encryption and securing your application's data: `php artisan key:generate`

6.  Run Migrations  
    Execute database migrations to create the necessary database tables: `php artisan migrate`


## Running the Project

1.  Start the Development Server  
    Start the Laravel development server: `php artisan serve`

2.  Access the Application  
    Open your web browser and navigate to the local development URL.


## Usage Instructions

1.  Sign Up / Log In  
    Use the registration form to create a new account or log in using your credentials or Google account.

2.  Profile Management  
    Update your profile information and manage your fitness data from the profile page.

3.  Manage Exercises  
    Browse predefined exercises, create your own, or modify existing ones, search exercises, view the workouts history, records for a particular exercise.

4.  Track Workouts  
    Log your workouts, including details of exercises and sets. View and edit past workout logs, view the personal record, best set achieved.

5.  Use Workout Templates  
    Access predefined workout templates or create custom templates tailored to your fitness goals.

6.  Track Measurements  
    Record and monitor physical measurements over time.


## API Documentation

For detailed API documentation, refer to [API Documentation](https://docs.google.com/document/d/1b_KR_X51zlmBS_KqMAWgqX0kZ8WganiTqKtxjMQIJtE) .

## Contributing

We welcome contributions to improve the Fit Progresso App. To contribute:

1.  Fork the repository.

2.  Create a new branch for your changes.

3.  Commit your changes with clear messages.

4.  Push your branch to your forked repository.

5.  Open a pull request with a description of your changes.




## Credits

-   Laravel: PHP framework used for backend development.

-   MySQL: Database system used for data storage.

-   Google OAuth: Authentication provider.

-   All Contributors: Thank you for your contributions and support!




## License

This project is licensed under a Custom License. All rights reserved.



## Conclusion

The Fit Progresso App is designed to be a comprehensive fitness tool, providing users with a powerful and flexible platform to track their workouts, manage their exercise routines, and monitor their progress. By leveraging modern technologies like Laravel and Google OAuth, the app aims to deliver a seamless user experience and robust functionality. Whether you’re looking to achieve personal fitness goals or manage a large community of users, Fit Progresso offers the features and flexibility needed to support your journey.

We hope you find the Fit Progresso App useful and encourage you to contribute to its development. Your feedback and contributions are invaluable as we strive to make the app better for everyone. If you have any questions or need further assistance, please don’t hesitate to reach out.

Thank you for using Fit Progresso, and we wish you the best on your fitness journey!