<?php
class Model_Calculator_Stack
{
	protected array $data	= [];

	public function push( $element )
	{
		$this->data[] = $element;
	}

	public function poke()
	{
		return end( $this->data );
	}

	public function pop()
	{
		return array_pop( $this->data );
	}
}
