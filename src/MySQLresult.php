<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

use \mysqli_result;

/**
 * Class for results of SQL queries from MySQL DB engine
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/MySQLresult.php $
 */

class MySQLresult extends SQLresult
    {

	/**
	 * MySQLi result
	 *
	 * @var mysqli_result
	 */
	private $_result;

	/**
	 * Instantiate this class
	 *
	 * @param resource $result Object containing SQL response
	 *
	 * @return void
	 */

	public function __construct($result)
	    {
		$this->_result = $result;
	    } //end __construct()


	/**
	 * Confirm successful execution of last SQL query
	 *
	 * @return bool
	 */

	public function isSuccessful()
	    {
		if ($this->_result instanceof mysqli_result || $this->_result === true)
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
		if (($this->_result instanceof mysqli_result) === false)
		    {
			return false;
		    }
		else
		    {
			return $this->_result->num_rows;
		    }
	    } //end getNumRows()


	/**
	 * Get associated row contents
	 *
	 * @return array
	 */

	public function getRow()
	    {
		if (($this->_result instanceof mysqli_result) === false)
		    {
			return false;
		    }
		else
		    {
			return $this->_result->fetch_assoc();
		    }
	    } //end getRow()


    } //end class

?>
