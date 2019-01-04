<?php

namespace tests\API;

use DB;
use tests\Curl;
use PHPUnit\Framework\TestCase;
use tests\API\Traits\EditUserInfo;

static $email;
static $testToken = null;
static $first_name = "Php Unit";
static $last_name = " Test Case";
static $token_session;

$email =  time().rand()."-phpunit@phpunit.de";

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \tests\API
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
		global $email, $first_name, $last_name;
		$reg = [
			"first_name" => $first_name,
			"last_name" => $last_name,
			"gender" => "male",
			"email" => $email,
			"phone" => "08".time().rand(0,9),
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
				"Authorization: Bearer {$o["data"]["token"]}",
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

	/**
	 * Test login + get_user_info
	 *
	 * @return void
	 */
	public function testLogin(): void
	{
		global $email, $first_name, $last_name, $testToken, $token_session;
		$o = $this->curl("http://localhost:8080/login.php?action=login",
			[
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => json_encode(
					[
						"username" => $email,
						"password" => "phpunit123QWEASDZXC",
					]
				),
				CURLOPT_HTTPHEADER => [
					"Authorization: Bearer {$testToken}",
					"Content-Type: application/json"
				]
			]
		);

		$this->assertEquals($o["info"]["http_code"], 200);

		$o = json_decode($o["out"], true);

		$o = $this->curl("http://localhost:8080/profile.php?action=get_user_info",
			[
				CURLOPT_HTTPHEADER => [
					"Authorization: Bearer {$o["data"]["message"]["token_session"]}",
					"Content-Type: application/json"
				]
			]
		);

		$token_session = $o["data"]["message"]["token_session"];

		$o = json_decode($o["out"], true);
		$this->assertEquals($o["data"]["first_name"], $first_name);
		$this->assertEquals($o["data"]["last_name"], trim($last_name));
		$this->assertEquals($o["data"]["email"], $email);
	}

	use EditUserInfo;
}
