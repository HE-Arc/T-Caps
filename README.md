<p align="center"><a href="https://tcaps.k8s.ing.he-arc.ch/" target="_blank"><img src="https://github.com/HE-Arc/T-Caps/blob/main/public/source/assets/images/logo.png?raw=true" width="400" alt="Laravel Logo"></a></p>

## About T-Caps

T-Caps is a project developed by students of the HE-Arc engineering school in Switzerland. The goal of this project is to create a web application that allows users to send message and document with the possibility to set an opening date and time. The message will be sent only when the opening date and time is reached.

## Launch the project locally

### Prerequisites

- PHP 8.2.12 or higher
- Composer
- Node.js
- Tested with MariaDB 10.4.28 or higher

### How to start

1. Clone the repository
2. Install the dependencies
    ```bash
    composer install
    npm install
    ```
3. Create a `.env` file by copying the `.env.example` file, and edit the configuration as needed
    ```bash
    cp .env.example .env
    ```
4. Generate the application key
    ```bash
    php artisan key:generate
    ```

5. Migrate and seed the database
    ```bash
    php artisan migrate:fresh --seed
    ```

6. Link the storage
    ```bash
    php artisan storage:link
    ```
   
6. Start the servers
    ```bash
    php artisan serve
    npm run dev
    ```
