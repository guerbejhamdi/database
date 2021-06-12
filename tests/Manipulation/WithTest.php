<?php namespace Tests\Database\Manipulation;

use Framework\Database\Manipulation\Select;
use Framework\Database\Manipulation\With;
use Framework\Database\Result;
use Tests\Database\TestCase;

class WithTest extends TestCase
{
	protected With $with;

	public function setup() : void
	{
		$this->with = new With(static::$database);
	}

	protected function prepareWith() : void
	{
		$this->with->reference('t1', static function (Select $select) {
			return $select->columns('*')->from('folks')->sql();
		})->select(static function (Select $select) {
			return $select->columns('*')->from('ancestors')->sql();
		});
	}

	public function testWith() : void
	{
		$this->prepareWith();
		$this->assertEquals(
			"WITH\n`t1` AS (SELECT\n *\n FROM `folks`\n)\nSELECT\n *\n FROM `ancestors`\n",
			$this->with->sql()
		);
	}

	public function testOptions() : void
	{
		$this->prepareWith();
		$this->with->options($this->with::OPT_RECURSIVE);
		$this->assertEquals(
			"WITH\nRECURSIVE\n`t1` AS (SELECT\n *\n FROM `folks`\n)\nSELECT\n *\n FROM `ancestors`\n",
			$this->with->sql()
		);
	}

	public function testManyReferences() : void
	{
		$this->prepareWith();
		$this->with->reference('t2', static function () {
			return 'select * from foo';
		});
		$this->assertEquals(
			"WITH\n`t1` AS (SELECT\n *\n FROM `folks`\n), `t2` AS (select * from foo)\nSELECT\n *\n FROM `ancestors`\n",
			$this->with->sql()
		);
	}

	public function testInvalidOption() : void
	{
		$this->with->options('foo');
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Invalid option: foo');
		$this->with->sql();
	}

	public function testWithoutReference() : void
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('References must be set');
		$this->with->sql();
	}

	public function testWithoutSelect() : void
	{
		$this->with->reference('t1', static function (Select $select) {
			return $select->columns('*')->from('folks')->sql();
		});
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('SELECT must be set');
		$this->with->sql();
	}

	public function testRun() : void
	{
		$this->createDummyData();
		$this->with->reference('t1', static function (Select $select) {
			return $select->columns('*')->from('t1')->sql();
		})->select(static function (Select $select) {
			return $select->columns('*')->from('t2')->sql();
		});
		$this->assertInstanceOf(
			Result::class,
			$this->with->run()
		);
	}
}
