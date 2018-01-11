<?php

$conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_finance . ";charset=utf8", $username , $password);


// ******************************************************************************

// $conn_finance = new mysqli($host, $username, $password, $db_finance);

// if ($conn_finance->connect_errno) 
// {
// 	echo "Failed to connect to MySQL: (" . $conn_finance->connect_errno . ") " . $conn_finance->connect_error;
// }

// ****************************************************************************

// $conn = mysql_connect($host,$username,$password);
// if (!$conn)
//   {
//   die('Could not connect: ' . mysql_error());
//   }

// mysql_select_db($db_finance);

// if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);


// *****************************************************************************


// Singleton


// class DB_FINANCE
// {

// 	/*** Declare instance ***/
// 	private static $instance = NULL;

// 	/**
// 	*
// 	* the constructor is set to private so
// 	* so nobody can create a new instance using new
// 	*
// 	*/
// 	private function __construct() 
// 	{
// 	  /*** maybe set the db name here later ***/
// 	}

// 	/**
// 	*
// 	* Return DB instance or create intitial connection
// 	*
// 	* @return object (PDO)
// 	*
// 	* @access public
// 	*
// 	*/
// 	public static function getInstance() 
// 	{
// 		if (!self::$instance)
// 		    {
// 		    self::$instance = new PDO("mysql:host=localhost;dbname=finance", 'root', 'root');
// 		    self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 		    }
// 		return self::$instance;
// 	}

// 	/**
// 	*
// 	* Like the constructor, we make __clone private
// 	* so nobody can clone the instance
// 	*
// 	*/
// 	private function __clone()
// 	{

// 	}

// } /*** end of class ***/


?>