<?php namespace Tests\Database\Definition\Table\Columns\Numeric;

use Tests\Database\TestCase;

class NumericDataTypeTest extends TestCase
{
	protected NumericDataTypeMock $column;

	protected function setUp() : void
	{
		$this->column = new NumericDataTypeMock(static::$database);
	}

	public function testAutoIncrement() : void
	{
		$this->assertEquals(
			' mock AUTO_INCREMENT NOT NULL',
			$this->column->autoIncrement()->sql()
		);
	}

	public function testSigned() : void
	{
		$this->assertEquals(
			' mock signed NOT NULL',
			$this->column->signed()->sql()
		);
	}

	public function testUnsigned() : void
	{
		$this->assertEquals(
			' mock unsigned NOT NULL',
			$this->column->unsigned()->sql()
		);
	}

	public function testZerofill() : void
	{
		$this->assertEquals(
			' mock zerofill NOT NULL',
			$this->column->zerofill()->sql()
		);
	}

	public function testFull() : void
	{
		$this->assertEquals(
			' mock unsigned zerofill AUTO_INCREMENT NOT NULL',
			$this->column->unsigned()->zerofill()->autoIncrement()->sql()
		);
	}
}
