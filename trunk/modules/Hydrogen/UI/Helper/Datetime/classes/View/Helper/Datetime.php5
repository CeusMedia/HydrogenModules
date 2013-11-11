<?php
/**
 *	View helper for converting and displaying timestamps.
 *
 *	Copyright (c) 2010-2013 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@version		$Id: Timestamp.php5 433 2012-05-23 15:49:34Z christian.wuerker $
 */
/**
 *	View helper for converting and displaying timestamps.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@uses			UI_HTML_Tag
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@version		$Id: Timestamp.php5 433 2012-05-23 15:49:34Z christian.wuerker $
 */
class View_Helper_Datetime extends CMF_Hydrogen_View_Helper_Abstract{

	public $stringEmpty			= "";
	public $formatDatetime		= 'Y-m-d H:i:s';
	public $formatDate			= 'Y-m-d';
	public $formatTime			= 'H:i:s';
	public $languageFileKey		= 'datetime';
	public $languageSection		= 'phrases-time';
	
	/**	@var	Alg_Time_DurationPhraser	$phraser */
	protected $phraser			= NULL;
	

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->setEnv( $env );
	}

	public function convertToTimestamp( $datetime ){
		return strtotime( $datetime );
	}

	public function convertFromTimestamp( $timestamp, $format = "r" ){
		return date( $format, (float)$timestamp );
	}

	public function getDateFromTimestamp( $timestamp, $format = NULL ){
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ? $format : $this->formatDate;
		$date	= date( $format, $timestamp );
		$attr	= array( 'class' => 'date' );
		$date	= UI_HTML_Tag::create( 'span', $date, $attr );
		return $date;
	}

	public function getDatetimeFromTimestamp( $timestamp, $format = NULL ){
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ? $format : $this->formatDatetime;
		$date	= date( $format, $timestamp );
		$attr	= array( 'class' => 'datetime' );
		$date	= UI_HTML_Tag::create( 'span', $date, $attr );
		return $date;
	}	

	public function getDurationPhraseFromTimestamp( $timestamp, $showDatetime = FALSE ){
		if( (int)$timestamp < 0 )
			return $this->stringEmpty;
		if( !$this->phraser ){
			$this->setPhraserLanguage( $this->languageFileKey, $this->languageSection );
			$phrase	= $this->phraser->getPhraseFromTimestamp( $timestamp );
		}
		if( $showDatetime ){
			$datetime	= $this->convertFromTimestamp( (int)$timestamp );
			$phrase		= UI_HTML_Elements::Acronym( $phrase, $datetime );
		}
		$attributes	= array(
			'class'				=> 'phrase ui-datetime-timephrase',
			'data-timestamp'	=> $timestamp,
		);
		return UI_HTML_Tag::create( 'span', $phrase, $attributes );
	}

	public function getTimeFromTimestamp( $timestamp, $format = NULL ){
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ? $format : $this->formatTime;
		$time	= date( $format, $timestamp );
		$attr	= array( 'class' => 'time' );
		$time	= UI_HTML_Tag::create( 'span', $time, $attr );
		return $time;
	}

	public function setPhraserLanguage( $fileKey, $section ){
		$words		= $this->env->language->getWords( $fileKey );
		if( !isset( $words[$section] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$section.'" in topic "'.$topic.'"' );
		$this->phraser	= new Alg_Time_DurationPhraser( $words[$section] );
	}
}
?>
