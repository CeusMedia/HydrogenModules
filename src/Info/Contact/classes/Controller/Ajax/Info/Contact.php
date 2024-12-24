<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Contact extends AjaxController
{
	protected Dictionary $moduleConfig;

	/**
	 *	AJAX action to receive form input data (from modal).
	 *	Validates against all active rules.
	 *
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function form(): int
	{
		if( !$this->request->getMethod()->isPost() )
			return $this->respondError( 0, 'Access granted for POST requests, only.' );

		try{
			/** @var Dictionary $inputData */
			$inputData		= $this->request->getAll( '', TRUE );
			$logic			= new Logic_Info_Contact( $this->env );
			$errorsOrTrue	= $logic->validateInput( $inputData );
			if( TRUE !== $errorsOrTrue )
				return $this->respondError( 0, 'Invalid input: '.join( ' ', $errorsOrTrue ) );
			$logic->sendFormMail( $inputData );
			return $this->respondData( TRUE );
		}
		catch( Exception $e ){
			$this->env->getLog()->logException( $e );
			return $this->respondError( 0, 'Exception: '.$e->getMessage() );
		}
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->moduleConfig		= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );
	}
}
