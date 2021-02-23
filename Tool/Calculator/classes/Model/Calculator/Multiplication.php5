<?php
class Model_Calculator_Multiplication extends Model_Calculator_Operator
{

	protected $precidence = 5;

	public function operate( Model_Calculator_Stack $stack )
	{
		$left	= $stack->pop();
		if( !$left )
			throw new Exception( 'Missing multiplicand' );
		$left	= $left->operate( $stack );

		$right	= $stack->pop();
		if( !$right )
			throw new Exception( 'Missing multiplier' );
		$right	= $right->operate( $stack );

		return $left * $right;
	}
}
