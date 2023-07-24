<?php
class Model_Calculator_Addition extends Model_Calculator_Operator
{
	protected int $precedence = 4;

	public function operate( Model_Calculator_Stack $stack )
	{
		$left	= $stack->pop();
		if( !$left )
			throw new Exception( 'Missing first summand' );
		$left	= $left->operate( $stack );

		$right	= $stack->pop();
		if( !$right )
			throw new Exception( 'Missing second summand' );
		$right	= $right->operate( $stack );

		return $left + $right;
	}
}
