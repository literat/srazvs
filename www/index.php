<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

$container = require_once __DIR__ . '/../app/bootstrap.php';

$container->getService('application')->run();