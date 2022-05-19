<?php
class Controller_Ajax_Csrf extends CMF_Hydrogen_Controller_Ajax
{
	protected $logic;

	public function getToken()
	{
		$formName	= $this->request->get( 'formName' );
		try{
			if( !$formName )
				throw new InvalidArgumentException( 'Form name is missing' );
			$token	= $this->logic->getToken( $formName );
			$this->respondData( array( 'token' => $token ) );
		}
		catch( Exception $e ){
			$this->respondException( $e );
		}
	}

	protected function __onInit()
	{
		$this->logic	= $this->env->getLogic()->get( 'CSRF' );
	}
}
