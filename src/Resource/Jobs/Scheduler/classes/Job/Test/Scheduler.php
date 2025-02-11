<?php
class Job_Test_Scheduler extends Job_Abstract{

	public function greetMinute(): void
	{
		$this->out( 'New minute: '.date( 'H:i:s' ) );
	}

	public function greetHour(): void
	{
		$this->out( 'New hour: '.date( 'H:i:s' ) );
	}

	public function greetDay(): void
	{
		$this->out( 'New day: '.date( 'd.m.Y' ) );
	}
}
