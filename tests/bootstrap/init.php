<?php

require __DIR__ . '/../../vendor/autoload.php';

passthru('php artisan migrate:fresh');
