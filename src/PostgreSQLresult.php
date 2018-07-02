<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

/**
 * Class for results of SQL queries from PostgreSQL DB engine
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/PostgreSQLresult.php $
 */

class PostgreSQLresult extends SQLresult
    {

	/**
	 * Result resource
	 *
	 * @var resource
	 */
	private $_result;

	/**
	 * Row number
	 *
	 * @var int
	 */
	private $_row;

	/**
	 * Instantiate this class
	 *
	 * @param resource $result Resource containing SQL response
	 *
	 * @return void
	 */

	public function __construct($result)
	    {
		$this->_result = $result;
		$this->_row    = 0;
	    } //end __construct()


	/**
	 * Confirm successful execution of last SQL query
	 *
	 * @return bool
	 */

	public function isSuccessful()
	    {
		if (is_resource($this->_result) === true)
		    {
			return true;
		    }
		else
		    {
			return false;
		    }
	    } //end isSuccessful()


	/**
	 * Get number of rows produced by SQL query
	 *
	 * @return int
	 */

	public function getNumRows()
	    {
		return pg_numrows($this->_result);
	    } //end getNumRows()


	/**
	 * Get associated row contents
	 *
	 * @return array
	 */

	public function getRow()
	    {
		if ($this->GetNumRows() > $this->_row)
		    {
			$arr = pg_fetch_array($this->_result, $this->_row, PGSQL_ASSOC);
			$this->_row++;
		    }
		else
		    {
			$arr = false;
		    }

		return $arr;
	    } //end getRow()


    } //end class

?>
