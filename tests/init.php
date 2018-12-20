<?php

require_once __DIR__."/../bootstrap/init.php";
require_once BASEPATH."/config/app.php";

function truncateTable(string $table): void
{
	$pdo = DB::pdo();
	$pdo->exec("SET foreign_key_checks = 0;");
	$pdo->exec("TRUNCATE TABLE `{$table}`;");
	unset($pdo);
}
