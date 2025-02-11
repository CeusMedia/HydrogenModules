<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Time_Analysis extends View
{
	protected Model_Work_Timer $modelTimer;
	protected Model_Project $modelProject;

	/**
	 *	@return void
	 */
	public function add(): void
	{
	}

	/**
	 *	@return void
	 */
	public function edit(): void
	{
	}

	/**
	 *	@return void
	 */
	public function index(): void
	{
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );

		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );

		$page		= $this->env->getPage();
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}
}
