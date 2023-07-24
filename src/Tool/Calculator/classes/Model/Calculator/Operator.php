<?php
abstract class Model_Calculator_Operator extends Model_Calculator_TerminalExpression
{
	protected int $precedence	= 0;
	protected bool $leftAssoc	= TRUE;

	public function getPrecedence(): int
	{
		return $this->precedence;
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
