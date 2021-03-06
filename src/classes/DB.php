<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 */
final class DB
{	
	/**
	 * @var \DB
	 */
	private static $self;

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Constructor.
	 */
	private function __construct()
	{
		require_once BASEPATH."/config/database.php";

		defined("PDO_PARAMETERS") or die("PDO_PARAMETERS is not defined!\n");

		$this->pdo = new PDO(...PDO_PARAMETERS);
		$this->pdo->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
		);
	}

	/**
	 * @return \PDO
	 */
	public static function pdo(): PDO
	{
		return self::getInstance()->pdo;
	}

	/**
	 * @return \DB
	 */
	public static function getInstance(): DB
	{
		if (!(self::$self instanceof DB)) {
			self::$self = new self;
		}
		return self::$self;
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->pdo = null;
		unset($this->pdo);
	}
}
