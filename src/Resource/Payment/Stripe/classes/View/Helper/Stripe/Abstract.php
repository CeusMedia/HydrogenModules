<?php

use CeusMedia\Common\Renderable;
use CeusMedia\HydrogenFramework\Environment;

abstract class View_Helper_Stripe_Abstract implements Stringable, Renderable
{
	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->__onInit();
	}

	public function __toString()
	{
		return $this->render();
	}

	abstract public function render(): string;

	protected function __onInit(): void
	{
	}
}
