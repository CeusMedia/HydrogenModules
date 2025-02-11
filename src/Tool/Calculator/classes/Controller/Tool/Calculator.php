<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Tool_Calculator extends Controller
{
	public function index()
	{
		if( !$this->env->getRequest()->isAjax() )
			return;
		$status		= "void";
		try{
			if( getEnv( 'REQUEST_METHOD' ) !== "POST" )
				throw new Exception( 'Only POST requests allowed' );
			if( !isset( $_POST['formula'] ) )
				throw new Exception( 'Formula missing' );
			$math		= new Model_Calculator_Math();
			$status		= "success";
			$formula	= str_replace( ",", ".", $_POST['formula'] );
			$answer		= $math->evaluate( $formula );
		}
		catch( Exception $e ){
			$status	= "error";
			$answer	= $e->getMessage();
		}
		print json_encode( [
			'status'	=> $status,
			'data'		=> $answer,
			'referer'	=> getEnv( 'HTTP_REFERER' ),
		] );
		exit;
	}
}
