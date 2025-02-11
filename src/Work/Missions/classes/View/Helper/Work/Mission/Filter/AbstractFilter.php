<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

abstract class View_Helper_Work_Mission_Filter_AbstractFilter
{
	protected WebEnvironment $env;
	protected array $words;
	protected ?View_Helper_ModalRegistry $modalRegistry		= NULL;
	protected array $values									= [];
	protected array $selected								= [];

	/**
	 *	@param		WebEnvironment		$env
	 */
	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
		$this->__onInit();
	}

	/**
	 *	@return		string
	 */
	abstract public function render(): string;

	/**
	 *	@param		View_Helper_ModalRegistry		$modalRegistry
	 *	@return		self
	 */
	public function setModalRegistry( View_Helper_ModalRegistry $modalRegistry ): self
	{
		$this->modalRegistry	= $modalRegistry;
		return $this;
	}

	/**
	 *	@param		array		$all
	 *	@param		array		$selected
	 *	@return		self
	 */
	public function setValues( array $all, array $selected ): self
	{
		$this->values	= $all;
		$this->selected	= $selected;
		return $this;
	}

	protected function __onInit(): void
	{
	}
}