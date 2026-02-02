# API Documentation

## Overview
Orange Absence provides a REST API for core authentication and potentially external integrations. The API is documented using **OpenAPI 3.0 (Swagger)**.

## Accessing the Docs
- **Swagger UI**: Visit `/api/documentation` on your local server.
- **Static JSON**: The specification is synced to `static/api/swagger.json` in this documentation site.

## How to Update
API Documentation is "Code-as-Docs". To update:
1.  Annotate your Controllers using PHP 8 Attributes (`#[OA\Get]`, etc.).
2.  Run `./deploy.sh` or `php artisan l5-swagger:generate`.
3.  The documentation site will automatically pick up changes on the next push.
