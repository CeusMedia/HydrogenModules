<?php
abstract class Model_Calculator_TerminalExpression {

    protected $value = '';

    public function __construct($value) {
        $this->value = $value;
    }

    public static function factory($value) {
        if (is_object($value) && $value instanceof Model_Calculator_TerminalExpression) {
            return $value;
        } elseif (is_numeric($value)) {
            return new Model_Calculator_Number($value);
        } elseif ($value == '+') {
            return new Model_Calculator_Addition($value);
        } elseif ($value == '-') {
            return new Model_Calculator_Subtraction($value);
        } elseif ($value == '*') {
            return new Model_Calculator_Multiplication($value);
        } elseif ($value == '/') {
            return new Model_Calculator_Division($value);
        } elseif ($value == '^') {
            return new Model_Calculator_Power($value);
        } elseif (in_array($value, array('(', ')'))) {
            return new Model_Calculator_Parenthesis($value);
        }
        throw new Exception('Undefined Value ' . $value);
    }

    abstract public function operate(Model_Calculator_Stack $stack);

    public function isOperator() {
        return false;
    }

    public function isParenthesis() {
        return false;
    }

    public function isNoOp() {
        return false;
    }

    public function render() {
        return $this->value;
    }
}
?>
