# API Integration

Orange Absence provides a robust API for external integrations and the mobile portal.

## Authentication Guide

API requests require session-based or token-based authentication depending on the client.

### For Mobile Portal
The mobile portal uses Laravel Sanctum's stateful cookie authentication.
1. `GET /sanctum/csrf-cookie` (initialize session)
2. `POST /login` (submit credentials)

## Core Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/portal` | Fetch user dashboard and active schedules. |
| `POST` | `/attendance/scan` | Submit QR payload and GPS coordinates. |
| `GET` | `/member/payments` | Fetch personal financial history. |

## Documentation as Code

We use `L5-Swagger` (OpenAPI 3.0). The documentation is generated directly from **PHP Attributes** in the Controllers.

### View Interactive Swagger UI
You can access the interactive documentation on your local development server:
- URL: `http://localhost:8000/api/documentation`

:::tip Advice: Integration Security
Never expose your `APP_KEY` or `JWT_SECRET`. External apps should use a dedicated Service Account with limited permissions.
:::

## Error Handling
The API returns standard HTTP status codes:
- `200`: Success
- `401`: Unauthorized (Login required)
- `403`: Forbidden (e.g., Geofence failed)
- `422`: Validation Error (e.g., Missing GPS data)
