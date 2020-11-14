<?php
class View_Work_Newsletter_Reader extends View_Work_Newsletter{

	public function add(){
		$words			= (object) $this->getWords( NULL, 'work/newsletter/reader' );
		$words->add		= (object) $words->add;
		$this->addData( 'words', $words );
	}

	public function edit(){
		$words			= (object) $this->getWords( NULL, 'work/newsletter/reader' );
		$words->edit	= (object) $words->edit;
		$words->states	= (object) $words->states;
		$words->gender	= (object) $words->gender;
		$this->addData( 'words', $words );
	}

	public function index(){
		$words			= (object) $this->getWords( NULL, 'work/newsletter/reader' );
		$words->index	= (object) $words->index;
		$this->addData( 'words', $words );
	}
}
?>
