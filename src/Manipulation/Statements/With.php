<?php namespace Framework\Database\Manipulation\Statements;

/**
 * Class With.
 *
 * @see https://mariadb.com/kb/en/library/with/
 */
class With extends Statement
{
	/**
	 * @see https://mariadb.com/kb/en/library/recursive-common-table-expressions-overview/
	 */
	public const OPT_RECURSIVE = 'RECURSIVE';

	/**
	 * Set the statement options.
	 *
	 * @param mixed $options Each option value must be one of the OPT_* constants
	 *
	 * @return $this
	 */
	public function options(...$options)
	{
		foreach ($options as $option) {
			$this->sql['options'][] = $option;
		}
		return $this;
	}

	protected function renderOptions() : ?string
	{
		if ( ! isset($this->sql['options'])) {
			return null;
		}
		$options = $this->sql['options'];
		foreach ($options as &$option) {
			$input = $option;
			$option = \strtoupper($option);
			if ($option !== static::OPT_RECURSIVE) {
				throw new \InvalidArgumentException("Invalid option: {$input}");
			}
		}
		return \implode(' ', $options);
	}

	/**
	 * @param \Closure|string $table
	 * @param \Closure        $alias
	 *
	 * @see https://mariadb.com/kb/en/library/non-recursive-common-table-expressions-overview/
	 * @see https://mariadb.com/kb/en/library/recursive-common-table-expressions-overview/
	 *
	 * @return $this
	 */
	public function reference($table, \Closure $alias)
	{
		$this->sql['reference'] = [
			'table' => $table,
			'alias' => $alias,
		];
		return $this;
	}

	protected function renderReference() : string
	{
		if ( ! isset($this->sql['reference'])) {
			throw new \LogicException('Reference must be set');
		}
		$reference = $this->renderColumn($this->sql['reference']['table']);
		$alias = $this->renderAsSelect($this->sql['reference']['alias']);
		return " {$reference} AS {$alias}";
	}

	private function renderAsSelect(\Closure $subquery) : string
	{
		return '(' . $subquery(new Select($this->manipulation)) . ')';
	}

	public function select(\Closure $select)
	{
		$this->sql['select'] = $select(new Select($this->manipulation));
		return $this;
	}

	protected function renderSelect() : string
	{
		if ( ! isset($this->sql['select'])) {
			throw new \LogicException('SELECT must be set');
		}
		return $this->sql['select'];
	}

	public function sql() : string
	{
		$sql = 'WITH' . \PHP_EOL;
		if ($part = $this->renderOptions()) {
			$sql .= '-- options ' . \PHP_EOL;
			$sql .= $part . \PHP_EOL;
		}
		$sql .= '-- reference ' . \PHP_EOL;
		$sql .= $this->renderReference() . \PHP_EOL;
		$sql .= '-- select ' . \PHP_EOL;
		$sql .= $this->renderSelect();
		return $sql;
	}
}
