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
		if ($_SERVER["REQUEST_METHOD"] !== "POST") {
			error_api("Method not allowed", 405);
		}

		// Validate input
		$i = json_decode(file_get_contents("php://input"), true);
		if (!is_array($i)) {
			error_api("Invalid request body", 400);
			return;
		}

		$this->validateEditUserInfoInput($i);
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function validateEditUserInfoInput(array &$i): void
	{
		$m = "Bad Request:";
		$required = [
			"first_name",
			"last_name",
			"gender",
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

		$c = strlen($i["first_name"]);

		if ($c >= 200) {
			error_api("{$m} `first_name` is too long. Please provide a name with size less than 200 bytes.", 400);
			return;
		}

		if ($c <= 4) {
			error_api("{$m} `first_name` is too short. Please provide a name with size more than 4 bytes.", 400);
			return;
		}

		$c = strlen($i["last_name"]);

		if ($c >= 200) {
			error_api("{$m} `last_name` is too long. Please provide a name with size less than 200 bytes.", 400);
			return;
		}

		if (!in_array($i["gender"], ["male", "female"])) {
			error_api("{$m} Invalid gender", 400);
			return;
		}
		$i["gender"] = $i["gender"] === "male" ? "m" : "f";
	}
}
