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
				HtmlTag::create( 'span', 'HTML-Vorschau' ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', [
						'type'			=> 'button',
						'class'			=> 'btn btn-info btn-mini',
						'data-toggle'	=> 'modal',
						'data-target'	=> '#modal-preview',
						'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
					] )
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
						], ['id' => 'newsletter-preview-iframe-container'] ),
					], ['id' => 'newsletter-preview-container'] ),
				], ['id' => 'newsletter-preview', 'class' => $class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}

	public function renderTextPreviewPanel( object $template, string $class = '' ): string
	{
		$urlPreview	= './work/newsletter/template/preview/text/'.$template->newsletterTemplateId;
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', [
				HtmlTag::create( 'span', 'Text-Vorschau' ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', [
						'type'			=> 'button',
						'class'			=> 'btn btn-info btn-mini',
						'data-toggle'	=> 'modal',
						'data-target'	=> '#modal-preview',
						'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
					] )
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
						], ['id' => 'newsletter-preview-iframe-container'] ),
					], ['id' => 'newsletter-preview-container'] ),
				], ['id' => 'newsletter-preview', 'class' => $class] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}
}
