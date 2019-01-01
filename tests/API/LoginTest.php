<?php

namespace tests\API;

use DB;
use tests\Curl;
use PHPUnit\Framework\TestCase;

static $testToken = null;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \API
 * @version 0.0.1
 */
class LoginTest extends TestCase
{
	use Curl;

	/**
	 * @return void
	 */
	public function testCreateANewAccountBeforeLogin(): void
	{
		$reg = [
			"first_name" => "Php Unit",
			"last_name" => " Test Case",
			"gender" => "male",
			"email" => "phpunit@phpunit.de",
			"phone" => "088592910210",
			"password" => "phpunit123QWEASDZXC",
			"cpassword" => "phpunit123QWEASDZXC",
		];

		$o = $this->curl("http://localhost:8080/register.php?action=get_token");
		$o = json_decode($o["out"], true);
		$this->assertTrue(
			isset(
				$o["status"],
				$o["data"],
				$o["data"]["token"],
				$o["data"]["expired"]
			)
		);
		$this->assertEquals($o["status"], "success");

		$me = json_decode(icdecrypt($o["data"]["token"], APP_KEY), true);
		$reg["captcha"] = $me["code"];
		$opt = [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($reg),
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer {$testToken}",
				"Content-Type: application/json"
			]
		];
		$o = $this->curl("http://localhost:8080/register.php?action=submit", $opt);
		$this->assertEquals($o["info"]["http_code"], 200);
	}

	/**
	 * @return void
	 */
	public function testGetToken(): void
	{
		global $testToken;
		$o = $this->curl("http://localhost:8080/login.php?action=get_token");
		$o = json_decode($o["out"], true);
		$this->assertTrue(
			isset(
				$o["status"],
				$o["data"],
				$o["data"]["token"],
				$o["data"]["expired"]
			)
		);
		$this->assertEquals($o["status"], "success");
		$testToken = $o["data"]["token"];
	}

	public function testLogin(): void
	{

	}
}
