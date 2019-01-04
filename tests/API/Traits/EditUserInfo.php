<?php

namespace tests\API\Traits;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \tests\API\Traits
 * @version 0.0.1
 */
trait EditUserInfo
{	
	/**
	 * @return array
	 */
	public function invalidUserData(): array
	{
		global $email, $first_name, $last_name, $token_session;

		return [
			[
				[
					"first_name" => "a",
					"last_name" => "{$last_name} new name",
					"gender" => "male",
					"email" => "{$email}",
					"phone" => "085123345567"
				],
				"/\`first_name\` is too short\./"
			],
			[
				[
					"first_name" => str_repeat("q", 300),
					"last_name" => "{$last_name} new name",
					"gender" => "male",
					"email" => "{$email}",
					"phone" => "085123345567"
				],
				"/\`first_name\` is too long\./"
			],
			[
				[
					"first_name" => "{$first_name}",
					"last_name" => "{$last_name} new name".str_repeat("qq", 200),
					"gender" => "male",
					"email" => "{$email}",
					"phone" => "085123345567"
				],
				"/\`last_name\` is too long\./"
			],
		];
	}

	/**
	 * @dataProvider invalidUserData
	 * @param array  $form
	 * @param string $mustMatch
	 * @return void
	 */
	public function testInvalidEditUserInfo(array $form, string $mustMatch = null): void
	{
		global $token_session;

		$o = $this->curl("http://localhost:8080/edit.php?action=edit_user_info",
			[
				CURLOPT_HTTPHEADER => [
					"Authorization: Bearer {$token_session}"
				],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($form)
			]
		);

		$this->assertEquals($o["info"]["http_code"], 400);
		$this->assertTrue((bool)preg_match($mustMatch, $o["out"]));
	}


	/**
	 * @return void
	 */
	public function testEditUserInfo(): void
	{
		global $email, $first_name, $last_name, $token_session;

		$o = $this->curl("http://localhost:8080/edit.php?action=edit_user_info",
			[
				CURLOPT_HTTPHEADER => [
					"Authorization: Bearer {$token_session}"
				],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode(
					[
						"first_name" => "{$first_name} new name",
						"last_name" => "{$last_name} new name",
						"gender" => "male",
						"email" => "{$email}",
						"phone" => "085123345567"
					]
				)
			]
		);

		$this->assertEquals($o["info"]["http_code"], 200);
	}
}
