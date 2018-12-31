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
class Home implements APIContract
{
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
		$tkn = null;
		if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
			$tkn = explode("Bearer ", $_SERVER["HTTP_AUTHORIZATION"]);
			if (isset($tkn[1])) {
				$tkn = json_decode(icdecrypt($tkn[1], APP_KEY), true);
			} else {
				$tkn = null;
			}
		}

		if ((!isset($tkn[0], $tkn[1]) || (md5($_SERVER["HTTP_USER_AGENT"]) !== $tkn[1]))) {
			error_api("Unauthorized", 401);
			return;
		}

		$this->tkn = $tkn;
		unset($tkn);

		switch ($this->action) {
			case "get_user_info":
				$this->getUserInfo();
			break;
		}
	}

	/**
	 * @return void
	 */
	private function getUserInfo(): void
	{
		try {
			$pdo = DB::pdo();
			$st = $pdo->prepare(
				"SELECT `a`.`id` AS `user_id`,`a`.`first_name`,`a`.`last_name` FROM `users` AS `a` WHERE `id` = :id LIMIT 1"
			);
			$st->execute([":id" => $this->tkn[0]]);
			if ($st = $st->fetch(PDO::FETCH_ASSOC)) {
				$st["user_id"] = (int)$st["user_id"];
				print API::json001("success", $st);
			}
			$st = $pdo = null;
			unset($st, $pdo);
		} catch (PDOException $e) {
			log($e);
			error_log($e);
			error_api("Internal Server Error: {$e}", 500);
			exit;
		}
	}
}
