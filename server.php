<?php

require_once 'vendor/autoload.php';

use App\Controllers\CreateTransacaoController;
use App\Controllers\GetExtratoController;
use App\Database\Pool;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$port = intval(getenv("PORT"));
$server = new Server("localhost", $port);

\Swoole\Runtime::enableCoroutine();

$server->on("start", function (Server $server) {
    echo "Swoole http server is started at http://localhost:{$server->port}\n";
});

$server->on("request", function (Request $request, Response $response) {
	$pool = new Pool();
	try {
		match (true) {
			GetExtratoController::match($request->server["request_uri"]) => (new GetExtratoController($request, $response))->run($pool),
			CreateTransacaoController::match($request->server["request_uri"]) => (new CreateTransacaoController($request, $response))->run($pool),
			default => $response->status(404)
		};
	
		$response->isWritable() && $response->end();
	} catch (\Throwable $th) {
		$response->status($th->getCode());
		$response->end();
	} 

});

$server->start();
