# About This Project

This project is a robust Laravel backend for an e-commerce platform, developed as part of an academic initiative for **Damascus University**. It features JWT authentication, role-based access control, and comprehensive API documentation.

---

## Features

-   **E-Commerce Platform**: Backend for managing products, goods, and user interactions.
-   **Shopping Cart**: Users can add products to their cart and place orders.
-   **Order Management**:
    -   Orders are divided into sub-orders, each sent to the respective store.
    -   Sellers approve or reject sub-orders.
    -   Once all sub-orders are approved, the admin marks the order as "Ready for Delivery."
    -   After delivery, the order status is updated to "Delivered."
-   **JWT Authentication**: Secure token-based authentication for users and admins.
-   **Role-Based Access Control**: Two main roles - **User** and **Admin**.
    -   Admin role is further divided into **Seller** and **Admin**.
-   **Multiauth System**: Separate authentication for different roles.
-   **Custom Middleware & Roles**: Manual middleware and role management to differentiate between admin sections.
-   **Service Layer Architecture**: Logic is separated into a **Service Layer**, keeping controllers clean and focused.
-   **OTP Verification**: Send One-Time Passwords (OTP) via email for secure verification.
-   **Image Uploads**: Image management with three categories:
    -   User images
    -   Product images
    -   Goods images
-   **Database Seeders**: Predefined seeders for product statuses and types.
-   **Route Groups**: Organized API routes using **Route Groups** in `api.php`.
-   **Firebase Notifications**: Real-time notifications using **Firebase**.
-   **Localization**: Full application translation, including custom validation messages.
-   **API Documentation**: Comprehensive API documentation generated using **Swagger UI**.

---

## Application Workflow

### User Flow

1. **Browse Products**: Users can browse products and add them to their shopping cart.
2. **Place Order**: Users can place an order, which is then divided into sub-orders for each store.
3. **Order Status**:
    - Sub-orders are sent to respective sellers for approval.
    - Once all sub-orders are approved, the admin marks the order as "Ready for Delivery."
    - After delivery, the order status is updated to "Delivered."

### Admin Flow

1. **Admin Account**:
    - Default admin credentials:
        ```json
        {
            "name": "Admin",
            "password": "123456789"
        }
        ```
2. **Manage Users**:
    - Admins can add new admins or sellers.
    - When adding a seller, the seller is associated with their store.
3. **Manage Stores**:
    - Sellers can manage their stores and add products.
4. **Order Management**:
    - Admins oversee the entire order process, from approval to delivery.

---

## Project Documentation

For more details about the project, you can refer to the following files:

-   **Project Description**: [Programming Languages Project 2024 - 2025.pdf](Programming%20Languages%20Project%202024%20-%202025.pdf)
-   **ERD (Entity-Relationship Diagram)**: [Programming_Languages_24-25.drawio.pdf](Programming_Languages_24-25.drawio.pdf)

---

## Installation

1. **Clone the repository**:
    ```bash
    git clone https://github.com/Kareem-Bizreh/Programming_Languages_Backend_24-25.git
    cd Programming_Languages_Backend_24-25
    ```
2. **Install dependencies**:
    ```bash
    composer install
    ```

### Set up the environment file

````markdown
3. **Set up the environment file**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
````

### Configure your email settings in `.env`

````markdown
4. **Configure your email settings in `.env`**:
    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=your_smtp_host
    MAIL_PORT=your_smtp_port
    MAIL_USERNAME=your_smtp_username
    MAIL_PASSWORD=your_smtp_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=your_email@example.com
    MAIL_FROM_NAME="Your App Name"
    ```
````

### Run migrations and seeders

````markdown
5. **Run migrations and seeders**:
    ```bash
    php artisan migrate:fresh --seed
    ```
````

### API Documentation

```markdown
7. **API Documentation**:
   API documentation is available via Swagger UI. Access it by running the project and visiting:
   [http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation).
```

## Closing Notes

This project was developed as part of an academic initiative for Damascus University. It started as a set of ideas and evolved into a fully functional e-commerce backend. We hope you find this project enjoyable and useful! Feel free to explore, contribute, or use it as a foundation for your own applications.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
