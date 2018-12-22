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
			], true],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Your email.+has already been registered as another user/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi222@gmail.com",
				"phone" => "085867152777",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Your phone.+has already been registered as another user/"]
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
				"email" => "ammarfaizi3@gmail.com",
				"phone" => "085867152771",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`first_name\` is too long\. Please provide a name with/"],
			[[
				"first_name" => "aaa",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi4@gmail.com",
				"phone" => "085867152772",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`first_name\` is too short\. Please provide a name with/"],
			[[
				"first_name" => "aaaaa",
				"last_name" => str_repeat("a", 300),
				"gender" => "male",
				"email" => "ammarfaizi5@gmail.com",
				"phone" => "085867152773",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/\`last_name\` is too long\. Please provide a name with/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "blabla",
				"email" => "ammarfaizi6@gmail.com",
				"phone" => "085867152774",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Invalid gender/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfai@zi2@gmail.com",
				"phone" => "085867152775",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/is not a valid email address/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => str_repeat("a", 200)."@gmail.com",
				"phone" => "085867152776",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/is not a valid email address/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi6@gmail.com",
				"phone" => "123",
				"password" => "ini password",
				"cpassword" => "ini password",
			], false, "/Invalid phone number/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi211@gmail.com",
				"phone" => "085867152777",
				"password" => "abcd",
				"cpassword" => "abcdz",
			], false, "/Confirm password is not equal with the password/"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi232@gmail.com",
				"phone" => "085867152777",
				"password" => "aaa",
				"cpassword" => "aaa",
			], false, "/\`password\` is too short\. Please provide a password /"],
			[[
				"first_name" => "Ammar",
				"last_name" => "Faizi",
				"gender" => "male",
				"email" => "ammarfaizi2444@gmail.com",
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

		if ($o["info"]["http_code"] === 500) {
			var_dump($o["out"]);

			if (preg_match("/Integrity constraint violation: 1062 Duplicate entry/", $o["out"])) {
				$this->testClose(true);
				$o = $this->submit($form);
			}
		}

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
		$me = json_decode(icdecrypt($testToken, APP_KEY), true);
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
	 * @param bool $force
	 * @return void
	 */
	public function testClose($force = false): void
	{
		$this->assertTrue(file_exists($f = BASEPATH."/php_server.pid"));

		if ((!in_array("-vvvvv", $_SERVER["argv"])) || $force) {
			$tables = ["users", "user_keys", "phones", "emails", "addresses"];
			$pdo = DB::pdo();
			$pdo->exec("SET foreign_key_checks = 0;");
			foreach ($tables as $key => $table) {
				$pdo->exec("TRUNCATE TABLE `{$table}`;");
			}
			$pdo = null;
			unset($pdo);
		}
	}
}
