<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

/**
 * Abstract class for access to SQL databases
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/SQLdatabase.php $
 */

abstract class SQLdatabase implements SQLdatabaseInterface
    {

	/**
	 * Connector type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Database host
	 *
	 * @var string
	 */
	protected $dbhost;

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected $dbname;

	/**
	 * Database user
	 *
	 * @var string
	 */
	protected $dbuser;

	/**
	 * Database pass
	 *
	 * @var string
	 */
	protected $dbpass;

	/**
	 * Instantiate this class
	 *
	 * @param string $dbhost Hostname of DB engine to connect
	 * @param string $dbname Name of database to connect to
	 * @param string $dbuser User name to connect to database with
	 * @param string $dbpass Password to connect to database with
	 *
	 * @return void
	 *
	 * @untranslatable dbhost
	 * @untranslatable dbname
	 * @untranslatable dbuser
	 * @untranslatable dbpass
	 */

	public function __construct($dbhost, $dbname, $dbuser, $dbpass)
	    {
		$this->dbhost = $this->_default("dbhost", $dbhost);
		$this->dbname = $this->_default("dbname", $dbname);
		$this->dbuser = $this->_default("dbuser", $dbuser);
		$this->dbpass = $this->_default("dbpass", $dbpass);
	    } //end __construct()


	/**
	 * Wakeup magic method: reconnects database as it is comes up disconnected on unserialization
	 *
	 * @return void
	 */

	abstract public function __wakeup();


	/**
	 * Sleep magic method: disconnects database
	 *
	 * @return array List of class properties to serialize
	 */

	abstract public function __sleep();


	/**
	 * Apply default value if none supplied
	 *
	 * @param string $name  Parameter name
	 * @param string $value Supplied value
	 *
	 * @return string value to use
	 */

	private function _default($name, $value)
	    {
		if (($value === "" || $value === false || $value === null) && isset($GLOBALS["DEFAULT_DATABASE"][$name]) === true)
		    {
			return $GLOBALS["DEFAULT_DATABASE"][$name];
		    }
		else
		    {
			return $value;
		    }
	    } //end _default()


	/**
	 * Execute SQL query on DB engine until successful
	 *
	 * @param string $query         SQL query to execute
	 * @param string $blob          Binary blob to accompany SQL query or false if SQL query is standard one
	 * @param array  $ignoreerrors  Array containing PostgreSQL error codes which are considered as successful.
	 * @param array  $supresserrors Array containing PostgreSQL error codes which are considered as errors requiring another attempt.
	 *
	 * @return SQLresult
	 */

	abstract protected function execUntilSuccess($query, $blob, array $ignoreerrors = array(), array $supresserrors = array());


	/**
	 * Escaping strings for SQL statement
	 *
	 * @param string $s String to be escaped
	 *
	 * @return string
	 */

	public function sqlText($s)
	    {
		return "'" . str_replace("'", "''", $s) . "'";
	    } //end sqlText()


	/**
	 * Execute SQL query on DB engine until successful
	 *
	 * @param string $query         SQL query to execute
	 * @param array  $ignoreerrors  Array containing PostgreSQL error codes which are considered as successful.
	 * @param array  $supresserrors Array containing PostgreSQL error codes which are considered as errors requiring another attempt.
	 *
	 * @return SQLresult
	 */

	public function execUntilSuccessful($query, array $ignoreerrors = array(), array $supresserrors = array())
	    {
		return $this->execUntilSuccess($query, false, $ignoreerrors, $supresserrors);
	    } //end execUntilSuccessful()


	/**
	 * Execute SQL query on DB engine until successful
	 *
	 * @param string $query         SQL query to execute
	 * @param string $blob          Binary blob to accompany SQL query
	 * @param array  $ignoreerrors  Array containing PostgreSQL error codes which are considered as successful.
	 * @param array  $supresserrors Array containing PostgreSQL error codes which are considered as errors requiring another attempt.
	 *
	 * @return SQLresult
	 */

	public function execBinaryBlobUntilSuccessful($query, $blob, array $ignoreerrors = array(), array $supresserrors = array())
	    {
		return $this->execUntilSuccess($query, $blob, $ignoreerrors, $supresserrors);
	    } //end execBinaryBlobUntilSuccessful()


	/**
	 * Execute query
	 *
	 * @param string $query SQL query to execute
	 * @param mixed  $blob  Binary Large Object to accompany SQL query or false is SQL query is simple
	 *
	 * @return mixed execution result
	 */

	protected function execQuery($query, $blob)
	    {
		if ($blob === false)
		    {
			return $this->exec($query);
		    }
		else
		    {
			return $this->execBinaryBlob($query, $blob);
		    }
	    } //end execQuery()


	/**
	 * Report an error
	 *
	 * @param int    $error        Error code
	 * @param string $errormessage Error message
	 * @param string $query        Query which caused the error
	 *
	 * @return void
	 *
	 * @untranslatable Stuck executing SQL,
	 * @untranslatable no error
	 * @untranslatable error
	 * @untranslatable in
	 */

	protected function reportError($error, $errormessage, $query)
	    {
		trigger_error(
		    "Stuck executing SQL, " .
		    (($error === 0) ? "no error" : "error " . $error . ": " . $errormessage) .
		    " in " . $query, E_USER_WARNING
		);
	    } //end reportError()


    } //end class

?>
