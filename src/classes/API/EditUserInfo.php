<?php

namespace API;

use DB;
use API;
use PDO;
use PDOException;
use Contracts\APIContract;
use API\Traits\TokenSession;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \API
 * @version 0.0.1
 */
class EditUserInfo implements APIContract
{
	use TokenSession;

	/**
	 * @var array
	 */
	private $tkn = [];

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
		$this->validateTokenSession();

		switch ($this->action) {
			case "edit_user_info":
				$this->editUserInfo();
			break;
		}
	}

	/**
	 * @return void
	 */
	private function editUserInfo(): void
	{
		$m = "Bad Request:";
		$required = [
			"first_name",
			"last_name",
			"gender",
			"email",
			"phone"
		];

		foreach ($required as $v) {
			if (!isset($i[$v])) {
				error_api("{$m} Field required: {$v}", 400);
				return;
			}
			if (!is_string($i[$v])) {
				error_api("{$m} Field `{$v}` must be a string", 400);
				return;
			}

			if (!in_array($v, ["password", "cpassword"])) {
				$i[$v] = trim($i[$v]);
			}
		}

		$i["email"] = strtolower($i["email"]);
	}

	/**
	 * @return void
	 */
	private function validateEditUserInfoInput(): void
	{		
	}
}
