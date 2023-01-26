<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\View;

class Controller_Ajax_Info_Event extends AjaxController
{
	protected Model_Address $modelAddress;
	protected Model_Event $modelEvent;

	protected function __onInit(): void
	{
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelEvent	= new Model_Event( $this->env );
	}

	public function typeaheadCities( $startsWith = NULL )
	{
		$list		= [];
		$startsWith	= $startsWith ? $startsWith : $this->request->get( 'query' );
		if( strlen( trim( $startsWith ) ) ){
			$geocoder	= new Logic_Geocoder( $this->env );
			$cities		= $geocoder->getCities( $startsWith );
			foreach( $cities as $city ){
				$list[]	= $city->zip.' '.$city->city;
			}
		}
		$this->respondData( ['options' => $list] );
	}

	public function modalView( $eventId )
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event )
			$this->respondError( 0, 'No event found.' );
		$event->address	= $this->modelAddress->get( $event->addressId );
		$view	= new View( $this->env );
		$html	= $view->loadTemplateFile( 'info/event/view.modal.php', ['event' => $event] );
		$this->respondData( $html );
	}
}
