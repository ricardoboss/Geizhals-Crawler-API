<?php

namespace Geizhals\Crawler;

// composer autoload
require '../vendor/autoload.php';

use Exception;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Options;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

define('GH_URL_PARAM', 'geizhals_url');

header('Access-Control-Allow-Origin: http://localhost:8080');

// check required parameters
if (!array_key_exists(GH_URL_PARAM, $_REQUEST)) {
	$paramName = GH_URL_PARAM;
	(new JsonResponse(['error' => "Missing required parameter $paramName."], 400))->send();

	exit;
}

// get Geizhals URL to crawl
$url = $_REQUEST[GH_URL_PARAM];

// use php-html-parser to fetch and load the DOM
$dom = new Dom();
$opts = new Options();
$opts->setWhitespaceTextNode(false);
$dom->setOptions($opts);
try {
	$dom->loadFromUrl($url);
} catch (Throwable $t) {
	(new JsonResponse(['error' => "Error while loading sources. {$t->getMessage()}"], 500))->send();

	exit;
}

$breadcrumbs = new Breadcrumbs();
try {
	$breadcrumbs->load($dom);
} catch (ChildNotFoundException | NotLoadedException | CircularException $e) {
	(new JsonResponse(['error' => "Error while parsing breadcrumbs. {$e->getMessage()}"], 500))->send();

	exit;
}

$product = new Product();
try {
	$product->load($dom);
} catch (Exception $e) {
	(new JsonResponse(['error' => "Error while parsing product properties. {$e->getMessage()}"], 500))->send();

	exit;
}

(new JsonResponse([
	'breadcrumbs' => $breadcrumbs->getItems(),
	'product' => ['raw' => $product->asArray()]
]))->send();
exit;
