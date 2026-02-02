---
sidebar_position: 1
---

# Introduction

Technical documentation for the **Orange Absence System**. This system is built to handle mission-critical attendance tracking and financial management for divisional organizations.

## Technical Philosophy

This project follows a "Strict & Transparent" philosophy:
1. **Explicit over Implicit**: We avoid magic methods where explicit code provides better IDE support and readability.
2. **Database First**: Relationships are enforced at the database level (foreign keys) to prevent data corruption.
3. **Type Safety**: Using PHP 8.3 features like constructor property promotion and strict typing throughout the backend.

## Key Modules

### 1. Attendance Engine
Leverages QR code scanning with real-time geofencing. It handles latitude/longitude verification to ensure users are physically present at the eskuls location.

### 2. Financial Automation
The system automatically calculates "Kas" (weekly dues). It tracks who hasn't paid and provides an audit log for all cash transactions.

### 3. Secretary Dashboard
A high-performance admin panel built with FilamentPHP, allowing secretaries to manage hundreds of records with ease.

## Getting Started

If you are a developer looking to contribute:
1. Clone the repository.
2. Run `./deploy.sh` to setup the environment.
3. Use `php artisan serve` to start the local server.

:::warning Production Note
Always ensure that `APP_URL` in `.env` is correctly set to your production domain, otherwise QR icons and storage links will break.
:::
