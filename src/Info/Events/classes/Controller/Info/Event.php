<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Event extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Model_Event_Address $modelAddress;
	protected Model_Event $modelEvent;

	public function calendar(): void
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

	public function filter( $reset = NULL ): void
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

	public function modal(): void
	{
//		print_m( $this->request->getAll() );
//		die;
		$eventId	= $this->request->get( 'eventId' );
		switch( $this->request->get( 'do' ) ){
			case 'view':
				$this->restart( 'view/'.$eventId.'?from=info/event/calendar', TRUE );
				break;
			default:
				$this->restart( NULL, TRUE );
		}
	}

	public function index(): void
	{
//		$this->restart( 'calendar', TRUE );
		$this->restart( 'map', TRUE );
	}

	public function map(): void
	{
		$location	= "04109 Leipzig";
		$range		= 10;

		$events		= [];
		$center		= NULL;
		if( 0 && $range ){
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

	public function setMonth( $year, $month ): void
	{
		$this->session->set( 'filter_info_event_year', $year );
		$this->session->set( 'filter_info_event_month', $month );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$eventId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $eventId ): void
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID' );
			$this->restart( NULL, TRUE );
		}
		$event->address	= $this->modelAddress->get( $event->addressId );
		$this->addData( 'event', $event );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelAddress	= new Model_Event_Address( $this->env );
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
