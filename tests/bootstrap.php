<?php

require __DIR__ . '/../vendor/autoload.php';

use Tester\Environment;



if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer install --dev`\n";
	exit(1);
}

date_default_timezone_set('Europe/Prague');

define('TEMP_DIR', sprintf('%s/tmp', __DIR__));

Environment::setup();
