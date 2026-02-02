<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Orange Absence API",
    description: "API Documentation for Orange Absence System",
    contact: new OA\Contact(email: "admin@orange.test")
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "API Server"
)]
#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Ardy Al-Banna"),
        new OA\Property(property: "email", type: "string", example: "admin@orange.test"),
        new OA\Property(property: "points", type: "integer", example: 100),
        new OA\Property(property: "is_suspended", type: "boolean", example: false),
    ]
)]
#[OA\Schema(
    schema: "Attendance",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user_id", type: "integer", example: 1),
        new OA\Property(property: "status", type: "string", example: "present"),
        new OA\Property(property: "is_approved", type: "boolean", example: true),
    ]
)]
abstract class Controller
{
    //
}
