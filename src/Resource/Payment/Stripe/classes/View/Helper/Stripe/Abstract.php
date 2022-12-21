<?php
abstract class View_Helper_Stripe_Abstract
{
	protected $env;

	public function __construct( $env )
	{
		$this->env		= $env;
		$this->__onInit();
	}

	public function __toString()
	{
		return $this->render();
	}

	abstract public function render();

	protected function __onInit(): void
	{
	}
}
