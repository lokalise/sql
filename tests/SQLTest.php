<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Closure;
use \Logics\Foundation\SQL\SQL;
use \PHPUnit_Framework_TestCase;

/**
 * This is commonly used method for testing MySQLdatabase and PostgreSQLdatabase classes
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/SQLTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SQLTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test shared connections
	 *
	 * @return void
	 */

	public function testCreatesNewSharedMysqlConnectionAndHandsItOutOnEachSubsequentCall()
	    {
		define("DBHOST", $GLOBALS["DB_HOST"]);
		define("DBNAME", $GLOBALS["DB_DBNAME"]);
		define("DBUSER", $GLOBALS["DB_USER"]);
		define("DBPASS", $GLOBALS["DB_PASSWD"]);

		$db1 = SQL::get("MySQL");
		$db2 = SQL::get("MySQL");

		$threadid = function ($db)
		    {
			return $db->db->thread_id;
		    };

		$threadid = Closure::bind($threadid, null, $db1);

		$this->assertEquals($threadid($db1), $threadid($db2));
	    } //end testCreatesNewSharedMysqlConnectionAndHandsItOutOnEachSubsequentCall()


	/**
	 * Test different ways of supplying credentials to the driver
	 *
	 * @return void
	 */

	public function testMysqlCanGetCredentialsDirectlyOrFromMysqlSpecificConfigOrFromGlobalConfig()
	    {
		define("DBHOST", $GLOBALS["DB_HOST"]);
		define("DBNAME", $GLOBALS["DB_DBNAME"]);
		define("DBUSER", $GLOBALS["DB_USER"]);
		define("DBPASS", $GLOBALS["DB_PASSWD"]);

		$db = SQL::get("MySQL", false);
		$this->assertInstanceOf("Logics\Foundation\SQL\MySQLdatabase", $db);

		define("MYSQL_DBHOST", $GLOBALS["DB_HOST"]);
		define("MYSQL_DBNAME", $GLOBALS["DB_DBNAME"]);
		define("MYSQL_DBUSER", $GLOBALS["DB_USER"]);
		define("MYSQL_DBPASS", $GLOBALS["DB_PASSWD"]);

		$db = SQL::get("MySQL", false);
		$this->assertInstanceOf("Logics\Foundation\SQL\MySQLdatabase", $db);

		$db = SQL::get("MySQL", false, $GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertInstanceOf("Logics\Foundation\SQL\MySQLdatabase", $db);
	    } //end testMysqlCanGetCredentialsDirectlyOrFromMysqlSpecificConfigOrFromGlobalConfig()


	/**
	 * Test shared connections
	 *
	 * @return void
	 */

	public function testCreatesNewSharedPostgresqlConnectionAndHandsItOutOnEachSubsequentCall()
	    {
		define("DBHOST", $GLOBALS["DB_HOST"]);
		define("DBNAME", $GLOBALS["DB_DBNAME"]);
		define("DBUSER", $GLOBALS["DB_USER"]);
		define("DBPASS", $GLOBALS["DB_PASSWD"]);

		$db1 = SQL::get("PostgreSQL");
		$db2 = SQL::get("PostgreSQL");

		$this->assertEquals($db1, $db2);
	    } //end testCreatesNewSharedPostgresqlConnectionAndHandsItOutOnEachSubsequentCall()


	/**
	 * Test different ways of supplying credentials to the driver
	 *
	 * @return void
	 */

	public function testPostgresqlCanGetCredentialsDirectlyOrFromMysqlSpecificConfigOrFromGlobalConfig()
	    {
		define("DBHOST", $GLOBALS["DB_HOST"]);
		define("DBNAME", $GLOBALS["DB_DBNAME"]);
		define("DBUSER", $GLOBALS["DB_USER"]);
		define("DBPASS", $GLOBALS["DB_PASSWD"]);

		$db = SQL::get("PostgreSQL", false);
		$this->assertInstanceOf("Logics\Foundation\SQL\PostgreSQLdatabase", $db);

		define("POSTGRESQL_DBHOST", $GLOBALS["DB_HOST"]);
		define("POSTGRESQL_DBNAME", $GLOBALS["DB_DBNAME"]);
		define("POSTGRESQL_DBUSER", $GLOBALS["DB_USER"]);
		define("POSTGRESQL_DBPASS", $GLOBALS["DB_PASSWD"]);

		$db = SQL::get("PostgreSQL", false);
		$this->assertInstanceOf("Logics\Foundation\SQL\PostgreSQLdatabase", $db);

		$db = SQL::get("PostgreSQL", false, $GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertInstanceOf("Logics\Foundation\SQL\PostgreSQLdatabase", $db);
	    } //end testPostgresqlCanGetCredentialsDirectlyOrFromMysqlSpecificConfigOrFromGlobalConfig()


	/**
	 * Test for MySQL without credentials
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 */

	public function testThrowsExceptionIfNoCredentialsForMysqlProvided()
	    {
		define("EXCEPTION_NO_MYSQL_CONNECTION_DETAILS", 1);

		$db = SQL::get("MySQL");
		unset($db);
	    } //end testThrowsExceptionIfNoCredentialsForMysqlProvided()


	/**
	 * Test for PostgreSQL without credentials
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 */

	public function testThrowsExceptionIfNoCredentialsForPostgresqlProvided()
	    {
		define("EXCEPTION_NO_POSTGRESQL_CONNECTION_DETAILS", 1);

		$db = SQL::get("PostgreSQL");
		unset($db);
	    } //end testThrowsExceptionIfNoCredentialsForPostgresqlProvided()


	/**
	 * Test for unknown driver
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 */

	public function testThrowsExceptionIfSqlDriverIsUnknown()
	    {
		define("EXCEPTION_UNKNOWN_SQL_DRIVER", 1);

		$db = SQL::get("unknown");
		unset($db);
	    } //end testThrowsExceptionIfSqlDriverIsUnknown()


    } //end class

?>
