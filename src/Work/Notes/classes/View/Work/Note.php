<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Note extends View
{
	public function add()
	{
	}

	public function index()
	{
		$query	= $this->env->getSession()->get( 'filter_notes_term' );
		$this->addData( 'query', $query );
	}

	public function view()
	{
	}

	public function edit()
	{
	}
}
