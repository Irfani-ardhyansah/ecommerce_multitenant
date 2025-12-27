# Multi-Tenant E-Commerce API (Laravel 11)

A robust Multi-Database Tenancy backend built with Laravel 11 and Stancl/Tenancy. This project provides a complete infrastructure for SaaS e-commerce with isolated databases for each store (tenant).

## 🚀 Key Features

- **Multi-Database Tenancy**: Automatic database creation and isolation for each tenant.
- **Central Admin (Landlord)**: Full CRUD for managing Tenants (Create, Read, Update, Delete stores).
- **Tenant Admin (Store Owner)**: Full CRUD for managing products within their specific store.
- **Tenant Customer**: Shopping cart features with automatic stock reservation logic.
- **Role-Based Authorization**: Distinct access levels for 'Admin' and 'User' roles.
- **API Documentation**: Automated Swagger/OpenAPI 3.0 documentation.
- **Automated Testing**: Integrated Unit & Feature tests with environment isolation.

---

## 🛠 Installation Guide

Follow these steps to set up the project on your local machine.

### 1. Configure Local Domains
Add the tenant domains to your local hosts file to enable subdomain routing.

* **Mac/Linux**: `sudo nano /etc/hosts`
* **Windows**: Run Notepad as Admin -> `C:\Windows\System32\drivers\etc\hosts`

Add the following lines:
```text
127.0.0.1   localhost
127.0.0.1   tenant1.localhost
127.0.0.1   tenant2.localhost
```
### 2. Install Dependencies
```text
composer install
```

### 3. Database Preparation

Create two empty MySQL databases:

central_db (Development)

central_test (Testing)

### 4. Environment Configuration

#### A. Development (.env)
```text
cp .env.example .env
```
Update your credentials:

DB_DATABASE=central_db

TENANCY_DATABASE_PREFIX=tenant

#### B. Testing (.env.testing)
```text
cp .env .env.testing
```

Update for isolation:

APP_ENV=testing

DB_DATABASE=central_test

TENANCY_DATABASE_PREFIX=test_tenant_

### 5. Initialize Application
```text
php artisan key:generate
php artisan migrate:fresh --seed
```

## 📖 API Documentation
```text
./api_documentation.postman_collection.json
```

## 🧪 Running Tests
```text
php artisan test --env=testing
```
