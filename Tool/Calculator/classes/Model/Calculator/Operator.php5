<?php
abstract class Model_Calculator_Operator extends Model_Calculator_TerminalExpression
{
	protected $precidence	= 0;
	protected $leftAssoc	= TRUE;

	public function getPrecidence(): int
	{
		return $this->precidence;
	}

	public function isLeftAssoc(): bool
	{
		return $this->leftAssoc;
	}

	public function isOperator(): bool
	{
		return TRUE;
	}
}
