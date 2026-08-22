<?php

use CeusMedia\HydrogenFramework\Controller\Api as ApiController;

class Controller_Api_Altcha extends ApiController
{
	protected Logic_Altcha $logic;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function challenge(): void
	{
		$this->respond( json_encode( $this->logic->challenge()->toArray() ) );
	}

	/**
	 *	@return        void
	 */
	public function verify(): void
	{
		$altchaField	= $this->request->get( 'altcha', '' ) ?? '';
		if( '' === $altchaField )
			$this->respondError( 'Missing "altcha" form field', 400 );

		try{
			$result		= $this->logic->verify( $altchaField );
			if( $result->verified )
				$this->respondData( ['verified' => $result] );
			$this->respondError( 'Verification failed', 0, 403 );
		}
		catch( Throwable $e ){
			$this->respondError( 'Exception: '.$e->getMessage(), $e->getCode(), 400 );
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic	= Logic_Altcha::getInstance( $this->env );
	}
}
