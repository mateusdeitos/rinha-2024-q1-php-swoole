<?php

require_once 'vendor/autoload.php';

use App\Controllers\GetExtratoController;
use App\DTO\ExtratoDTO;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$port = intval(getenv("PORT"));
$server = new Server("localhost", $port);

$server->on("start", function (Server $server) {
    echo "OpenSwoole http server is started at http://localhost:{$server->port}\n";
});

$server->on("request", function (Request $request, Response $response) {

	match (true) {
		GetExtratoController::match($request->server["request_uri"]) => (new GetExtratoController($request, $response))->run(),
		default => $response->status(404)
	};

	$response->end();
});

$server->start();
