<?php

if (!defined("TEA_MESSENGER_API_INIT")) {
	define("TEA_MESSENGER_API_INIT", 1);

	require __DIR__."/../config/init.php";

	/**
	 * @param string $class
	 * @return void
	 */
	function teaMessengerApiInternalAutoloader(string $class): void
	{
		$class = str_replace("\\", "/", $class);
		if (file_exists($f = BASEPATH."/src/classes/{$class}.php")) {
			require $f;
		}
	}

	spl_autoload_register("teaMessengerApiInternalAutoloader");

	require BASEPATH."/vendor/autoload.php";
	require BASEPATH."/src/helpers.php";

	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: http://localhost:3000");
	header("Access-Control-Allow-Headers: Origin,Authorization,Content-Type");
	header("Access-Control-Allow-Methods: POST,GET,OPTIONS,HEAD");

	if (isset($_SERVER["REQUEST_METHOD"]) && in_array($_SERVER["REQUEST_METHOD"], ["OPTIONS", "HEAD"])) {
		http_response_code(200);
		exit;
	}
}
