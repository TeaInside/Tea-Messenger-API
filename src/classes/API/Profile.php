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
class Profile implements APIContract
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
				"SELECT  
					`a`.`id` AS `user_id`,`a`.`first_name`,`a`.`last_name`,
					`a`.`gender`,
					`a`.`registered_at`,`b`.`email`,`c`.`phone`
				FROM `users` AS `a` 
				INNER JOIN `emails` AS `b` ON `b`.`id` = `a`.`primary_email`
				INNER JOIN `phones` AS `c` ON `c`.`id` = `a`.`primary_phone` 
					WHERE `a`.`id` = :id LIMIT 1"
			);
			$st->execute([":id" => $this->tkn[0]]);
			if ($st = $st->fetch(PDO::FETCH_ASSOC)) {
				$st["photo"] = "";
				$st["registered_at"] = date("d F Y", strtotime($st["registered_at"]));
				foreach ($st as &$stptr) {
					$stptr = htmlspecialchars($stptr, ENT_QUOTES, "UTF-8");
				}
				unset($stptr);
				$st["user_id"] = (int)$st["user_id"];
				print API::json001("success", $st);
			} else {
				error_api("Unauthorized", 401);
				return;
			}
			$st = $pdo = null;
			unset($st, $pdo);
		} catch (PDOException $e) {
			$e = $e->getMessage();
			error_log($e);
			error_api("Internal Server Error: {$e}", 500);
			exit;
		}
	}
}
