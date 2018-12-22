<?php

if (!isset($_SERVER["argv"][1])) {
	exit(0);
}

$_SERVER["argv"][1] = urlencode($_SERVER["argv"][1]);
$ch = curl_init("https://tea-messenger-dev.teainside.org/circleci.php?project=tea-messenger-api&branch={$_SERVER["argv"][1]}");
curl_setopt_array($ch,
	[
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode(
			[
				"argv_2" =>  isset($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : null
			]
		)
	]
);
print curl_exec($ch);
curl_close($ch);
