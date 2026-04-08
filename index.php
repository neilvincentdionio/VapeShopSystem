<?php
// Front controller wrapper.
//
// In XAMPP setups the DocumentRoot is often the project root (not `public/`).
// Delegate all requests to the real front controller in `public/index.php`.
require __DIR__ . '/public/index.php';