<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Time_Analysis extends View
{
	protected $modelTimer;
	protected $modelProject;

	public function add(){}

	public function edit(){}

	public function index(){}

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
