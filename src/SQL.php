<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

use \Exception;

/**
 * SQL drivers manager
 *
 * @author    Vladimir Bashkirtsev <vladimir@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/SQL.php $
 */

class SQL
    {

	/**
	 * Shared SQL drivers
	 *
	 * @var array
	 */
	static private $_shared = array();

	/**
	 * Get SQL driver instance
	 *
	 * @param string $driver     SQL driver type
	 * @param bool   $shared     True if shared connection is acceptable, end user is not guaranteed that SQL will not mix with other SQL queries
	 * @param mixed  $parameters Specific parameters to be passed to SQL driver
	 *
	 * @return SQLdatabase
	 *
	 * @throws Exception Unknown SQL driver
	 *
	 * @exceptioncode EXCEPTION_UNKNOWN_SQL_DRIVER
	 */

	static public function get($driver, $shared = true, ... $parameters)
	    {
		$driver = strtolower($driver);
		$hash   = md5(serialize($parameters));
		if ($shared === true && isset(self::$_shared[$driver][$hash]) === true)
		    {
			$instance = self::$_shared[$driver][$hash];
		    }
		else
		    {
			switch ($driver)
			    {
				case "mysql":
					$instance = self::_generateMySQL($parameters);
				    break;
				case "postgres":
				case "postgresql":
					$instance = self::_generatePostgreSQL($parameters);
				    break;
				default:
				    throw new Exception(_("Unknown SQL driver"), EXCEPTION_UNKNOWN_SQL_DRIVER);
			    }

			if ($shared === true)
			    {
				self::$_shared[$driver][$hash] = $instance;
			    }
		    } //end if

		return $instance;
	    } //end get()


	/**
	 * Generate MySQL instance
	 *
	 * @param array $parameters MySQL connection parameters
	 *
	 * @return MySQLdatabase MySQL connection instance
	 *
	 * @throws Exception No connection details for MySQL database
	 *
	 * @exceptioncode EXCEPTION_NO_MYSQL_CONNECTION_DETAILS
	 *
	 * @optionalconst MYSQL_DBHOST "" MySQL database host
	 * @optionalconst MYSQL_DBNAME "" MySQL database name
	 * @optionalconst MYSQL_DBUSER "" MySQL database user
	 * @optionalconst MYSQL_DBPASS "" MySQL database pass
	 * @optionalconst DBHOST       "" Default database host
	 * @optionalconst DBNAME       "" Default database name
	 * @optionalconst DBUSER       "" Default database user
	 * @optionalconst DBPASS       "" Default database pass
	 *
	 * @untranslatable MYSQL_DBHOST
	 * @untranslatable MYSQL_DBNAME
	 * @untranslatable MYSQL_DBUSER
	 * @untranslatable MYSQL_DBPASS
	 * @untranslatable DBHOST
	 * @untranslatable DBNAME
	 * @untranslatable DBUSER
	 * @untranslatable DBPASS
	 */

	static private function _generateMySQL(array $parameters)
	    {
		if (count($parameters) === 4)
		    {
			list($host, $name, $user, $pass) = $parameters;
		    }
		else if (self::_defined(array("MYSQL_DBHOST", "MYSQL_DBNAME", "MYSQL_DBUSER", "MYSQL_DBPASS")) === true)
		    {
			$host = MYSQL_DBHOST;
			$name = MYSQL_DBNAME;
			$user = MYSQL_DBUSER;
			$pass = MYSQL_DBPASS;
		    }
		else if (self::_defined(array("DBHOST", "DBNAME", "DBUSER", "DBPASS")) === true)
		    {
			$host = DBHOST;
			$name = DBNAME;
			$user = DBUSER;
			$pass = DBPASS;
		    }
		else
		    {
			throw new Exception(_("No connection details for MySQL database"), EXCEPTION_NO_MYSQL_CONNECTION_DETAILS);
		    } //end if

		return new MySQLdatabase($host, $name, $user, $pass);
	    } //end _generateMySQL()


	/**
	 * Generate PostgreSQL instance
	 *
	 * @param array $parameters PostgreSQL connection parameters
	 *
	 * @return PostgreSQLdatabase PostgreSQL connection instance
	 *
	 * @throws Exception No connection details for PostgreSQL database
	 *
	 * @exceptioncode EXCEPTION_NO_POSTGRESQL_CONNECTION_DETAILS
	 *
	 * @optionalconst POSTGRESQL_DBHOST "" PostgreSQL database host
	 * @optionalconst POSTGRESQL_DBNAME "" PostgreSQL database name
	 * @optionalconst POSTGRESQL_DBUSER "" PostgreSQL database user
	 * @optionalconst POSTGRESQL_DBPASS "" PostgreSQL database pass
	 * @optionalconst DBHOST            "" Default database host
	 * @optionalconst DBNAME            "" Default database name
	 * @optionalconst DBUSER            "" Default database user
	 * @optionalconst DBPASS            "" Default database pass
	 *
	 * @untranslatable POSTGRESQL_DBHOST
	 * @untranslatable POSTGRESQL_DBNAME
	 * @untranslatable POSTGRESQL_DBUSER
	 * @untranslatable POSTGRESQL_DBPASS
	 * @untranslatable DBHOST
	 * @untranslatable DBNAME
	 * @untranslatable DBUSER
	 * @untranslatable DBPASS
	 */

	static private function _generatePostgreSQL(array $parameters)
	    {
		if (count($parameters) === 4)
		    {
			list($host, $name, $user, $pass) = $parameters;
		    }
		else if (self::_defined(array("POSTGRESQL_DBHOST", "POSTGRESQL_DBNAME", "POSTGRESQL_DBUSER", "POSTGRESQL_DBPASS")) === true)
		    {
			$host = POSTGRESQL_DBHOST;
			$name = POSTGRESQL_DBNAME;
			$user = POSTGRESQL_DBUSER;
			$pass = POSTGRESQL_DBPASS;
		    }
		else if (self::_defined(array("DBHOST", "DBNAME", "DBUSER", "DBPASS")) === true)
		    {
			$host = DBHOST;
			$name = DBNAME;
			$user = DBUSER;
			$pass = DBPASS;
		    }
		else
		    {
			throw new Exception(_("No connection details for PostgreSQL database"), EXCEPTION_NO_POSTGRESQL_CONNECTION_DETAILS);
		    } //end if

		return new PostgreSQLdatabase($host, $name, $user, $pass);
	    } //end _generatePostgreSQL()


	/**
	 * Confirm that constants are defined
	 *
	 * @param array $constants Array of constant names
	 *
	 * @return bool True if all contants are defined
	 */

	static private function _defined(array $constants)
	    {
		$defined = true;
		foreach ($constants as $constant)
		    {
			if (defined($constant) === false)
			    {
				$defined = false;
			    }
		    }

		return $defined;
	    } //end _defined()


    } //end class

?>
