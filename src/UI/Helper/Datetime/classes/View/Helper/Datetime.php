<?php
/**
 *	View helper for converting and displaying timestamps.
 */

use CeusMedia\Common\Alg\Time\DurationPhraser as TimeDurationPhraser;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

/**
 *	View helper for converting and displaying timestamps.
 */
class View_Helper_Datetime extends Abstraction
{
	public string $stringEmpty			= '';
	public string $formatDatetime		= 'Y-m-d H:i:s';
	public string $formatDate			= 'Y-m-d';
	public string $formatTime			= 'H:i:s';
	public string $languageFileKey		= 'datetime';
	public string $languageSection		= 'phrases-time';

	/**	@var	TimeDurationPhraser|NULL	$phraser */
	protected ?TimeDurationPhraser $phraser			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env	Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function convertToTimestamp( string $datetime ): int
	{
		return strtotime( $datetime );
	}

	public function convertFromTimestamp( $timestamp, string $format = "r" ): string
	{
		return date( $format, (float)$timestamp );
	}

	public function getDateFromTimestamp( $timestamp, string $format = NULL ): string
	{
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ?: $this->formatDate;
		$date	= date( $format, $timestamp );
		$attr	= ['class' => 'date'];
		return HtmlTag::create( 'span', $date, $attr );
	}

	public function getDatetimeFromTimestamp( $timestamp, string $format = NULL ): string
	{
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ?: $this->formatDatetime;
		$date	= date( $format, $timestamp );
		$attr	= ['class' => 'datetime'];
		return HtmlTag::create( 'span', $date, $attr );
	}

	public function getDurationPhraseFromTimestamp( $timestamp, bool $showDatetime = FALSE ): string
	{
		if( (int)$timestamp < 0 )
			return $this->stringEmpty;
		if( !$this->phraser ){
			$this->setPhraserLanguage( $this->languageFileKey, $this->languageSection );
		}
		$phrase	= $this->phraser->getPhraseFromTimestamp( $timestamp );
		if( $showDatetime ){
			$datetime	= $this->convertFromTimestamp( (int)$timestamp );
			$phrase		= HtmlElements::Acronym( $phrase, $datetime );
		}
		$attributes	= [
			'class'				=> 'phrase ui-datetime-timephrase',
			'data-timestamp'	=> $timestamp,
		];
		return HtmlTag::create( 'span', $phrase, $attributes );
	}

	public function getTimeFromTimestamp( $timestamp, $format = NULL ): string
	{
		if( (int)$timestamp < 1 )
			return $this->stringEmpty;
		$format	= $format ?: $this->formatTime;
		$time	= date( $format, $timestamp );
		$attr	= ['class' => 'time'];
		return HtmlTag::create( 'span', $time, $attr );
	}

	public function setPhraserLanguage( string $fileKey, string $section ): self
	{
		$words		= $this->env->getLanguage()->getWords( $fileKey );
		if( !isset( $words[$section] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$section.'" in file "'.$fileKey.'"' );
		$this->phraser	= new TimeDurationPhraser( $words[$section] );
		return $this;
	}
}
