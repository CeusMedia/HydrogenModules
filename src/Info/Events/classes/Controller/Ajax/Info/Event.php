<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\View;

class Controller_Ajax_Info_Event extends AjaxController
{
	protected Model_Event_Address $modelAddress;
	protected Model_Event $modelEvent;

	/**
	 *	@param		?string		$startsWith
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function typeaheadCities( ?string $startsWith = NULL ): void
	{
		$list		= [];
		$startsWith	= $startsWith ?: $this->request->get( 'query' );
		if( strlen( trim( $startsWith ) ) ){
			$geocoder	= new Logic_Geocoder( $this->env );
			$cities		= $geocoder->getCities( $startsWith );
			foreach( $cities as $city ){
				$list[]	= $city->zip.' '.$city->city;
			}
		}
		$this->respondData( ['options' => $list] );
	}

	/**
	 *	@param		int|string		$eventId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function modalView( int|string $eventId ): void
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event )
			$this->respondError( 0, 'No event found.' );
		$event->address	= $this->modelAddress->get( $event->addressId );
		$view	= new View( $this->env );
		$html	= $view->loadTemplateFile( 'info/event/view.modal.php', ['event' => $event] );
		$this->respondData( $html );
	}

	protected function __onInit(): void
	{
		$this->modelAddress	= new Model_Event_Address( $this->env );
		$this->modelEvent	= new Model_Event( $this->env );
	}
}
