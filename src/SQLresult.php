<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

/**
 * Abstract class for results of SQL queries
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/SQLresult.php $
 */

abstract class SQLresult
    {

	/**
	 * Instantiate this class
	 *
	 * @param resource $result Resource containing SQL response
	 *
	 * @return void
	 */

	abstract public function __construct($result);


	/**
	 * Confirm successful execution of last SQL query
	 *
	 * @return bool
	 */

	abstract public function isSuccessful();


	/**
	 * Get number of rows produced by SQL query
	 *
	 * @return int
	 */

	abstract public function getNumRows();


	/**
	 * Get associated row contents
	 *
	 * @return array
	 */

	abstract public function getRow();


    } //end class

?>
