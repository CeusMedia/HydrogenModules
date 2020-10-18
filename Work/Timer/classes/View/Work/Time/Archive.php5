<?php
class View_Work_Time_Archive extends CMF_Hydrogen_View
{
	protected $modelTimer;
	protected $modelProject;

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function __onInit()
	{
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
	}
}
