<?php

namespace API;


use API;
use Contracts\APIContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \API
 * @version 0.0.1
 */
class Register implements APIContract
{
	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $captcha;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (!isset($_GET["action"])) {
			error_api("Bad Request: Invalid action", 400);
		}

		$this->action = $_GET["action"];
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		switch ($this->action) {
			case "get_token":
				$this->getToken();
				break;
			case "submit":
				$this->submit();
				break;
			default:
				break;
		}
	}

	/**
	 * @return void
	 */
	private function getToken(): void
	{
		$expired = time()+3600;

		// By using this token, we don't need any session which saved at the server side.
		print API::json001(
			"success",
			[
				// Encrypted expired time and random code 6 bytes for the captcha.
				"token" => cencrypt(json_encode(
					[
						"expired" => $expired,
						"code" => rstr(6, "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM")
					]
				), APP_KEY),

				// Show expired time.
				"expired" => $expired
			]
		);
	}
}
