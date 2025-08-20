<?php
class View_Work_Newsletter_Dashboard extends View_Work_Newsletter
{
	public function index(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addModuleFile( 'module.work.newsletter.js' );
		$this->env->getPage()->css->theme->addUrl( 'module.work.newsletter.css' );
	}
}
