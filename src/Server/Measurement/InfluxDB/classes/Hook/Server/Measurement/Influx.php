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
}