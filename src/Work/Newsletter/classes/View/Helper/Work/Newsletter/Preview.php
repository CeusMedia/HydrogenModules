<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Newsletter_Preview
{
	public const MODE_NEWSLETTER	= 1;
	public const MODE_TEMPLATE		= 2;

	public const FORMAT_HTML		= 1;
	public const FORMAT_PLAIN		= 2;

	protected Environment $env;
	protected ?object $resource			= NULL;
	protected int $format				= self::FORMAT_HTML;
	protected int $mode					= self::MODE_NEWSLETTER;
	protected string $class				= '';

	public function __construct( Environment $env )
	{
		$this->env = $env;
	}

	public function render(): string
	{
		if( NULL === $this->resource )
			throw new RuntimeException( 'No resource set' );
		if( self::FORMAT_HTML === $this->format )
			return $this->renderHtmlPreviewPanel();
		return $this->renderTextPreviewPanel();
	}

	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	public function setFormat( int $format ): self
	{
		$this->format	= $format;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		$this->mode		= $mode;
		return $this;
	}

	public function setResource( object $newsletterOrTemplate ): self
	{
		$this->resource	= $newsletterOrTemplate;
		return $this;
	}


	//  --  PROTECTED  --  //


	/**
	 *	@return		string
	 */
	protected function renderHtmlPreviewPanel(): string
	{
		$urlPreview	= './work/newsletter/template/preview/html/'.$this->resource->newsletterTemplateId;
		if( self::MODE_NEWSLETTER === $this->mode )
			$urlPreview	= './work/newsletter/preview/html/'.$this->resource->newsletterId;

		$buttonScale	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-mobile fa-mobile-alt"></i>&nbsp;<i class="fa fa-fw fa-tablet"></i>&nbsp;<i class="fa fa-fw fa-desktop"></i>', [
			'type'			=> 'button',
			'class'			=> 'btn btn-mini',
			'onclick'		=> 'ModuleWorkNewsletter.toggleScaled(this);',
		] );
		$buttonModal	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-window-maximize"></i><!--&nbsp;Ansicht-->', [
			'type'			=> 'button',
			'class'			=> 'btn not-btn-info btn-mini',
			'data-toggle'	=> 'modal',
			'data-target'	=> '#modal-preview',
			'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
		] );
		$buttonOpen		= HtmlTag::create( 'a', '<i class="fa fa-fw fa-external-link"></i><!--&nbsp;Tab-->', [
			'href'			=> $urlPreview,
			'class'			=> 'btn btn-mini',
			'target'		=> '_modal-preview_',
		] );
		$buttons	= HtmlTag::create( 'div', [
			$buttonScale,
			$buttonModal,
			$buttonOpen
		], ['class' => 'btn-group'] );

		$iframe	= HtmlTag::create('iframe', '', [
			'src'			=> $urlPreview,
			'frameborder'	=> '0',
		] );

		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', [
				HtmlTag::create( 'span', 'HTML-Vorschau' ),
				HtmlTag::create( 'div', $buttons, ['style' => 'float: right'] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', $iframe, ['class' => 'newsletter-preview-iframe-container'] ),
					], ['class' => 'newsletter-preview-container'] ),
				], ['class' => 'newsletter-preview '.$this->class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}

	protected function renderTextPreviewPanel(): string
	{
		$urlPreview	= './work/newsletter/template/preview/text/'.$this->resource->newsletterTemplateId;
		if( self::MODE_NEWSLETTER === $this->mode )
			$urlPreview	= './work/newsletter/preview/text/'.$this->resource->newsletterId;

		$buttonScale	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-mobile fa-mobile-alt"></i>&nbsp;<i class="fa fa-fw fa-tablet"></i>&nbsp;<i class="fa fa-fw fa-desktop"></i>', [
			'type'			=> 'button',
			'class'			=> 'btn btn-mini',
			'onclick'		=> 'ModuleWorkNewsletter.toggleScaled(this);',
		] );
		$buttonModal	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-window-maximize"></i><!--&nbsp;Ansicht-->', [
			'type'			=> 'button',
			'class'			=> 'btn not-btn-info btn-mini',
			'data-toggle'	=> 'modal',
			'data-target'	=> '#modal-preview',
			'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
		] );
		$buttons	= HtmlTag::create( 'div', [
			$buttonScale,
			$buttonModal,
		], ['class' => 'btn-group'] );

		$iframe		= HtmlTag::create('iframe', '', [
			'src'			=> $urlPreview,
			'frameborder'	=> '0',
		] );

		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', [
				HtmlTag::create( 'span', 'Text-Vorschau' ),
				HtmlTag::create( 'div', $buttons, ['style' => 'float: right'] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', $iframe, ['class' => 'newsletter-preview-iframe-container'] ),
					], ['class' => 'newsletter-preview-container'] ),
				], ['class' => 'newsletter-preview '.$this->class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}
}