<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

use \Exception;
use \mysqli;

/**
 * Class for access to MySQL databases
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/MySQLdatabase.php $
 */

class MySQLdatabase extends SQLdatabase
    {

	const REPORT_ERROR_ON_ITERATION = 100;

	const RETRY_STANDOFF_TIME = 100000;

	/**
	 * MySQLi connection
	 *
	 * @var mysqli
	 */
	protected $db;

	/**
	 * MySQL settings to be applied on connection
	 *
	 * @var array
	 */
	protected $settings;

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
	 * @untranslatable MySQL
	 */

	public function __construct($dbhost, $dbname, $dbuser, $dbpass)
	    {
		$this->type     = "MySQL";
		$this->settings = array();
		parent::__construct($dbhost, $dbname, $dbuser, $dbpass);
		$this->__wakeup();
	    } //end __construct()


	/**
	 * Wakeup magic method: reconnects database as it is comes up disconnected on unserialization
	 *
	 * @return void
	 *
	 * @throws Exception Default settings were not restored
	 *
	 * @exceptioncode EXCEPTION_CANNOT_SET_CONNECTION_SETTINGS
	 *
	 * @untranslatable 'UTF8'
	 * @untranslatable SET
	 */

	public function __wakeup()
	    {
		mysqli_report(MYSQLI_REPORT_STRICT);
		$this->db = new mysqli($this->dbhost, $this->dbuser, $this->dbpass);
		if (($this->db instanceof mysqli) === true)
		    {
			if ($this->db->select_db($this->dbname) === true)
			    {
				$settings = array("NAMES" => "'UTF8'");
				foreach ($this->settings as $key => $value)
				    {
					$settings["`" . $key . "` ="] = $this->sqlText($value);
				    }

				foreach ($settings as $key => $value)
				    {
					$query = "SET " . $key . " " . $value;
					if ($this->exec($query) === false)
					    {
						throw new Exception(_("Query '") . $query . _("' has failed during unserialization"), EXCEPTION_CANNOT_SET_CONNECTION_SETTINGS);
					    }
				    }
			    }
			else
			    {
				if ($this->db->close() === true)
				    {
					$this->db = false;
				    }
			    } //end if
		    } //end if
	    } //end __wakeup()


	/**
	 * Sleep magic method: disconnects database
	 *
	 * @return array List of class properties to serialize
	 *
	 * @untranslatable character_set_system
	 * @untranslatable character_sets_dir
	 */

	public function __sleep()
	    {
		if ($this->isConnected() === true)
		    {
			$this->settings = array();

			$settingsList = $this->exec("SHOW VARIABLES WHERE `Variable_name` REGEXP '(character_set.*)|(collation.*)'");
			while ($settings = $settingsList->getRow())
			    {
				if (($settings["Variable_name"] !== "character_set_system") &&
				    ($settings["Variable_name"] !== "character_sets_dir"))
				    {
					$this->settings[$settings["Variable_name"]] = $settings["Value"];
				    }
			    }

			$this->db->close();
			$this->db = false;
		    }

		return array_keys(get_object_vars($this));
	    } //end __sleep()


	/**
	 * Check if SQL engine is connected
	 *
	 * @return boolean true if connected
	 *
	 * @untranslatable (Broken pipe)
	 */

	public function isConnected()
	    {
		$isdbok          = $this->db instanceof mysqli;
		$suppressLogging = "(Broken pipe)";
		$suppressLogging = $suppressLogging;
		return ($isdbok === true && $this->db->ping() === true);
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
			$result = new MySQLresult($this->db->query($query));
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
	 *
	 * @untranslatable b
	 * @untranslatable bind_param
	 */

	public function execBinaryBlob($query, $blobs)
	    {
		if ($this->isConnected() === false)
		    {
			$this->__wakeup();
		    }

		if ($this->isConnected() === true)
		    {
			$stmt = $this->db->prepare($query);
			if ($stmt !== false)
			    {
				if (is_array($blobs) === false)
				    {
					$blobs = array($blobs);
				    }

				$params = array_merge(array(str_repeat("b", count($blobs))), array_fill(0, count($blobs), null));
				foreach ($params as $key => $value)
				    {
					unset($value);
					$references[$key] = &$params[$key];
				    }

				call_user_func_array(array($stmt, "bind_param"), $references);

				$i = 0;
				foreach ($blobs as $blob)
				    {
					$stmt->send_long_data($i, $blob);
					$i++;
				    }

				return $stmt->execute();
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
	 * @param array  $ignoreerrors  Array containing MySQL error codes which are considered as successful.
	 *                              Notably error 1062 ER_DUP_ENTRY quite often is OK as record already does exist.
	 * @param array  $supresserrors Array containing MySQL error codes which are considered as errors requiring another attempt.
	 *                              Notably error 1205 ER_LOCK_WAIT_TIMEOUT should cause another attempt to execute statement.
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
				$error = ((($this->db instanceof mysqli) === true) ? $this->db->errno : 0);
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
							$this->reportError($error, $this->db->error, $query);
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
	 *
	 * @untranslatable DESCRIBE
	 */

	public function fields($table)
	    {
		$result = $this->exec("DESCRIBE " . $table);
		if (($result instanceof MySQLresult) === true)
		    {
			$fields = array();
			while ($row = $result->GetRow())
			    {
				$fields[] = $row["Field"];
			    }

			return $fields;
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
		if ($this->isConnected() === false)
		    {
			$this->__wakeup();
		    }

		if ($this->isConnected() === true)
		    {
			return $this->db->insert_id;
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
		if (($this->db instanceof mysqli) === true)
		    {
			return "'" . $this->db->real_escape_string($s) . "'";
		    }
		else
		    {
			return parent::sqlText($s);
		    }
	    } //end sqlText()


    } //end class

?>
