<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Event extends Controller
{
	public function calendar()
	{
		$location	= "04109 Leipzig";
		$range		= 10;

		$events		= [];
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
	}

	public function filter( $reset = NULL )
	{
		$filters	= ['query', 'location', 'range'];
		if( $reset ){
			foreach( $filters as $filter ){
				$this->session->remove( 'filter_info_event_'.$filter );
			}
		}
		foreach( $filters as $filter ){
			if( $this->request->has( $filter ) ){
				$this->session->set( 'filter_info_event_'.$filter, $this->request->get( $filter ) );
			}
		}
		$range		= abs( $this->session->get( 'filter_info_event_range' ) );
		$location	= $this->session->get( 'filter_info_event_location' );
		if( $location && !$range )
			$this->session->set( 'filter_info_event_range', 10 );
//		if( $range < 1 )
//			$this->session->set( 'filter_info_event_range', 10 );

		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	public function modal()
	{
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

	public function index()
	{
//		$this->restart( 'calendar', TRUE );
		$this->restart( 'map', TRUE );
	}

	public function map()
	{
		$location	= "04109 Leipzig";
		$range		= 10;

		$events		= [];
		$center		= NULL;
		if( $location && $range ){
			$geocoder	= new Logic_Geocoder( $this->env );
			try{
				$parts		= preg_split( "/\s+/", $location );
				$center		= $geocoder->getPointByPostcodeAndCity( $parts[0], $parts[1] );
//				$this->messenger->noteNotice( "Map Center: ".$center->lat.", ".$center->lon );
				$spaceRange	= new SpaceRange( $center->x, $center->y, $center->z, $range );
				$timeRange	= new TimeRange( "2017-05-01", "2017-0601" );
				$events		= $this->modelEvent->getAllWithinTimeAndSpaceRanges( $spaceRange, $timeRange );
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteFailure( $e->getMessage() );
			}
#			catch( Exception $e ){
#				$this->messenger->noteError( /*$msg->errorLocationInvalid, $location*/'Ungültige Ortseingabe ('.$e->getMessage().').' );
#				$this->session->remove( 'filter_index_location' );
#				$this->restart();
#			}
		}
		$this->addData( 'events', $events );
		$this->addData( 'center', $center );
	}

	public function setMonth( $year, $month )
	{
		$this->session->set( 'filter_info_event_year', $year );
		$this->session->set( 'filter_info_event_month', $month );
		$this->restart( NULL, TRUE );
	}

	public function view( $eventId )
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID' );
			$this->restart( NULL, TRUE );
		}
		$event->address	= $this->modelAddress->get( $event->addressId );
		$this->addData( 'event', $event );
	}

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelEvent	= new Model_Event( $this->env );

		if( !$this->session->get( 'filter_info_event_year' ) )
			$this->session->set( 'filter_info_event_year', date( 'Y' ) );
		if( !$this->session->get( 'filter_info_event_month' ) )
			$this->session->set( 'filter_info_event_month', date( 'm' ) );

		$this->addData( 'query', $this->session->get( 'filter_info_event_query' ) );
		$this->addData( 'location', $this->session->get( 'filter_info_event_location' ) );
		$this->addData( 'range', $this->session->get( 'filter_info_event_range' ) );
		$this->addData( 'year', $this->session->get( 'filter_info_event_year' ) );
		$this->addData( 'month', $this->session->get( 'filter_info_event_month' ) );
		$this->addData( 'from', $this->request->get( 'from' ) );
	}
}
