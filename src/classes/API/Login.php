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
class Login implements APIContract
{
	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $loginType = null;

	/**
	 * @var string
	 */
	private $tokenSession = null;

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
			case "login":
				$this->login();
				break;
			default:
				break;
		}
	}

	/**
	 * @return void
	 */
	private function login(): void
	{
		$in = file_get_contents("php://input");

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

		$this->validateInput($i);
		is_string($this->loginType) and $this->checkLogin($i);

		if (is_string($this->tokenSession)) {
			print API::json001("success",
				[
					"message" => [
						"state" => "login_success",
						"token_session" => $this->tokenSession
					]
				]
			);
		} else {
			print API::json001("success",
				[
					"message" => [
						"state" => "Invalid username or password!",
					]
				]
			);
		}
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function checkLogin(array &$i): void
	{
		try {
			$pdo = DB::pdo();

			if ($this->loginType === "email") {
				$st = $pdo->prepare("SELECT  `a`.`id`,`a`.`password`,`b`.`ukey`,`a`.`registered_at` FROM `users` AS `a` INNER JOIN `user_keys` AS `b` ON `a`.`id` = `b`.`user_id` INNER JOIN `emails` AS `c` ON `a`.`id` = `c`.`user_id` WHERE `c`.`email` = :email LIMIT 1;");
				$st->execute([":email" => $i["username"]]);
			} else if ($this->loginType === "phone") {

			}

			if ($st = $st->fetch(PDO::FETCH_ASSOC)) {
				if (icdecrypt($st["password"], icdecrypt($st["ukey"], $st["registered_at"])) === $i["password"]) {
					$this->tokenSession = icencrypt(
						json_encode(
							[
								$st["id"],
								md5($_SERVER["HTTP_USER_AGENT"])
							]
						), 
						APP_KEY
					);
				}
			}
		} catch (PDOException $e) {
			// Close PDO connection.
			$st = $pdo = null;
			$e = $e->getMessage();

			log($e);
			error_log($e);
			error_api("Internal Server Error: {$e}", 500);
			exit;
		}
	}

	/**
	 * @param array &$i
	 * @return void
	 */
	private function validateInput(array &$i): void
	{
		$m = "Bad Request:";
		$required = [
			"username",
			"password"
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
		$i["username"] = strtolower($i["username"]);
		if (filter_var($i["username"], FILTER_VALIDATE_EMAIL)) {
			$this->loginType = "email";
		}

		if (preg_match("/^[0\+]\d{4,13}$/", $i["username"])) {
			$this->loginType = "phone";
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
