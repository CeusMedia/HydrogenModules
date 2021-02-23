<?php
class Model_Calculator_Parenthesis extends Model_Calculator_TerminalExpression
{
	protected $precidence	= 7;

	public function operate( Model_Calculator_Stack $stack )
	{
	}

	public function getPrecidence(): int
	{
		return $this->precidence;
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
