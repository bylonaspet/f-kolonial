<?php
declare(strict_types=1);

namespace FKolonial;

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

const KOLONIAL_API = 'https://www.kolonial.cz/api/v2';

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
	// todo fail
}

$accessToken = \json_decode($response->getBody()->getContents())->access_token;

try {
	$response = $guzzle->get(KOLONIAL_API . '/orders', [
		'headers' => [
			'Authorization' => 'Bearer ' . $accessToken,
		],
	]);
} catch (ServerException $e) {
	// todo fail
}

$orders = \json_decode($response->getBody()->getContents())->orders;

foreach ($orders as $order) {
	if ($order->number == $_GET['variable_symbol']) {
		echo \json_encode($order);
	}
}

// todo fail
