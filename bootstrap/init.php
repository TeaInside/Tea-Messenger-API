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
}
