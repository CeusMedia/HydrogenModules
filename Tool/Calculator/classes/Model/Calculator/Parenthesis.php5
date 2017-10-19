<?php
class Model_Calculator_Parenthesis extends Model_Calculator_TerminalExpression {

    protected $precidence = 7;

    public function operate(Model_Calculator_Stack $stack) {
    }

    public function getPrecidence() {
        return $this->precidence;
    }

    public function isNoOp() {
        return true;
    }

    public function isParenthesis() {
        return true;
    }

    public function isOpen() {
        return $this->value == '(';
    }

}
?>
