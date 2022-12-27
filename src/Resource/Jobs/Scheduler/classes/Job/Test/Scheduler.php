<?php
class Job_Test_Scheduler extends Job_Abstract{

	public function greetMinute(){
		$this->out( 'New minute: '.date( 'H:i:s' ) );
	}

	public function greetHour(){
		$this->out( 'New hour: '.date( 'H:i:s' ) );
	}

	public function greetDay(){
		$this->out( 'New day: '.date( 'd.m.Y' ) );
	}
}
