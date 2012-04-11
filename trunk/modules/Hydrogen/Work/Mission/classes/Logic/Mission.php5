<?php
class Logic_Mission{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		$this->model	= new Model_Mission( $env );
	}

	public function moveDate(){
		
	}
	
	public function getDate( $string ){
		$day	= 24 * 60 * 60;
		$now	= time();
		$string	= strtolower( trim( $string ) );
		
		if( preg_match( "/^[+-][0-9]+$/", $string ) ){
			$sign	= substr( $string, 0, 1 );
			$number	= substr( $string, 1 );
			$time	= $sign == '+' ? $now + $number * $day : $now - $number * $day;
		}
		else{
			switch( $string ){
				case '':
				case 'heute':
					$time	= $now;
					break;
				case '+1':
				case 'morgen':
					$time	= $now + 1 * $day;
					break;
				case '+2':
				case 'übermorgen':
					$time	= $now + 1 * $day;
					break;
				default:
					$time	= strtotime( $string );
					break;
			}
		}
		return date( "Y-m-d", $time );
	}
	
}
?>