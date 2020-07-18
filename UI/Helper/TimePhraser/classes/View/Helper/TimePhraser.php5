<?php
class View_Helper_TimePhraser
{

	const MODE_HINT		= 1;
	const MODE_BREAK	= 2;

	const MODES			= array(
		self::MODE_HINT,
		self::MODE_BREAK,
	);

	protected $env;
	protected $asHtml			= TRUE;
	protected $template			='%s';
	protected $mode				= self::MODE_HINT;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function convert( $timestamp, bool $asHtml = FALSE, ?string $prefix = NULL, ?string $suffix = NULL ): string
	{
		$helper	= new CMF_Hydrogen_View_Helper_Timestamp( $timestamp );

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
				if( (int) $timestamp ){
					if( $this->template )
						$phrase	= sprintf( $this->template, $phrase );
					$phrase	= $prefix ? $prefix.' '.$phrase : $phrase;
					$phrase	= $suffix ? $phrase.' '.$suffix : $phrase;
				}
				break;
		}
		return $phrase;
	}

	static public function convertStatic( CMF_Hydrogen_Environment $env, $timestamp, bool $asHtml = FALSE, ?string $prefix = NULL, ?string $suffix = NULL )
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
