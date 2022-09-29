<?php

use CeusMedia\Common\FS\File\ICal\Builder as IcalFileBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_News extends Controller
{
	public function calendar()
	{
		$list	= $this->getVisibleNews();

		$root		= new XmlNode( 'event');
		$calendar	= new XmlNode( 'VCALENDAR' );
		$calendar->addChild( new XmlNode( 'VERSION', '2.0' ) );

		foreach( $list as $news ){
			$node	= new XmlNode( 'VEVENT' );
			if( $news->startsAt )
				$node->addChild( new XmlNode( 'DTSTART', date( "Ymd\THis", $news->startsAt ) ) );
			if( !$news->endsAt && $news->startsAt )
				$news->endsAt	= $news->startsAt;
			if( $news->endsAt )
				$node->addChild( new XmlNode( 'DTEND', date( "Ymd\THis", $news->endsAt ) ) );
			$node->addChild( new XmlNode( 'SUMMARY', utf8_decode( $news->title ) ) );
			$node->addChild( new XmlNode( 'CREATED', date( "Ymd\THis", $news->createdAt ) ) );
#			if( $mission->modifiedAt )
#				$node->addChild( new XmlNode( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
#			if( $mission->location )
#				$node->addChild( new XmlNode( 'LOCATION', $mission->location ) );
#			if( $mission->priority )
#				$node->addChild( new XmlNode( 'PRIORITY', ( ceil( $mission->priority - 7 ) / -2 ) ) );
			$calendar->addChild( $node );
		}
		$root->addChild( $calendar );
		$ical	= new IcalFileBuilder();
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
			$articles   = $logic->getArticles( ['new' => 1], ['createdAt' => 'DESC'] );
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
