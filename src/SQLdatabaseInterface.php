<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\SQL
 */

namespace Logics\Foundation\SQL;

/**
 * Interface for access to SQL databases
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/src/SQLdatabaseInterface.php $
 */

interface SQLdatabaseInterface
    {

	/**
	 * Check if SQL engine is connected
	 *
	 * @return boolean true if connected
	 */

	public function isConnected();


	/**
	 * Execute SQL query on DB engine
	 *
	 * @param string $query SQL query to execute
	 *
	 * @return SQLresult
	 */

	public function exec($query);


	/**
	 * Execute SQL query on DB engine until successful
	 *
	 * @param string $query         SQL query to execute
	 * @param array  $ignoreerrors  Array containing PostgreSQL error codes which are considered as successful.
	 * @param array  $supresserrors Array containing PostgreSQL error codes which are considered as errors requiring another attempt.
	 *
	 * @return SQLresult
	 */

	public function execUntilSuccessful($query, array $ignoreerrors = array(), array $supresserrors = array());


	/**
	 * Execute SQL query on DB engine
	 *
	 * @param string $query SQL query to execute
	 * @param string $blobs Binary blob or array of blobs to accompany SQL query
	 *
	 * @return SQLresult
	 */

	public function execBinaryBlob($query, $blobs);


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

	public function execBinaryBlobUntilSuccessful($query, $blob, array $ignoreerrors = array(), array $supresserrors = array());


	/**
	 * Get list of fields of the table.
	 *
	 * @param string $table Table name to get fields from
	 *
	 * @return array
	 */

	public function fields($table);


	/**
	 * Get last inserted row ID.
	 *
	 * @return int
	 */

	public function insertID();


    } //end interface

?>
