<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Work_Newsletter_Template extends View_Work_Newsletter
{
	public function add(): void
	{
		$words				= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->add			= (object) $words->add;
		$this->addData( 'words', $words );
	}

	public function edit(): void
	{
		$words				= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->edit		= (object) $words->edit;
		$words->preview		= (object) $words->preview;
		$words->addStyle	= (object) $words->addStyle;
		$words->styles		= (object) $words->styles;
		$this->addData( 'words', $words );
	}

	public function export(): void
	{
	}

	public function index(): void
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter/template' );
		$words->index	= (object) $words->index;
		$this->addData( 'words', $words );
	}

	public function viewTheme()
	{
	}

	/**
	 *	@param		object		$template
	 *	@param		string		$class			CSS class to apply to newsletter preview div
	 *	@return		string
	 */
	public function renderHtmlPreviewPanel( object $template, string $class = '' ): string
	{
		$urlPreview	= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', [
				HtmlTag::create( 'span', ( (object) $this->getWords( 'preview', 'work/newsletter/template' ) )->headingHtml ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'button', '<i class="fa fa-fw fa-mobile fa-mobile-alt"></i>&nbsp;<i class="fa fa-fw fa-tablet"></i>&nbsp;<i class="fa fa-fw fa-desktop"></i>', [
							'type'			=> 'button',
							'class'			=> 'btn btn-mini',
							'onclick'		=> 'ModuleWorkNewsletter.toggleScaled(this);',
						] ),
						HtmlTag::create( 'button', '<i class="fa fa-fw fa-window-maximize"></i><!--&nbsp;Ansicht-->', [
							'type'			=> 'button',
							'class'			=> 'btn not-btn-info btn-mini',
							'data-toggle'	=> 'modal',
							'data-target'	=> '#modal-preview',
							'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
						] ),
						HtmlTag::create( 'a', '<i class="fa fa-fw fa-external-link"></i><!--&nbsp;Tab-->', [
							'href'			=> $urlPreview,
							'class'			=> 'btn btn-mini',
							'target'		=> '_modal-preview_',
						] )
					], ['class' => 'btn-group'] )
				], ['style' => 'float: right'] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create('iframe', '', [
								'src'			=> $urlPreview,
								'frameborder'	=> '0',
							] )
						], ['class' => 'newsletter-preview-iframe-container'] ),
					], ['class' => 'newsletter-preview-container'] ),
				], ['class' => 'newsletter-preview '.$class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}

	public function renderTextPreviewPanel( object $template, string $class = '' ): string
	{
		$urlPreview	= './work/newsletter/template/preview/text/'.$template->newsletterTemplateId;
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', [
				HtmlTag::create( 'span', ( (object) $this->getWords( 'preview', 'work/newsletter/template' ) )->headingText ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'button', '<i class="fa fa-fw fa-mobile fa-mobile-alt"></i>&nbsp;<i class="fa fa-fw fa-tablet"></i>&nbsp;<i class="fa fa-fw fa-desktop"></i>', [
							'type'			=> 'button',
							'class'			=> 'btn btn-mini',
							'onclick'		=> 'ModuleWorkNewsletter.toggleScaled(this);',
						] ),
						HtmlTag::create( 'button', '<i class="fa fa-fw fa-window-maximize"></i><!--&nbsp;Ansicht-->', [
							'type'			=> 'button',
							'class'			=> 'btn not-btn-info btn-mini',
							'data-toggle'	=> 'modal',
							'data-target'	=> '#modal-preview',
							'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
						] )
					], ['class' => 'btn-group'] )
				], ['style' => 'float: right'] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create('iframe', '', [
								'src'			=> $urlPreview,
								'frameborder'	=> '0',
							] )
						], ['class' => 'newsletter-preview-iframe-container'] ),
					], ['class' => 'newsletter-preview-container'] ),
				], ['class' => 'newsletter-preview '.$class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addModuleFile( 'module.work.newsletter.js' );
		$this->env->getPage()->css->theme->addUrl( 'module.work.newsletter.css' );
	}
}
