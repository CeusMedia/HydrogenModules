<?php
class Model_Calculator_Power extends Model_Calculator_Operator {

    protected $precidence = 6;

    public function operate(Model_Calculator_Stack $stack) {
        $left	= $stack->pop();
		if(!$left)
			throw new Exception( 'Missing exponent' );
		$left	= $left->operate($stack);

        $right	= $stack->pop();
		if(!$right)
			throw new Exception( 'Missing base' );
		$right	= $right->operate($stack);

        return pow($right, $left);
    }
}
?>
