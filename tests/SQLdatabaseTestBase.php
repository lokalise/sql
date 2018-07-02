<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Exception;
use \Logics\Tests\DefaultDataSet;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * This is commonly used method for testing MySQLdatabase and PostgreSQLdatabase classes
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/SQLdatabaseTestBase.php $
 *
 * @donottranslate
 */

abstract class SQLdatabaseTestBase extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use DefaultDataSet;

	/**
	 * Testing object
	 *
	 * @var SQLdatabase
	 */
	protected $object;

	/**
	 * Handler for PHP errors: we just need to record what error has occured so we can test for it
	 *
	 * @param int    $errno      Contains the level of the error raised
	 * @param string $errstr     Contains the error message
	 * @param string $errfile    Contains the filename that the error was raised in
	 * @param int    $errline    Contains the line number the error was raised at
	 * @param array  $errcontext An array that points to the active symbol table at the point the error occurred
	 *
	 * @return boolean true if script should continue execution
	 */

	public static function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
	    {
		unset($errno);
		$GLOBALS["errstr"] = $errstr;
		if (strpos($errstr, "Stuck executing SQL") !== false)
		    {
			$GLOBALS["stuckerror"] = $errstr;
		    }

		unset($errfile);
		unset($errline);
		unset($errcontext);
		return true;
	    } //end errorHandler()


	/**
	 * This method is called by SIGALRM system signal on timeout
	 *
	 * @return void
	 *
	 * @throws Exception Timeout message
	 */

	public function timeoutCallback()
	    {
		throw new Exception("Timeout", 0);
	    } //end timeoutCallback()


	/**
	 * Test isConnected()
	 *
	 * @return void
	 */

	public function testCanTellWhetherDatabaseServerIsConnected()
	    {
		$this->assertEquals(true, $this->object->isConnected());
	    } //end testCanTellWhetherDatabaseServerIsConnected()


	/**
	 * Test magic method '__sleep'
	 *
	 * @return void
	 */

	public function testShouldDisconnectFromServerOnSleep()
	    {
		$this->assertTrue($this->object->isConnected());
		serialize($this->object);
		$this->assertFalse($this->object->isConnected());
	    } //end testShouldDisconnectFromServerOnSleep()


    } //end class

?>
