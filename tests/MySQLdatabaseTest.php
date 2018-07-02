<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Exception;
use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Tests\GetConnectionMySQL;

/**
 * Test for MySQLdatabase class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/MySQLdatabaseTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class MySQLdatabaseTest extends SQLdatabaseTestBase
    {

	use GetConnectionMySQL;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$conn = $this->getConnection();
		$db   = $conn->getConnection();
		$db->exec(
		    "CREATE TABLE IF NOT EXISTS `MySQLdatabase` (" .
		    "id int NOT NULL AUTO_INCREMENT, " .
		    "string text NOT NULL, " .
		    "testblob longblob NOT NULL, " .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);

		parent::setUp();
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		unset($this->object);

		$conn = $this->getConnection();
		$db   = $conn->getConnection();
		$db->exec("DROP TABLE IF EXISTS `MySQLdatabase`;");

		unset($GLOBALS["errstr"]);
		unset($GLOBALS["stuckerror"]);
	    } //end tearDown()


	/**
	 * Test exec()
	 *
	 * @return void
	 */

	public function testCanExecuteSqlQueries()
	    {
		$this->assertEquals(false, $this->object->exec("SELECT * FROM nonexistenttable"));
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $this->object->exec("SELECT * FROM MySQLdatabase"));

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->exec("SELECT * FROM MySQLdatabase"));
	    } //end testCanExecuteSqlQueries()


	/**
	 * Test execBinaryBlob()
	 *
	 * @return void
	 */

	public function testCanExecuteSqlQueriesWithLargeBinaryBlobs()
	    {
		$blob = str_repeat("s", (1024 * 1024 * 8));
		$this->assertEquals(false, $this->object->execBinaryBlob("INSERT INTO nonexistenttable SET testblob = ?", $blob));
		$this->assertEquals(true, $this->object->execBinaryBlob("INSERT INTO MySQLdatabase SET testblob = ?", $blob));

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->execBinaryBlob("INSERT INTO MySQLdatabase SET testblob = ?", $blob));
	    } //end testCanExecuteSqlQueriesWithLargeBinaryBlobs()


	/**
	 * Test execUntilSuccessful()
	 *
	 * @requires extension pcntl
	 *
	 * @return void
	 */

	public function testCanExecuteSqlQueryRepeatedlyUntilItSucceeds()
	    {
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $this->object->execUntilSuccessful("SELECT * FROM MySQLdatabase"));

		set_error_handler(array($this, "errorHandler"));
		pcntl_signal(SIGALRM, array($this, "timeoutCallback"), true);

		pcntl_alarm(540);

		try
		    {
			$this->object->execUntilSuccessful("SELECT * FROM nonexistenttable");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(true, (($e->getMessage() === "Timeout") && strpos($GLOBALS["errstr"], "Stuck executing SQL") !== false));
		    }

		pcntl_alarm(5);

		try
		    {
			$this->assertFalse($this->object->execUntilSuccessful("SELECT * FROM nonexistenttable", array(1146)));
			pcntl_alarm(0);
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(false, ($e->getMessage() === "Timeout"));
		    }

		restore_error_handler();

		$blob = "blob";
		$this->assertEquals(true, $this->object->execBinaryBlobUntilSuccessful("INSERT INTO MySQLdatabase SET testblob = ?", $blob));
	    } //end testCanExecuteSqlQueryRepeatedlyUntilItSucceeds()


	/**
	 * Test sqlText()
	 *
	 * @return void
	 */

	public function testCanEscapeTextInLiteralValuesSoItCanBeUsedDirectlyInSqlQuery()
	    {
		$this->assertEquals("'test'", $this->object->sqlText("test"));
		$this->assertEquals("'won\'t'", $this->object->sqlText("won't"));

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals("'test'", $this->object->sqlText("test"));
		$this->assertEquals("'won''t'", $this->object->sqlText("won't"));
	    } //end testCanEscapeTextInLiteralValuesSoItCanBeUsedDirectlyInSqlQuery()


	/**
	 * Test insertID()
	 *
	 * @return void
	 */

	public function testCanReturnInsertIdAfterSqlQueryExecution()
	    {
		$this->object->exec("INSERT INTO MySQLdatabase SET string = 'test1'");
		$this->assertEquals(1, $this->object->insertID());
		$this->object->exec("INSERT INTO MySQLdatabase SET string = 'test2'");
		$this->assertEquals(2, $this->object->insertID());

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->object->exec("INSERT INTO MySQLdatabase SET string = 'test1'");
		$this->assertEquals(false, $this->object->insertID());
	    } //end testCanReturnInsertIdAfterSqlQueryExecution()


	/**
	 * Test fields()
	 *
	 * @return void
	 */

	public function testCanReturnListOfTableFields()
	    {
		$this->assertEquals(false, $this->object->fields("nonexistenttable"));
		$this->assertEquals(array("id", "string", "testblob"), $this->object->fields("MySQLdatabase"));
	    } //end testCanReturnListOfTableFields()


	/**
	 * Test to use default database
	 *
	 * @return void
	 */

	public function testIfNoMysqlCredentialsAreSuppliedGlobalDefaultSettingsAreUsedInstead()
	    {
		$GLOBALS["DEFAULT_DATABASE"]["dbhost"] = $GLOBALS["DB_HOST"];
		$GLOBALS["DEFAULT_DATABASE"]["dbname"] = $GLOBALS["DB_DBNAME"];
		$GLOBALS["DEFAULT_DATABASE"]["dbuser"] = $GLOBALS["DB_USER"];
		$GLOBALS["DEFAULT_DATABASE"]["dbpass"] = $GLOBALS["DB_PASSWD"];
		$this->object = new MySQLdatabase("", "", "", "");
		$this->assertEquals(true, $this->object->isConnected());
	    } //end testIfNoMysqlCredentialsAreSuppliedGlobalDefaultSettingsAreUsedInstead()


	/**
	 * Test attempt to open non-existent database
	 *
	 * @return void
	 */

	public function testDoesNotConnectToDatabaseServerIfCredentialsAreIncorrect()
	    {
		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->isConnected());
	    } //end testDoesNotConnectToDatabaseServerIfCredentialsAreIncorrect()


	/**
	 * Test restoring settings in magic method '__wakeup'
	 *
	 * @return void
	 */

	public function testShouldRestoreCharsetOnWakeUp()
	    {
		$this->object->exec("SET NAMES 'cp1251'");

		$sample = "Пример текста в кодировке UTF-8";
		$result = $this->object->exec("INSERT INTO MySQLdatabase (string) VALUES(" . $this->object->sqlText($sample) . ")");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$result = $this->object->exec("SELECT string FROM MySQLdatabase");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(1, $result->getNumRows());
		$this->assertEquals(array("string" => $sample), $result->getRow());

		$this->object = unserialize(serialize($this->object));

		$result = $this->object->exec("SELECT string FROM MySQLdatabase");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(1, $result->getNumRows());
		$this->assertEquals(array("string" => $sample), $result->getRow());

		serialize($this->object);

		$result = $this->object->exec("SELECT string FROM MySQLdatabase");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(1, $result->getNumRows());
		$this->assertEquals(array("string" => $sample), $result->getRow());
	    } //end testShouldRestoreCharsetOnWakeUp()


	/**
	 * Test restoring settings in magic method '__wakeup'
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 */

	public function testFailsToWakeUpIfCannotRestoreSettings()
	    {
		define("EXCEPTION_CANNOT_SET_CONNECTION_SETTINGS", 1);

		$serialized   = serialize($this->object);
		$serialized   = str_replace("s:20:\"character_set_client\";", "s:11:\"unknown_var\";", $serialized);
		$this->object = unserialize($serialized);
	    } //end testFailsToWakeUpIfCannotRestoreSettings()


    } //end class

?>
