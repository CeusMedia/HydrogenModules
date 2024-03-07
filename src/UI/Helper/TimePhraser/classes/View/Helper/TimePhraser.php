<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Timestamp;

class View_Helper_TimePhraser
{
	public const MODE_HINT		= 1;
	public const MODE_BREAK		= 2;

	public const MODES			= [
		self::MODE_HINT,
		self::MODE_BREAK,
	];

	protected Environment $env;
	protected bool $asHtml			= TRUE;
	protected string $template			='%s';
	protected int $mode				= self::MODE_HINT;
	protected $timestamp;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function convert( float|int|string $timestamp, bool $asHtml = FALSE, ?string $prefix = NULL, ?string $suffix = NULL ): string
	{
		$helper	= new Timestamp( (int) $timestamp );

		switch( $this->mode ){
			case self::MODE_BREAK:
				$phrase	= $helper->toPhrase( $this->env, FALSE, 'timephraser', 'phrases-time' );
				if( $this->template )
					$phrase	= sprintf( $this->template, $phrase );
				$phrase	= '<div style="font-size: 0.9em; line-height: 1.1em;">'.$phrase.'</div><div style="font-size: 0.75em; opacity: 0.66; line-height: 1em;">'.date( 'd.m. H:i:s', $timestamp ).'</div>';
				break;
			case self::MODE_HINT:
			default:
				$phrase	= $helper->toPhrase( $this->env, $asHtml, 'timephraser', 'phrases-time' );
				if( 0 !== ( (int) $timestamp ) ){
					if( $this->template )
						$phrase	= sprintf( $this->template, $phrase );
					$phrase	= $prefix ? $prefix.' '.$phrase : $phrase;
					$phrase	= $suffix ? $phrase.' '.$suffix : $phrase;
				}
				break;
		}
		return $phrase;
	}

	public static function convertStatic( Environment $env, float|int|string $timestamp, bool $asHtml = FALSE, ?string $prefix = NULL, ?string $suffix = NULL ): string
	{
		$helper	= new self( $env );
		return $helper->convert( $timestamp, $asHtml, $prefix, $suffix );
	}

	public function render(): string
	{
		if( !$this->timestamp )
			throw new \RuntimeException( 'No timestamp set, yet' );
		return $this->convert( $this->timestamp, $this->asHtml );
	}

	public function setAsHtml( bool $asHtml = TRUE ): self
	{
		$this->asHtml	= $asHtml;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		if( !in_array( $mode, self::MODES ) )
			throw new \RangeException( 'Invalid mode' );
		$this->mode		= $mode;
		return $this;
	}

	public function setTemplate( string $template ): self
	{
		$this->template	= $template;
		return $this;
	}

	public function setTimestamp( string $timestamp ): self
	{
		$this->timestamp	= $timestamp;
		return $this;
	}
}
