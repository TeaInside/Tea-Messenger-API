<?php

namespace tests\API;

use tests\Curl;
use PHPUnit\Framework\TestCase;

static $testToken = null;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \API
 * @version 0.0.1
 */
class RegisterTest extends TestCase
{
	use Curl;

	/**
	 * @return void
	 */
	public function testGetToken(): void
	{
		global $testToken;
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
		$testToken = $o["data"]["token"];
	}

	/**
	 * @return array
	 */
	private function validInput(): array
	{
		return [
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], true]
		];
	}

	/**
	 * @return array
	 */
	private function invalidInput(): array
	{
		return [
			[[
				"first_name" => str_repeat("q", 300),
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`first_name\` is too long\. Please provide a name with/"],
			[[
				"first_name" => "aaa",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`first_name\` is too short\. Please provide a name with/"],
			[[
				"first_name" => "aaaaa",
				"last_name" => str_repeat("a", 300),
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`last_name\` is too long\. Please provide a name with/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "blabla",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Invalid gender/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfai@zi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/is not a valid email address/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => str_repeat("a", 200)."@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/is not a valid email address/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "123",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Invalid phone number/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "abcd",
				"cpassword" => "abcdz",
			], false, "/The confirm password is not same with password/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "aaa",
				"cpassword" => "aaa",
			], false, "/\`password\` is too short\. Please provide a password /"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => str_repeat("q", 300),
				"cpassword" => str_repeat("q", 300),
			], false, "/\`password\` is too long\. Please provide a password /"]
		];
	}

	/**
	 * @return array
	 */
	public function dataToBeTested(): array
	{
		return array_merge([], $this->validInput(), $this->invalidInput());
	}

	/**
	 * @dataProvider dataToBeTested
	 * @param array  $form
	 * @param bool   $isValid
	 * @param string $mustMatch
	 * @return void
	 */
	public function testSubmit(array $form, bool $isValid, string $mustMatch = null): void
	{
		$o = $this->submit($form);

		$this->assertTrue(isset($o["info"]["http_code"]));
		$this->assertEquals($o["info"]["http_code"], ($isValid ? 200 : 400));

		if (!is_null($mustMatch)) {
			$this->assertTrue((bool)preg_match($mustMatch, $o["out"]));
		}
	}

	/**
	 * @return array
	 */
	private function submit(array $form): array
	{
		global $testToken;
		$me = json_decode(dencrypt($testToken, APP_KEY), true);
		$form["captcha"] = $me["code"];
		$opt = [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($form),
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer {$testToken}",
				"Content-Type: application/json"
			]
		];
		return $this->curl("http://localhost:8080/register.php?action=submit", $opt);
	}

	/**
	 * @return void
	 */
	public function testClose(): void
	{
		$this->assertTrue(file_exists($f = BASEPATH."/php_server.pid"));
	}
}
