<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Note extends View
{
	public function add(): void
	{
	}

	public function index(): void
	{
		$query	= $this->env->getSession()->get( 'filter_notes_term' );
		$this->addData( 'query', $query );
	}

	public function view(): void
	{
	}

	public function edit(): void
	{
	}
}
