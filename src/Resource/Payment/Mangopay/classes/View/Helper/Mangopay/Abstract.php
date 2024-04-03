<?php
abstract class View_Helper_Mangopay_Abstract
{
	protected \CeusMedia\HydrogenFramework\Environment $env;

	public function __construct( \CeusMedia\HydrogenFramework\Environment $env )
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
