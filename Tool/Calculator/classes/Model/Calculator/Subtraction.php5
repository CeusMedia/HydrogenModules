<?php
class Model_Calculator_Subtraction extends Model_Calculator_Operator {

    protected $precidence = 4;

    public function operate(Model_Calculator_Stack $stack) {
        $left	= $stack->pop();
		if(!$left)
			throw new Exception( 'Missing minuend' );
		$left	= $left->operate($stack);

        $right	= $stack->pop();
		if(!$right)
			throw new Exception( 'Missing subtrahend' );
		$right	= $right->operate($stack);

        return $right - $left;
    }
}
?>
