<?php

namespace API\Traits;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \API\TokenSession
 * @version 0.0.1
 */
trait TokenSession
{
	/**
	 * @var array
	 */
	private $tkn = [];

	/**
	 * @return void
	 */
	public function validateTokenSession(): void
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
		isset($_SERVER["HTTP_USER_AGENT"]) or $_SERVER["HTTP_USER_AGENT"] = "1";
		if ((!isset($tkn[0], $tkn[1]) || (md5($_SERVER["HTTP_USER_AGENT"]) !== $tkn[1]))) {
			error_api("Unauthorized", 401);
			return;
		}

		$this->tkn = $tkn;
		unset($tkn);
	}
}
