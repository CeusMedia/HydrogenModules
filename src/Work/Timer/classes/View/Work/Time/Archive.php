<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Time_Archive extends View
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
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
	}
}
