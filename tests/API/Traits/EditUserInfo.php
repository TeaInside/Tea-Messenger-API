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

		var_dump($o["out"]);

		die;
	}
}
