<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Force la base de données de test sans suffixe _test
$_ENV['DATABASE_URL'] = 'mysql://fightclub:fightclub@mysql:3306/fightclub?serverVersion=8.0';
$_SERVER['DATABASE_URL'] = 'mysql://fightclub:fightclub@mysql:3306/fightclub?serverVersion=8.0';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
