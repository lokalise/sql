<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Exception;
use \Logics\Foundation\SQL\PostgreSQLdatabase;
use \Logics\Tests\GetConnectionPostgreSQL;

/**
 * Test for PostgreSQLdatabase class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/PostgreSQLdatabaseTest.php $
 *
 * @donottranslate
 */

class PostgreSQLdatabaseTest extends SQLdatabaseTestBase
    {
	use GetConnectionPostgreSQL;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$conn = $this->getConnection();
		$sql  = $conn->getConnection();

		$sql->exec("DROP TABLE IF EXISTS PostgreSQLdatabase");
		$sql->exec("DROP SEQUENCE IF EXISTS id_seq");

		$sql->exec("CREATE SEQUENCE id_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1");
		$sql->exec(
		    "CREATE TABLE IF NOT EXISTS PostgreSQLdatabase (" .
		    "id int PRIMARY KEY DEFAULT nextval('id_seq'), " .
		    "string text, " .
		    "testblob bytea" .
		    ")"
		);

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);

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
		$sql  = $conn->getConnection();
		$sql->exec("DROP TABLE IF EXISTS PostgreSQLdatabase");
		$sql->exec("DROP SEQUENCE id_seq");

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
		set_error_handler(array($this, "errorHandler"));

		$this->assertEquals(false, $this->object->exec("SELECT * FROM nonexistenttable"));
		$this->assertContains("relation \"nonexistenttable\" does not exist", $GLOBALS["errstr"]);
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $this->object->exec("SELECT * FROM PostgreSQLdatabase"));

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->exec("SELECT * FROM PostgreSQLdatabase"));

		restore_error_handler();
	    } //end testCanExecuteSqlQueries()


	/**
	 * Test execBinaryBlob()
	 *
	 * @return void
	 */

	public function testCanExecuteSqlQueriesWithLargeBinaryBlobs()
	    {
		set_error_handler(array($this, "errorHandler"));

		$blob = "blob";
		$this->assertEquals(false, $this->object->execBinaryBlob("INSERT INTO nonexistenttable (testblob) VALUES ($1)", $blob));
		$this->assertContains("relation \"nonexistenttable\" does not exist", $GLOBALS["errstr"]);
		$this->assertEquals(true, $this->object->execBinaryBlob("INSERT INTO PostgreSQLdatabase (testblob) VALUES ($1)", $blob));

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->execBinaryBlob("INSERT INTO PostgreSQLdatabase (testblob) VALUES ($1)", $blob));

		restore_error_handler();
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
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $this->object->execUntilSuccessful("SELECT * FROM PostgreSQLdatabase"));

		set_error_handler(array($this, "errorHandler"));
		pcntl_signal(SIGALRM, array($this, "timeoutCallback"), true);

		pcntl_alarm(540);

		try
		    {
			$this->object->execUntilSuccessful("SELECT * FROM nonexistenttable");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(true, (($e->getMessage() === "Timeout") && strpos($GLOBALS["stuckerror"], "Stuck executing SQL") !== false));
		    }

		pcntl_alarm(5);

		try
		    {
			$this->assertFalse(
			    $this->object->execUntilSuccessful("SELECT * FROM nonexistenttable", array("ERROR:  relation \"nonexistenttable\" does not exist"))
			);
			pcntl_alarm(0);
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(false, ($e->getMessage() === "Timeout"));
		    }

		restore_error_handler();

		$blob = "blob";
		$this->assertEquals(true, $this->object->execBinaryBlobUntilSuccessful("INSERT INTO PostgreSQLdatabase (testblob) VALUES ($1)", $blob));
	    } //end testCanExecuteSqlQueryRepeatedlyUntilItSucceeds()


	/**
	 * Test sqlText()
	 *
	 * @return void
	 */

	public function testCanEscapeTextInLiteralValuesSoItCanBeUsedDirectlyInSqlQuery()
	    {
		set_error_handler(array($this, "errorHandler"));

		$this->assertEquals("'test'", $this->object->sqlText("test"));
		$this->assertEquals("'won''t'", $this->object->sqlText("won't"));

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertContains("database \"nonexistentdb\" does not exist", $GLOBALS["errstr"]);
		$this->assertEquals("'test'", $this->object->sqlText("test"));
		$this->assertEquals("'won''t'", $this->object->sqlText("won't"));

		restore_error_handler();
	    } //end testCanEscapeTextInLiteralValuesSoItCanBeUsedDirectlyInSqlQuery()


	/**
	 * Test insertID()
	 *
	 * @return void
	 */

	public function testCanReturnInsertIdAfterSqlQueryExecution()
	    {
		set_error_handler(array($this, "errorHandler"));

		$this->assertEquals(false, $this->object->insertID());

		$this->object->exec("INSERT INTO PostgreSQLdatabase (string) VALUES ('test1')");
		$this->assertEquals(1, $this->object->insertID());
		$this->object->exec("INSERT INTO PostgreSQLdatabase (string) VALUES ('test2')");
		$this->assertEquals(2, $this->object->insertID());

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->object->exec("INSERT INTO PostgreSQLdatabase (string) VALUES ('test1')");
		$this->assertContains("database \"nonexistentdb\" does not exist", $GLOBALS["errstr"]);
		$this->assertEquals(false, $this->object->insertID());

		restore_error_handler();
	    } //end testCanReturnInsertIdAfterSqlQueryExecution()


	/**
	 * Test fields()
	 *
	 * @return void
	 */

	public function testCanReturnListOfTableFields()
	    {
		set_error_handler(array($this, "errorHandler"));

		$this->assertEquals(false, $this->object->fields("nonexistenttable"));
		$this->assertEquals(false, $this->object->fields("bad' 'table"));
		$this->assertEquals(array("id", "string", "testblob"), $this->object->fields("PostgreSQLdatabase"));

		restore_error_handler();
	    } //end testCanReturnListOfTableFields()


	/**
	 * Test to use default database
	 *
	 * @return void
	 */

	public function testIfNoPostgresqlCredentialsAreSuppliedGlobalDefaultSettingsAreUsedInstead()
	    {
		$GLOBALS["DEFAULT_DATABASE"]["dbhost"] = $GLOBALS["DB_HOST"];
		$GLOBALS["DEFAULT_DATABASE"]["dbname"] = $GLOBALS["DB_DBNAME"];
		$GLOBALS["DEFAULT_DATABASE"]["dbuser"] = $GLOBALS["DB_USER"];
		$GLOBALS["DEFAULT_DATABASE"]["dbpass"] = $GLOBALS["DB_PASSWD"];
		$this->object = new PostgreSQLdatabase("", "", "", "");
		$this->assertEquals(true, $this->object->isConnected());
	    } //end testIfNoPostgresqlCredentialsAreSuppliedGlobalDefaultSettingsAreUsedInstead()


	/**
	 * Test attempt to open non-existent database
	 *
	 * @return void
	 */

	public function testDoesNotConnectToDatabaseServerIfCredentialsAreIncorrect()
	    {
		set_error_handler(array($this, "errorHandler"));

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], "nonexistentdb", $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->assertEquals(false, $this->object->isConnected());
		$this->assertContains("database \"nonexistentdb\" does not exist", $GLOBALS["errstr"]);

		restore_error_handler();
	    } //end testDoesNotConnectToDatabaseServerIfCredentialsAreIncorrect()


    } //end class

?>
