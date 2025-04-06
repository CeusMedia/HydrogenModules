<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Measurement_Influx extends Hook
{
	/**
	 *	@return		void
	 */
	public function onAppMeasure(): void
	{
		$payload	= new Dictionary( $this->getPayload() );
		$logic		= new Logic_Measurement_Influx( $this->env );
		$logic->write(
			$payload->get( 'measurement' ),
			$payload->get( 'tags' ),
			$payload->get( 'fields' )
		);
	}

	/**
	 *	Sends basic exception data to InfluxDB.
	 *	@return void
	 */
	public function onEnvLogException(): void
	{
		$exception	= $this->getPayload();
		if( !is_object( $exception ) || !$exception instanceof Throwable )
			return;

		try{
			$logic		= new Logic_Measurement_Influx( $this->env );
			$logic->write( 'exception', [
					'class'	=> get_class( $exception ),
			], [
					'message'	=> $exception->getMessage(),
					'code'		=> $exception->getCode(),
					'file'		=> $exception->getFile(),
					'line'		=> $exception->getLine(),
			] );
		}
		catch( Throwable ){}
	}
}