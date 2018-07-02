<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Logics\Foundation\SQL\PostgreSQLdatabase;
use \Logics\Tests\GetConnectionPostgreSQL;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Test for PostgreSQLresult class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/PostgreSQLresultTest.php $
 *
 * @donottranslate
 */

class PostgreSQLresultTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use GetConnectionPostgreSQL;

	/**
	 * Testing object
	 *
	 * @var PostgreSQLdatabase
	 */
	protected $object;

	/**
	 * Get test data set
	 *
	 * @return \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */

	public function getDataSet()
	    {
		return $this->createFlatXmlDataSet(__DIR__ . "/PostgreSQLresultFixture.xml");
	    } //end getDataSet()


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
		$sql->exec("CREATE SEQUENCE id_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1");
		$sql->exec(
		    "CREATE TABLE IF NOT EXISTS PostgreSQLresult (" .
		    "id int PRIMARY KEY DEFAULT nextval('id_seq'), " .
		    "string text, " .
		    "testblob bytea" .
		    ")"
		);

		parent::setUp();

		$this->object = new PostgreSQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
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
		$sql->exec("DROP TABLE IF EXISTS PostgreSQLresult");
		$sql->exec("DROP SEQUENCE id_seq");
	    } //end tearDown()


	/**
	 * Test testGetNumRows()
	 *
	 * @return void
	 */

	public function testCanTellHowManyRowsResultContains()
	    {
		$result = $this->object->exec("SELECT * FROM PostgreSQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $result);
		$this->assertEquals(2, $result->getNumRows());

		$result = $this->object->exec("TRUNCATE TABLE PostgreSQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $result);
		$this->assertEquals(false, $result->getNumRows());
	    } //end testCanTellHowManyRowsResultContains()


	/**
	 * Test testGetRow()
	 *
	 * @return void
	 */

	public function testReturnsQueryResultRowByRowUntilAllRowsAreReturned()
	    {
		$result = $this->object->exec("SELECT id, string FROM PostgreSQLresult WHERE id = 1");
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $result);
		$this->assertEquals(array("id" => "1", "string" => "Hello buddy!"), $result->getRow());
		$this->assertEquals(2, $this->getConnection()->getRowCount("postgresqlresult"));

		$result = $this->object->exec("TRUNCATE TABLE PostgreSQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\PostgreSQLresult", $result);
		$this->assertEquals(false, $result->getRow());
	    } //end testReturnsQueryResultRowByRowUntilAllRowsAreReturned()


    } //end class

?>
