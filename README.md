# E-commerce API

This is the backend RESTful API for the [E-commerce-front](https://github.com/wailman24/E-commerce-front) project—a modern e-commerce platform focused on providing the best deals on electronics in Algeria. This API is built with Laravel and supports multi-role authentication, product management, order processing, reviews, payments, and seller dashboards.

## Features

- User authentication (register, login, roles: client, seller, admin)
- Product management (CRUD, status, categories, images, search, recommendations)
- Order and order item management
- Wishlist support
- Product reviews and ratings
- Seller dashboard and earnings tracking
- Admin management endpoints
- Payment integration (PayPal, pay on delivery)
- Feedback system
- Recommendation system (content-based and collaborative)
- Secure API with Laravel Sanctum

## Tech Stack

- **Framework:** Laravel (PHP)
- **Database:** MySQL (or other supported by Laravel)
- **Authentication:** Laravel Sanctum
- **Payments:** PayPal API integration
- **Other:** Eloquent ORM, RESTful endpoints

## Prerequisites

- PHP >= 8.0
- Composer
- MySQL or compatible database
- Node.js & npm (for Laravel Mix/assets, if needed)

## Setup Instructions

1. **Clone the repo:**
   ```bash
   git clone https://github.com/wailman24/E-commerce-api.git
   cd E-commerce-api
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Copy and configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env and set database, mail, and PayPal credentials
   ```

4. **Generate app key:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations and (optional) seeders:**
   ```bash
   php artisan migrate
   # php artisan db:seed
   ```

6. **Serve the API:**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`.

## API Overview

- **Auth:** `POST /api/register`, `POST /api/login`, `POST /api/logout`
- **Products:** CRUD via `/api/addproduct`, `/api/updateproduct/{id}`, `/api/deleteproduct/{id}`, `/api/getproduct/{id}`, `/api/getvalidproducts`
- **Categories:** `/api/getallcategory`, `/api/addcategory`, `/api/updatecategory/{id}`
- **Orders:** `/api/allorders`, `/api/order_item`, `/api/dec/{order_item}`, `/api/inc/{order_item}`
- **Wishlist:** `/api/wishlist`, `/api/wishlist/add`, `/api/existinwishlist/{product}`
- **Reviews:** `/api/addreview/{productId}`, `/api/reviews/{productId}`
- **Payment:** `/api/payment`, `/api/paymentondelivery/{id}`, `/api/payment/success`, `/api/payment/cancel`
- **Recommendations:** `/api/recommendations/content/{productID}`, `/api/recommendations/users/{UserID}`

> For full API details, see the `routes/api.php` file.

## Payment Integration

- Configure PayPal credentials in `.env` using keys from `config/paypal.php`
- Supports both sandbox and live modes

## Project Structure

- `app/Http/Controllers/` — Main API controllers
- `routes/api.php` — Main API route definitions
- `app/Models/` — Eloquent models
- `app/Http/Resources/` — Transformers for API responses

## Related Projects

- **Frontend:** [wailman24/E-commerce-front](https://github.com/wailman24/E-commerce-front)

## License

[MIT](LICENSE)

---

> Built with ❤️ using Laravel.
