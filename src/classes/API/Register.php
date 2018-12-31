<?php

namespace API;

use DB;
use API;
use PDO;
use PDOException;
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
	 * @param mixed $userId
	 * @return void
	 */
	private function fallback($userId): void
	{
		DB::pdo()
			->prepare("DELETE FROM `users` WHERE `id` = :id LIMIT 1;")
			->execute([":id" => $userId]);
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function validateDB(array &$i): void
	{
		$r = explode("@", $i["email"], 2);
		$e = str_replace(
			["+", ".", "_", "-"],
			"%",
			$r[0]
		);
		$e = "{$e}@{$r[1]}";
		$pdo = DB::pdo();
		$st = $pdo->prepare("SELECT `user_id` FROM `emails` WHERE `email` LIKE :email LIMIT 1;");
		$st->execute([":email" => $e]);
		if ($st = $st->fetch(PDO::FETCH_NUM)) {
			error_api("Your email '{$i["email"]}' has already been registered as another user. Please use another email! ~", 400);
			return;
		}
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function save(array &$i): void
	{
		try {

			$createdAt = date("Y-m-d H:i:s");
			$encryptedUserKey = icencrypt($userKey = rstr(32), $createdAt);
			$i["password"] = icencrypt($i["password"], $userKey);
			unset($userKey);

			$pdo = DB::pdo();
			$st = $pdo->prepare(
				"INSERT INTO `users` (`first_name`, `last_name`, `username`, `gender`, `password`, `registered_at`, `updated_at`) VALUES (:first_name, :last_name, NULL, :gender, :password, :registered_at, NULL);"
			);
			$st->execute(
				[
					":first_name" => $i["first_name"],
					":last_name" => $i["last_name"],
					":gender" => $i["gender"],
					":password" => $i["password"],
					":registered_at" => $createdAt
				]
			);

			$userId = $pdo->lastInsertId();			

			$st = $pdo->prepare(
				"INSERT INTO `user_keys` (`user_id`, `ukey`, `created_at`) VALUES (:user_id, :ukey, :created_at);"
			);
			$st->execute(
				[
					":user_id" => $userId,
					":ukey" => $encryptedUserKey,
					":created_at" => $createdAt
				]
			);

			$st = $pdo->prepare(
				"INSERT INTO `emails` (`user_id`, `email`, `created_at`) VALUES (:user_id, :email, :created_at);"
			);
			$st->execute(
				[
					":user_id" => $userId,
					":email" => $i["email"],
					":created_at" => $createdAt
				]
			);

			$emailId = $pdo->lastInsertId();

			$st = $pdo->prepare(
				"INSERT INTO `phones` (`user_id`, `phone`, `created_at`) VALUES (:user_id, :phone, :created_at);"
			);
			$st->execute(
				[
					":user_id" => $userId,
					':phone' => $i["phone"],
					":created_at" => $createdAt
				]
			);

			$phoneId = $pdo->lastInsertId();

			$st = $pdo->prepare(
				"UPDATE `users` SET `primary_email`=:email_id,`primary_phone`=:phone_id WHERE `id` = :user_id LIMIT 1;"
			);
			$st->execute(
				[
					":email_id" => $emailId,
					":phone_id" => $phoneId,
					":user_id" => $userId
				]
			);

			print API::json001("success",
				[
					"message" => "register_success"
				]
			);
		} catch (PDOException $e) {

			if (isset($userId)) {
				$this->fallback($userId);
			}

			// Close PDO connection.
			$st = $pdo = null;
			$e = $e->getMessage();

			if (preg_match("/(?:Duplicate entry \')(.*)(?:\' for key \')(.*)(?:\')/U", $e, $m)) {
				error_api("Your {$m[2]} '{$m[1]}' has already been registered as another user. Please use another {$m[2]}!", 400);
			}

			log($e);
			error_log($e);
			error_api("Internal Server Error: {$e}", 500);
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

			if (!in_array($v, ["password", "cpassword"])) {
				$i[$v] = trim($i[$v]);
			}
		}

		$i["email"] = strtolower($i["email"]);

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
			error_api("{$m} Confirm password is not equal with the password", 400);
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

		$this->validateDB($i);

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
				"token" => icencrypt(json_encode(
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
