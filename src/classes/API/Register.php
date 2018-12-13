<?php

namespace API;

use DB;
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
	private function save(array &$i): void
	{
		try {
			$pdo = DB::pdo();
			$st = $pdo->prepare(
				"INSERT INTO `users` (`first_name`, `last_name`, `username`, `gender`, `password`, `registered_at`, `updated_at`) VALUES (:first_name, :last_name, :username, :gender, :password, :registered_at, NULL);"
			);
			$st->execute(
				[
					":first_name" => $i["first_name"],
					":last_name" => $i["last_name"],
					":username" => $i["username"],
					":gender" => $i["gender"],
					":password" => $i["password"],
					":registered_at" => date("Y-m-d H:i:s")
				]
			);

			print API::json001("success",
				[
					"message" => "register_success"
				]
			);
		} catch (PDOException $e) {
			// Close PDO connection.
			$st = $pdo = null;
			
			error_api("Internal Server Error: {$e->getMessage()}", 500);

			unset($e, $st, $pdo, $i);
			exit;
		}

		// Close PDO connection.
		$st = $pdo = null;
		unset($st, $pdo, $i);
	}

	/**
	 * @return void
	 */
	private function submit(): void
	{
		if ($_SERVER["REQUEST_METHOD"] !== "POST") {
			error_api("Method not allowed", 405);
		}
		
		$this->captcha = API::validateToken();

		// Validate input
		$i = json_decode(file_get_contents("php://input"), true);
		if (!is_array($i)) {
			error_api("Invalid request body");
			return;
		}
		$this->validateSubmitInput($i);
		$this->save($i);
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function validateSubmitInput(array &$i): void
	{
		$m = "Bad Request:";
		$required = [
			"first_name",
			"last_name",
			"gender",
			"email",
			"phone",
			"password",
			"cpassword",
			"captcha"
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

			$i[$v] = trim($i[$v]);
		}

		if ($i["captcha"] !== $this->captcha) {
			error_api("{$m} Invalid captcha response", 400);
			return;
		}

		unset($required, $v);

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

		if (!filter_var($i["email"], FILTER_VALIDATE_EMAIL)) {
			error_api("{$m} \"{$i["email"]}\" is not a valid email address", 400);
			return;
		}

		if (!preg_match("/^[0\+]\d{4,13}$/", $i["phone"])) {
			error_api("{$m} Invalid phone number", 400);
			return;
		}

		if ($i["password"] !== $i["cpassword"]) {
			error_api("{$m} The confirm password is not same with password", 400);
			return;
		}

		$c = strlen($i["password"]);

		if ($c < 6) {
			error_api("{$m} `password` is too short. Please provide a password with size more than 6 bytes.", 400);
			return;
		}

		if ($c >= 200) {
			error_api("{$m} `password` is too long. Please provide a password with size less than 200 bytes.", 400);
			return;
		}

		unset($c, $i);
		return;
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
