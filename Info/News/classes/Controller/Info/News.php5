<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_News extends Controller
{
	public function calendar()
	{
		$list	= $this->getVisibleNews();

		$root		= new XML_DOM_Node( 'event');
		$calendar	= new XML_DOM_Node( 'VCALENDAR' );
		$calendar->addChild( new XML_DOM_Node( 'VERSION', '2.0' ) );

		foreach( $list as $news ){
			$node	= new XML_DOM_Node( 'VEVENT' );
			if( $news->startsAt )
				$node->addChild( new XML_DOM_Node( 'DTSTART', date( "Ymd\THis", $news->startsAt ) ) );
			if( !$news->endsAt && $news->startsAt )
				$news->endsAt	= $news->startsAt;
			if( $news->endsAt )
				$node->addChild( new XML_DOM_Node( 'DTEND', date( "Ymd\THis", $news->endsAt ) ) );
			$node->addChild( new XML_DOM_Node( 'SUMMARY', utf8_decode( $news->title ) ) );
			$node->addChild( new XML_DOM_Node( 'CREATED', date( "Ymd\THis", $news->createdAt ) ) );
#			if( $mission->modifiedAt )
#				$node->addChild( new XML_DOM_Node( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
#			if( $mission->location )
#				$node->addChild( new XML_DOM_Node( 'LOCATION', $mission->location ) );
#			if( $mission->priority )
#				$node->addChild( new XML_DOM_Node( 'PRIORITY', ( ceil( $mission->priority - 7 ) / -2 ) ) );
			$calendar->addChild( $node );
		}
		$root->addChild( $calendar );
		$ical	= new File_ICal_Builder();
		$ical	= trim( $ical->build( $root ) );
		print( $ical );
		exit;
	}

	public function index()
	{
		$config	= $this->env->getConfig()->getAll( 'module.info_news.', TRUE );

		//   @todo extract this data collection as hook to module Catalog
		if( $this->env->getModules()->has( 'Catalog' ) ){
			$logic		= new Logic_Catalog( $this->env );
			$articles   = $logic->getArticles( array( 'new' => 1 ), array( 'createdAt' => 'DESC' ) );
			$this->addData( 'article', $articles[array_rand( $articles, 1 )] );
		}
		$this->addData( 'news', $this->getVisibleNews( $config->get( 'show.max' ) ) );
		$this->addData( 'showOnEmpty', $config->get( 'show.empty' ) );
	}

	//  --  PROTECTED  --  //

	protected function getVisibleNews( $limit = 10 )
	{
		$model		= new Model_News( $this->env );
		$news		= $model->getAllByIndices(
			array( 'status' => 1 ),
			array( 'newsId' => 'DESC' )
		);
		$list	= [];
		foreach( $news as $entry ){
			if( $entry->startsAt && (int)time() < (int)$entry->startsAt )
				continue;
			if( $entry->endsAt && (int)time() > (int)$entry->endsAt )
				continue;
			switch( $entry->type ){
				case 1:
					break;
				case 2:
					break;
				case 3:
					break;
			}
			$list[]	= $entry;
		}
		return array_slice( $list, 0, $limit );
	}
}
