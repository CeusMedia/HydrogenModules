<?php
abstract class Model_Calculator_TerminalExpression
{
	protected $value = '';

	public function __construct( $value )
	{
		$this->value = $value;
	}

	public static function factory( $value )
	{
		if( is_object( $value ) && $value instanceof Model_Calculator_TerminalExpression )
			return $value;
		else if( is_numeric( $value ) )
			return new Model_Calculator_Number( $value );
		else if( $value === '+' )
			return new Model_Calculator_Addition( $value );
		else if( $value === '-' )
			return new Model_Calculator_Subtraction( $value );
		elseif ( $value === '*' )
			return new Model_Calculator_Multiplication( $value );
		elseif ( $value === '/' )
			return new Model_Calculator_Division( $value );
		elseif ( $value === '^' )
			return new Model_Calculator_Power( $value );
		else if( in_array( $value, ['(', ')'] ) )
			return new Model_Calculator_Parenthesis( $value );
		throw new Exception( 'Undefined Value ' . $value );
	}

	abstract public function operate( Model_Calculator_Stack $stack );

	public function isOperator(): bool
	{
		return FALSE;
	}

	public function isParenthesis(): bool
	{
		return FALSE;
	}

	public function isNoOp(): bool
	{
		return FALSE;
	}

	public function render()
	{
		return $this->value;
	}
}
