#!/usr/bin/env php
<?php

declare(strict_types=1);

use Swoole\HTTP\Server;
use Swoole\HTTP\Request;
use Swoole\HTTP\Response;

// https://www.php.net/manual/ru/function.pg-get-notify.php#121241
$dbConn = new \PDO('pgsql:dbname=sse; host=pgsql;', 'web', 'passwoRt');
$dbConn->exec('LISTEN "channel_name"');


$http = new Server('0.0.0.0', 88);

$http->on('start', static function (Server $server): void {
    echo 'Server started'.\PHP_EOL;
});

$http->on('shutdown', static function (Server $server) use ($dbConn): void {
    if ($dbConn) {
        $dbConn->exec('KILL CONNECTION_ID()');
        unset($dbConn);
    }

    echo 'Server shutdown'.\PHP_EOL;
});

$http->on('request', static function (Request $request, Response $response) use ($dbConn): void {
    $response->header('Access-Control-Allow-Origin', '*');
    $response->header('Content-Type', 'text/event-stream');
    $response->header('Cache-Control', 'no-cache');
    $response->header('Connection', 'keep-alive');
    $response->header('X-Accel-Buffering', 'no');

    $lastEventId = $request->header['last-event-id'] ?? $request->get['lastEventId'] ?? 0;

    for ($i = 0; $i < 10; ++$i) {
        $result = $dbConn->pgsqlGetNotify(\PDO::FETCH_ASSOC, 10000); // https://www.php.net/manual/en/pdo.pgsqlgetnotify.php
        if ($result) {
            $response->write("id: $lastEventId\n");
            $response->write("data: " . \json_encode($result, \JSON_THROW_ON_ERROR) . "\n\n");
        }
        //Co::sleep(10); // that or use pgsqlGetNotify timeout?
    }

    $response->end();
});

$http->start();
