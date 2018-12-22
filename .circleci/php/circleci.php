<?php

if (isset($_GET["project"], $_GET["branch"]) && is_string($_GET["project"]) && is_string($_GET["branch"])) {

	$branch = escapeshellarg($_GET["branch"]);

	if ($_GET["project"] === "tea-messenger-api") {
		$cmd = escapeshellarg(PHP_BINARY)." {$branch}";
		shell_exec("nohup sh -c {$cmd} >> /dev/null 2>&1");
	}
}

if (isset($argv[1])) {
	$argv[1] = escapeshellarg($argv[1]);
	$commands = [
		"git reset --hard",
		"git checkout {$argv[1]}",
		"git pull --rebase"
	];
	foreach ($commands as $v) {
		$v = "cd ".escapeshellarg(realpath(__DIR__."/../.."))."; {$v}";
		shell_exec($v);
	}
}