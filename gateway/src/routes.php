<?php
declare(strict_types=1);

use Slim\App;
use gateway\api\actions\ProxyApiAction;

return function (App $app): void {
    $app->any('/{routes:.+}', ProxyApiAction::class);
};