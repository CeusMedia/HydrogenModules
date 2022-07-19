<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_AddressView
{
	const OUTPUT_UNKNOWN		= 0;
	const OUTPUT_TEXT			= 1;
	const OUTPUT_HTML			= 2;

	const OUTPUTS				= [
		self::OUTPUT_UNKNOWN,
		self::OUTPUT_TEXT,
		self::OUTPUT_HTML,
	];

	protected $env;
	protected $address;
	protected $output			= self::OUTPUT_HTML;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'address' );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	protected function escape( string $value ): string
	{
		return htmlentities( $value, ENT_QUOTES, 'UTF-8' );
	}

	protected function getCountryLabel( string $countryCode ): string
	{
		if( $countryCode && array_key_exists( $countryCode, $this->words['countries'] ) )
			return $this->words['countries'][$countryCode];
	}

	public function render(): string
	{
		if( !$this->address )
			return '';
		switch( $this->output ){
			case self::OUTPUT_HTML:
				return $this->renderAsHtml();
			case self::OUTPUT_TEXT:
				return $this->renderAsText();
		}
	}

	public function setAddress( $addressOrId ): self
	{
		if( is_object( $addressOrId ) )
			$this->address	= $addressOrId;
		else if( preg_match( '/^[0-9]+$/', $addressOrId ) )
			$this->address	= $this->model->get( $addressOrId );
		if( !$this->address )
			throw new InvalidArgumentException( 'Neither address nor valid address ID given' );
		return $this;
	}

	public function setOutput( int $format ): string
	{
		if( !in_array( (int) $format, array( self::OUTPUT_HTML, self::OUTPUT_TEXT ) ) )
			throw new InvalidArgumentException( 'Invalid output format' );
		$this->output		= (int) $format;
		return $this;
	}

	public function setTextTop( string $text ): self
	{
		$this->textTop		= $text;
		return $this;
	}

	protected function renderAsHtml(): string
	{
		$w		= (object) $this->words['view'];
		$d		= new Dictionary( $this->address );
//		print_m( $d->getAll() );die;
		$list	= [];
		if( trim( $d->get( 'institution' ) ) )
			$list[]	= $this->renderRow( 'institution', $this->escape( $d->get( 'institution' ) ) );
		$list[]	= $this->renderRow( 'name', $this->escape( $d->get( 'firstname' ).' '.$d->get( 'surname' ) ) );
		$list[]	= $this->renderRow( 'address', join( '<br/>', array(
			$this->escape( $d->get( 'street' ) ),
			$this->escape( $d->get( 'postcode' ).' '.$d->get( 'city' ) ),
			$this->getCountryLabel( $d->get( 'country' ) ),
			$this->escape( $d->get( 'region' ) ),
		) ) );
		$list[]	= $this->renderRow( 'email', $this->escape( $d->get( 'email' ) ) );
		if( trim( $d->get( 'phone' ) ) )
			$list[]	= $this->renderRow( 'phone', $this->escape( $d->get( 'phone' ) ) );
		return join( $list );
	}

	protected function renderAsText(): string
	{
	//	$helperText		= new View_Helper_Mail_Text();
		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( $this->words['view'] );

		$d		= new Dictionary( $this->address );
		if( trim( $d->get( 'institution' ) ) )
			$helperFacts->add( 'institution', '', $d->get( 'institution' ) );
		$helperFacts->add( 'name', '', $d->get( 'firstname' ).' '.$d->get( 'surname' ) );
		$helperFacts->add( 'address', '', join( "\n", array(
			$d->get( 'street' ),
			$d->get( 'postcode' ).' '.$d->get( 'city' ),
			$this->getCountryLabel( $d->get( 'country' ) ),
			$d->get( 'region' ),
		) ) );
		$helperFacts->add( 'email', '', $d->get( 'email' ) );
		if( trim( $d->get( 'phone' ) ) )
			$helperFacts->add( 'phone', '', $d->get( 'phone' ) );
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT );
		return $helperFacts->render();
	}

	protected function renderRow( string $labelKey, string $content ): string
	{
		$w		= $this->words['view'];
		$label	= $w['label'.ucfirst( $labelKey )];
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'small', $label, array( 'class' => 'muted' ) )
				) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'big', $content, array( 'class' => NULL ) )
				) ),
			), array( 'class' => 'span12' ) )
		), array( 'class' => 'row-fluid' ) );
	}
}
