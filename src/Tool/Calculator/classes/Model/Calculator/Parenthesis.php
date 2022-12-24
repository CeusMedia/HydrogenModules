<?php
class Model_Calculator_Parenthesis extends Model_Calculator_TerminalExpression
{
	protected int $precedence	= 7;

	public function operate( Model_Calculator_Stack $stack )
	{
	}

	public function getPrecedence(): int
	{
		return $this->precedence;
	}

	public function isNoOp(): bool
	{
		return TRUE;
	}

	public function isParenthesis(): bool
	{
		return TRUE;
	}

	public function isOpen(): bool
	{
		return $this->value == '(';
	}
}
