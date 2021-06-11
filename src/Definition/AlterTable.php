<?php namespace Framework\Database\Definition;

use Framework\Database\Definition\Table\TableDefinition;
use Framework\Database\Statement;
use InvalidArgumentException;
use LogicException;

/**
 * Class AlterTable.
 *
 * @see https://mariadb.com/kb/en/library/alter-table/
 */
class AlterTable extends Statement
{
	/**
	 * @return $this
	 */
	public function online()
	{
		$this->sql['online'] = true;
		return $this;
	}

	protected function renderOnline() : ?string
	{
		if ( ! isset($this->sql['online'])) {
			return null;
		}
		return ' ONLINE';
	}

	/**
	 * @return $this
	 */
	public function ignore()
	{
		$this->sql['ignore'] = true;
		return $this;
	}

	protected function renderIgnore() : ?string
	{
		if ( ! isset($this->sql['ignore'])) {
			return null;
		}
		return ' IGNORE';
	}

	/**
	 * @param string $tableName
	 *
	 * @return $this
	 */
	public function table(string $tableName)
	{
		$this->sql['table'] = $tableName;
		return $this;
	}

	protected function renderTable() : string
	{
		if (isset($this->sql['table'])) {
			return ' ' . $this->database->protectIdentifier($this->sql['table']);
		}
		throw new LogicException('TABLE name must be set');
	}

	/**
	 * @param int $seconds
	 *
	 * @return $this
	 */
	public function wait(int $seconds)
	{
		$this->sql['wait'] = $seconds;
		return $this;
	}

	protected function renderWait() : ?string
	{
		if ( ! isset($this->sql['wait'])) {
			return null;
		}
		if ($this->sql['wait'] < 0) {
			throw new InvalidArgumentException(
				"Invalid WAIT value: {$this->sql['wait']}"
			);
		}
		return " WAIT {$this->sql['wait']}";
	}

	/**
	 * @param callable $definition
	 *
	 * @return $this
	 */
	public function add(callable $definition)
	{
		$this->sql['add'] = $definition;
		return $this;
	}

	protected function renderAdd() : ?string
	{
		if ( ! isset($this->sql['add'])) {
			return null;
		}
		$definition = new TableDefinition($this->database);
		$this->sql['add']($definition);
		return $definition->sql('ADD');
	}

	/**
	 * @param callable $definition
	 *
	 * @return $this
	 */
	public function change(callable $definition)
	{
		$this->sql['change'] = $definition;
		return $this;
	}

	protected function renderChange() : ?string
	{
		if ( ! isset($this->sql['change'])) {
			return null;
		}
		$definition = new TableDefinition($this->database);
		$this->sql['change']($definition);
		return $definition->sql('CHANGE');
	}

	/**
	 * @param callable $definition
	 *
	 * @return $this
	 */
	public function modify(callable $definition)
	{
		$this->sql['modify'] = $definition;
		return $this;
	}

	protected function renderModify() : ?string
	{
		if ( ! isset($this->sql['modify'])) {
			return null;
		}
		$definition = new TableDefinition($this->database);
		$this->sql['modify']($definition);
		return $definition->sql('MODIFY');
	}

	/**
	 * @param callable $definition
	 *
	 * @return $this
	 */
	public function dropColumns(callable $definition)
	{
		$this->sql['drop_columns'] = $definition;
		return $this;
	}

	/**
	 * @param callable $definition
	 *
	 * @return $this
	 */
	public function drop(callable $definition)
	{
		$this->sql['drop'] = $definition;
		return $this;
	}

	public function sql() : string
	{
		$sql = 'ALTER' . $this->renderOnline() . $this->renderIgnore();
		$sql .= ' TABLE';
		$sql .= $this->renderTable() . \PHP_EOL;
		if ($part = $this->renderWait()) {
			$sql .= $part . \PHP_EOL;
		}
		$sql .= $this->renderAdd();
		$sql .= $this->renderChange();
		$sql .= $this->renderModify();
		return $sql;
	}

	/**
	 * Runs the ALTER TABLE statement.
	 *
	 * @return int The number of affected rows
	 */
	public function run() : int
	{
		return $this->database->exec($this->sql());
	}
}
