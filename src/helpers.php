<?php



if (!function_exists("rstr")) {
	/**
	 *
	 * Generate a random string
	 *
	 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
	 * @param int    $n
	 * @param string $e
	 * @return string
	 */
	function rstr(int $n, string $e = null): string
	{
		$e = is_string($e) ? $e : "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890---___...";
		$c = strlen($e) - 1;
		$r = "";
		for ($i=0; $i <= $c; $i++) { 
			$r .= $e[rand(0, $c)];
		}
		return $r;
	}
}
