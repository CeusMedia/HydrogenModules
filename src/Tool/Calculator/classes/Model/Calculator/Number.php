<?php
class Model_Calculator_Number extends Model_Calculator_TerminalExpression
{
	public function operate( Model_Calculator_Stack $stack )
	{
		return $this->value;
	}
}
