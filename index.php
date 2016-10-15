<?php
declare(strict_types=1);

namespace FKolonial;

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

const KOLONIAL_API = 'https://www.kolonial.cz/api/v2';

header('Content-Type: application/json; charset=utf-8');

$fail = function (string $reason, int $code) {
	http_response_code($code);
	echo \json_encode((object) ['error' => $reason]);
};

$requiredQueryArgs = [
	'client_id',
	'client_secret',
	'username',
	'password',
	'variable_symbol',
];

foreach ($requiredQueryArgs as $arg) {
	if (!array_key_exists($arg, $_GET)) {
		return $fail(sprintf('Missing parameter %s', $arg), 404);
	}
}

$guzzle = new Client(['verify' => false]);

try {
	$response = $guzzle->post(KOLONIAL_API . '/authorize', [
		'json' => [
			'grant_type' => 'password',
			'client_id' => $_GET['client_id'],
			'client_secret' => $_GET['client_secret'],
			'username' => $_GET['username'],
			'password' => $_GET['password'],
		],
	]);
} catch (ServerException $e) {
	return $fail($e->getMessage(), 500);
}

$accessToken = \json_decode($response->getBody()->getContents())->access_token;

try {
	$response = $guzzle->get(KOLONIAL_API . '/orders', [
		'headers' => [
			'Authorization' => 'Bearer ' . $accessToken,
		],
	]);
} catch (ServerException $e) {
	return $fail($e->getMessage(), 500);
}

$orders = \json_decode($response->getBody()->getContents())->orders;

foreach ($orders as $order) {
	if ($order->number == $_GET['variable_symbol']) {
		echo \json_encode($order);
		return;
	}
}

return $fail('No order found', 404);
