<?php
class View_Work_Newsletter_Template extends View_Work_Newsletter
{
	public function add(): void
	{
		$words				= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->add			= (object) $words->add;
		$this->addData( 'words', $words );
	}

	public function edit(): void
	{
		$words				= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->edit		= (object) $words->edit;
		$words->preview		= (object) $words->preview;
		$words->addStyle	= (object) $words->addStyle;
		$words->styles		= (object) $words->styles;
		$this->addData( 'words', $words );
	}

	public function export(): void
	{
	}

	public function index(): void
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->index	= (object) $words->index;
		$this->addData( 'words', $words );
	}

	public function viewTheme()
	{
	}
}
