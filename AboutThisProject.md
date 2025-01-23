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
