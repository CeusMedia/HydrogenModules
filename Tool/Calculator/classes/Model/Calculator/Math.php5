<?php
class Model_Calculator_Math {

    protected $variables = array();

    public function evaluate($string) {
        $stack = $this->parse($string);
        return $this->run($stack);
    }

    public function parse($string) {
        $tokens = $this->tokenize($string);
        $output = new Model_Calculator_Stack();
        $operators = new Model_Calculator_Stack();
        foreach ($tokens as $token) {
            $token = $this->extractVariables($token);
            $expression = Model_Calculator_TerminalExpression::factory($token);
            if ($expression->isOperator()) {
                $this->parseOperator($expression, $output, $operators);
            } elseif ($expression->isParenthesis()) {
                $this->parseParenthesis($expression, $output, $operators);
            } else {
                $output->push($expression);
            }
        }
        while (($op = $operators->pop())) {
            if ($op->isParenthesis()) {
                throw new RuntimeException('Mismatched Parenthesis');
            }
            $output->push($op);
        }
        return $output;
    }

    public function registerVariable($name, $value) {
        $this->variables[$name] = $value;
    }

    public function run(Model_Calculator_Stack $stack) {
        while (($operator = $stack->pop()) && $operator->isOperator()) {
            $value = $operator->operate($stack);
            if (!is_null($value)) {
                $stack->push( Model_Calculator_TerminalExpression::factory($value) );
            }
        }
        return $operator ? $operator->render() : $this->render($stack);
    }

    protected function extractVariables($token) {
        if ($token[0] == '$') {
            $key = substr($token, 1);
            return isset($this->variables[$key]) ? $this->variables[$key] : 0;
        }
        return $token;
    }

    protected function render( Model_Calculator_Stack $stack ) {
        $output = '';
        while (($el = $stack->pop())) {
            $output .= $el->render();
        }
        if ($output) {
            return $output;
        }
        throw new RuntimeException('Could not render output');
    }

    protected function parseParenthesis( Model_Calculator_TerminalExpression $expression, Stack $output, Stack $operators ){
        if ($expression->isOpen()) {
            $operators->push($expression);
        } else {
            $clean = false;
            while (($end = $operators->pop())) {
                if ($end->isParenthesis()) {
                    $clean = true;
                    break;
                } else {
                    $output->push($end);
                }
            }
            if (!$clean) {
                throw new RuntimeException('Mismatched Parenthesis');
            }
        }
    }

    protected function parseOperator( Model_Calculator_TerminalExpression $expression, Model_Calculator_Stack $output, Model_Calculator_Stack $operators ){
        $end = $operators->poke();
        if (!$end) {
            $operators->push($expression);
        } elseif ($end->isOperator()) {
            do {
                if ($expression->isLeftAssoc() && $expression->getPrecidence() <= $end->getPrecidence()) {
                    $output->push($operators->pop());
                } elseif (!$expression->isLeftAssoc() && $expression->getPrecidence() < $end->getPrecidence()) {
                    $output->push($operators->pop());
                } else {
                    break;
                }
            } while (($end = $operators->poke()) && $end->isOperator());
            $operators->push($expression);
        } else {
            $operators->push($expression);
        }
    }

    protected function tokenize( $string ){
		$string	= preg_replace( "/^-([0-9.]+)/", "(0-\\1)", $string );
		$string	= preg_replace( "/(\+|-|\*|\/)-(\d+)/", "\\1(0-\\2)", $string );
        $parts = preg_split('(([0-9.]+|\+|-|\(|\)|\*|/)|\s+)', $string, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
//print_r( $parts );die;
        $parts = array_map('trim', $parts);
        return $parts;
    }
}
?>
