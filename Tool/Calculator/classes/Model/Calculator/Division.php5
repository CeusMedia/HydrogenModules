<?php
class Model_Calculator_Division extends Model_Calculator_Operator {

    protected $precidence = 5;

    public function operate(Model_Calculator_Stack $stack) {
        $left	= $stack->pop();
		if(!$left)
			throw new Exception( 'Missing divisor' );
		$left	= $left->operate($stack);
		if($left == 0)
			throw new Exception( 'Division by zero' );

        $right	= $stack->pop();
		if(!$right)
			throw new Exception( 'Missing dividend' );
		$right	= $right->operate($stack);

        return $right / $left;
    }

}
?>
