# Attendance & Scheduling Guide

## Overview
This is the core of Orange Absence. It handles division schedules and automated QR code generation.

## How it Works
1.  **Schedules**: Admins define when a division meets.
2.  **QR Codes**: The system generates unique, time-sensitive verification codes based on the schedule.
3.  **Scanning**: Members scan the code via the mobile-friendly portal.

## Operations
- **Generate Codes**: Codes are generated automatically via scheduled tasks (`php artisan app:generate-daily-codes`).
- **Manual Attendance**: Admins can override attendance status (Present, Late, Absent) via the `Attendances` resource.
- **Schedule Management**: Create weekly recurring spots for each division (e.g., Every Tuesday at 5 PM).
