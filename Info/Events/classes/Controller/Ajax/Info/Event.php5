<?php
class Controller_Ajax_Info_Event extends CMF_Hydrogen_Controller
{
	protected function __onInit()
	{
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelEvent	= new Model_Event( $this->env );
	}

	public function typeaheadCities( $startsWith = NULL )
	{
		$list		= array();
		$startsWith	= $startsWith ? $startsWith : $this->request->get( 'query' );
		if( strlen( trim( $startsWith ) ) ){
			$geocoder	= new Logic_Geocoder( $this->env );
			$cities		= $geocoder->getCities( $startsWith );
			foreach( $cities as $city ){
				$list[]	= $city->zip.' '.$city->city;
			}
		}
		$this->respondData( array( 'options' => $list ) );
	}

	public function modalView( $eventId )
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event )
			$this->respondError( 0, 'No event found.' );
		$event->address	= $this->modelAddress->get( $event->addressId );
		$view	= new CMF_Hydrogen_View( $this->env );
		$html	= $view->loadTemplateFile( 'info/event/view.modal.php', array( 'event' => $event ) );
		$this->respondData( $html );
	}
}