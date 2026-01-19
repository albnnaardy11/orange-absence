<?php
require 'vendor/autoload.php';
use Maatwebsite\Excel\HeadingRowImport;

// Boot Laravel to use facade? No, HeadingRowImport can be used if we setup valid reader?
// Actually simpler to use the Facade if we boot app, but that's complex in script.
// Let's try to use the class directly if possible, but it depends on config.
// Better: use artisan tinker.

// I will run this via artisan tinker command line.
echo "Script file not used.";
