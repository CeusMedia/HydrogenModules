<?php
class Controller_Info_Event extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelEvent	= new Model_Event( $this->env );

		if( !$this->session->get( 'filter_info_event_year' ) )
			$this->session->set( 'filter_info_event_year', date( 'Y' ) );
		if( !$this->session->get( 'filter_info_event_month' ) )
			$this->session->set( 'filter_info_event_month', date( 'm' ) );

		$this->addData( 'from', $this->request->get( 'from' ) );
	}

	public function calendar(){
		$location	= "04109 Leipzig";
		$range		= 10;

		$events		= array();
		$geocoder	= new Logic_Geocoder( $this->env );
		try{
			$parts		= preg_split( "/\s+/", $location );
			$center		= $geocoder->getPointByPostcodeAndCity( $parts[0], $parts[1] );
			$spaceRange	= new SpaceRange( $center->x, $center->y, $center->z, $range );
			$timeRange	= new TimeRange( "2017-05-01", "2017-0601" );
			$events		= $this->modelEvent->getAllWithinTimeAndSpaceRanges( $spaceRange, $timeRange );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
		$this->addData( 'events', $events );
		$this->addData( 'year', $this->session->get( 'filter_info_event_year' ) );
		$this->addData( 'month', $this->session->get( 'filter_info_event_month' ) );
	}

	public function modal(){
//		print_m( $this->request->getAll() );
//		die;
		$eventId	= $this->request->get( 'eventId' );
		switch( $this->request->get( 'do' ) ){
			case 'view':
				$this->restart( 'view/'.$eventId.'?from=info/event/calendar', TRUE );
			default:
				$this->restart( NULL, TRUE );
		}
	}

	public function index(){
		$this->restart( 'calendar', TRUE );
	}

	public function setMonth( $year, $month ){
		$this->session->set( 'filter_info_event_year', $year );
		$this->session->set( 'filter_info_event_month', $month );
		$this->restart( NULL, TRUE );
	}

	public function modalView( $eventId ){
		if( !$this->request->isAjax() ){
			$this->messenger->noteFailure( 'Access denied. Usable in modal view, only.' );
			$this->restart( NULL, TRUE );
		}
		$event	= $this->modelEvent->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID' );
			$this->restart( NULL, TRUE );
		}
		$event->address	= $this->modelAddress->get( $event->addressId );
		$this->addData( 'event', $event );
	}

	public function view( $eventId ){
		$event	= $this->modelEvent->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID' );
			$this->restart( NULL, TRUE );
		}
		$event->address	= $this->modelAddress->get( $event->addressId );
		$this->addData( 'event', $event );
	}
}
?>
