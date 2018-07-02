<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

/**
 * Class for access to PostgreSQL databases
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/PostgreSQLdatabase.php $
 */

class PostgreSQLdatabase extends SQLdatabase
    {

	const REPORT_ERROR_ON_ITERATION = 100;

	const RETRY_STANDOFF_TIME = 100000;

	/**
	 * Database connection
	 *
	 * @var resource
	 */
	protected $db;

	/**
	 * PostgreSQL connection string
	 *
	 * @var string
	 */
	protected $connectionString;

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
	 * @untranslatable PostgreSQL
	 * @untranslatable host=
	 * @untranslatable dbname=
	 * @untranslatable user=
	 * @untranslatable password=
	 * @untranslatable options='--client_encoding=UTF8'
	 */

	public function __construct($dbhost, $dbname, $dbuser, $dbpass)
	    {
		parent::__construct($dbhost, $dbname, $dbuser, $dbpass);

		$this->type             = "PostgreSQL";
		$this->connectionString = (($this->dbhost === "") ? "" : "host=" . $this->dbhost . " ") .
		    "dbname=" . $this->dbname . " user=" . $this->dbuser . " password=" . $this->dbpass . " options='--client_encoding=UTF8'";

		$this->__wakeup();
	    } //end __construct()


	/**
	 * Wakeup magic method: reconnects database as it is comes up disconnected on unserialization
	 *
	 * @return void
	 */

	public function __wakeup()
	    {
		$this->db = pg_connect($this->connectionString, PGSQL_CONNECT_FORCE_NEW);
	    } //end __wakeup()


	/**
	 * Sleep magic method: disconnects database
	 *
	 * @return array List of class properties to serialize
	 */

	public function __sleep()
	    {
		if ($this->isConnected() === true)
		    {
			pg_close($this->db);
			$this->db = false;
		    }

		return array_keys(get_object_vars($this));
	    } //end __sleep()


	/**
	 * Check if SQL engine is connected
	 *
	 * @return boolean true if connected
	 */

	public function isConnected()
	    {
		return is_resource($this->db);
	    } //end isConnected()


	/**
	 * Execute SQL query on DB engine
	 *
	 * @param string $query SQL query to execute
	 *
	 * @return SQLresult
	 */

	public function exec($query)
	    {
		if ($this->isConnected() === false)
		    {
			$this->__wakeup();
		    }

		if ($this->isConnected() === true)
		    {
			$result = new PostgreSQLresult(pg_exec($this->db, $query));
			if ($result->isSuccessful() === true)
			    {
				return $result;
			    }
			else
			    {
				return false;
			    }
		    }
		else
		    {
			return false;
		    }
	    } //end exec()


	/**
	 * Execute SQL query on DB engine
	 *
	 * @param string $query SQL query to execute
	 * @param string $blobs Binary blob or array of blobs to accompany SQL query
	 *
	 * @return SQLresult
	 */

	public function execBinaryBlob($query, $blobs)
	    {
		if ($this->isConnected() === false)
		    {
			$this->__wakeup();
		    }

		if ($this->isConnected() === true)
		    {
			$stmt = pg_prepare($this->db, "", $query);
			if ($stmt !== false)
			    {
				if (is_array($blobs) === false)
				    {
					$blobs = array($blobs);
				    }

				return is_resource(pg_execute($this->db, "", $blobs));
			    }
			else
			    {
				return false;
			    } //end if
		    }
		else
		    {
			return false;
		    } //end if
	    } //end execBinaryBlob()


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

	protected function execUntilSuccess($query, $blob, array $ignoreerrors = array(), array $supresserrors = array())
	    {
		declare(ticks = 1);

		$lasterror      = 0;
		$lasterrorcount = 0;
		do
		    {
			$result = $this->execQuery($query, $blob);

			if ($result === false)
			    {
				$error = ((is_resource($this->db) === true) ? strtok(pg_last_error($this->db), "\n") : "");
				if (in_array($error, $ignoreerrors) === true)
				    {
					break;
				    }

				usleep((self::RETRY_STANDOFF_TIME * min($lasterrorcount, self::REPORT_ERROR_ON_ITERATION)));

				if (in_array($error, $supresserrors) === false)
				    {
					if ($lasterror === $error)
					    {
						$lasterrorcount++;
						if ($lasterrorcount === self::REPORT_ERROR_ON_ITERATION)
						    {
							$this->reportError($error, pg_last_error($this->db), $query);
						    }
					    }
					else
					    {
						$lasterror      = $error;
						$lasterrorcount = 0;
					    }
				    }
			    } //end if
		    } while ($result === false);
		return $result;
	    } //end execUntilSuccess()


	/**
	 * Get list of fields of the table.
	 *
	 * @param string $table Table name to get fields from
	 *
	 * @return array
	 */

	public function fields($table)
	    {
		$result = $this->exec("SELECT column_name FROM information_schema.columns WHERE table_name = '" . strtolower($table) . "'");
		if (($result instanceof PostgreSQLresult) === true)
		    {
			$fields = array();
			while ($row = $result->GetRow())
			    {
				$fields[] = $row["column_name"];
			    }

			return ((count($fields) === 0) ? false : $fields);
		    }
		else
		    {
			return false;
		    }
	    } //end fields()


	/**
	 * Get last inserted row ID.
	 *
	 * @return int
	 */

	public function insertID()
	    {
		$error = pg_last_error($this->db);
		if ($error === false || $error === "")
		    {
			$insertquery = pg_query("SELECT lastval();");
			if ($insertquery !== false)
			    {
				$insertrow = pg_fetch_row($insertquery);
				return $insertrow[0];
			    }
			else
			    {
				return false;
			    }
		    }
		else
		    {
			return false;
		    }
	    } //end insertID()


	/**
	 * Escaping strings for SQL statement
	 *
	 * @param string $s String to be escaped
	 *
	 * @return string
	 */

	public function sqlText($s)
	    {
		if ($this->isConnected() === true)
		    {
			return pg_escape_literal($this->db, $s);
		    }
		else
		    {
			return parent::sqlText($s);
		    }
	    } //end sqlText()


    } //end class

?>
