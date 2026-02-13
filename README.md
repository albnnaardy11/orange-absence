# Orens Absence Architecture
# Read this before touching the code.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3-F28D15.svg)](https://filamentphp.com)
[![Build Status](https://img.shields.io/github/actions/workflow/status/albnnaardy11/orens-absence/deploy-docs.yml?branch=main)](https://github.com/albnnaardy11/orens-absence/actions)
[![Docs](https://img.shields.io/badge/Docs-Latest-success)](https://albnnaardy11.github.io/orens-absence)

## Why This Exists
This is an attendance system. It tracks who is here and who isn't.
It uses **Laravel 11** because we need stability, not your weekend framework experiment.
It uses **Filament** because writing admin panels by hand is a waste of time.

## Architecture
*   **Backend**: Laravel 11. Strict typing. No magic methods where explicit code works better.
*   **Admin**: FilamentPHP.
*   **API**: REST with OpenAPI 3.0 specs (`l5-swagger`).
*   **Docs**: Docusaurus, hosted on GitHub Pages. Architecture-as-Code.

## Quick Start
Don't ask questions. Just run this.

```bash
# 1. Clone
git clone https://github.com/albnnaardy11/orens-absence.git

# 2. Setup (Magic script, handles env, keys, migration)
./deploy.sh 

# 3. Serve
php artisan serve
```

## API Specifications
The API is documented using standard OpenAPI 3.0.
*   **Local**: `/api/documentation` (if enabled)
*   **Live**: [Documentation Site](https://albnnaardy11.github.io/orens-absence)

## Contributing
1.  **Strict Types Only**: If you touch a file and don't declare types, I will reject your PR.
2.  **No Fluff**: Logic goes in Services or Actions. Controllers are for routing.
3.  **Tests**: If it breaks, you fix it.

## Deployment
Run `./deploy.sh` on the server. It detects production and optimizes everything.
Run `./update-docs.sh` to fix the documentation if you broke it.