<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\SQL
 */

namespace Logics\Tests\Foundation\SQL;

use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Test for MySQLresult class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:12:20 +0000 (Wed, 17 Aug 2016) $ $Revision: 45 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/SQL/tags/0.1/tests/MySQLresultTest.php $
 *
 * @donottranslate
 */

class MySQLresultTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use GetConnectionMySQL;

	/**
	 * Testing object
	 *
	 * @var MySQLdatabase
	 */
	protected $object;

	/**
	 * Get test data set
	 *
	 * @return \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */

	public function getDataSet()
	    {
		return $this->createFlatXmlDataSet(__DIR__ . "/MySQLresultFixture.xml");
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
		$db   = $conn->getConnection();
		$db->exec(
		    "CREATE TABLE IF NOT EXISTS `MySQLresult` (" .
		    "id int NOT NULL AUTO_INCREMENT, " .
		    "string text NOT NULL, " .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);

		parent::setUp();

		$this->object = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
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
		$db->exec("DROP TABLE IF EXISTS `MySQLresult`;");
	    } //end tearDown()


	/**
	 * Test testGetNumRows()
	 *
	 * @return void
	 */

	public function testCanTellHowManyRowsResultContains()
	    {
		$result = $this->object->exec("SELECT * FROM MySQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(2, $result->getNumRows());

		$result = $this->object->exec("TRUNCATE TABLE MySQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(false, $result->getNumRows());
	    } //end testCanTellHowManyRowsResultContains()


	/**
	 * Test testGetRow()
	 *
	 * @return void
	 */

	public function testReturnsQueryResultRowByRowUntilAllRowsAreReturned()
	    {
		$result = $this->object->exec("SELECT * FROM MySQLresult WHERE id = 1");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(array("id" => "1", "string" => "Hello buddy!"), $result->getRow());

		$result = $this->object->exec("TRUNCATE TABLE MySQLresult");
		$this->assertInstanceOf("\Logics\Foundation\SQL\MySQLresult", $result);
		$this->assertEquals(false, $result->getRow());
	    } //end testReturnsQueryResultRowByRowUntilAllRowsAreReturned()


    } //end class

?>
