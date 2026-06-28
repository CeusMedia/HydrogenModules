<?php

use CeusMedia\Common\FS\File\CSS\Compressor as CssFileCompressor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\API\Premailer as Premailer;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use Entity_Newsletter as NewsletterEntity;
use Entity_Newsletter_Reader as ReaderEntity;
use Entity_Newsletter_Reader_Letter as ReaderLetterEntity;
use Entity_Newsletter_Template as TemplateEntity;

class Mail_Newsletter extends Mail_Abstract
{
	protected string $cachePath			= 'contents/cache/';
	protected array $additionalPlaceholderKeys	= [
		'linkView',
		'linkUnregister',
		'linkTracking',
		'tracking',
		'imprint',
	];

	protected function applyPremailer( string $html ): string
	{
		if( $this->env->getConfig()->get( 'module.resource_newsletter.premailer.html' ) ){
			$premailer	= new Premailer();
			try{
				$response	= $premailer->convertFromHtml( $html, [
					'preserve_styles'	=> FALSE,
					'remove_ids'		=> TRUE,
					'remove_classes'	=> TRUE,
					'remove_comments'	=> TRUE,
				] );
				$converted	= $premailer->getHtml();
				$html		= $converted;
			}
			catch( Exception $e ){
				$this->env->getLog()->logException( $e );
			}
		}
		return $html;
	}

	protected function extendMailPageByBrowserSupport(): void
	{
		if( (bool) ( $this->data['preview'] ?? FALSE ) )
			return;
		$script	= 'document.getElementById("browser-link").remove();';							//  script to remove browser link in browser view
		$this->page->addScript( 'window.addEventListener("load", function(){'.$script.'});' );	//  add script to HTML page
	}

	protected function extendMailPageByHeads(): void
	{
		$this->page->addHead( HtmlTag::create( 'meta', NULL, ['charset' => 'utf-8'] ) );
		$this->page->addHead( HtmlTag::create( 'meta', NULL, ['name' => 'x-apple-disable-message-reformatting'] ) );
		$this->page->addHead( '<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->' );
		$this->page->addMetaTag( "name", "viewport", "width=device-width" );
		$this->page->addMetaTag( "http-equiv", "X-UA-Compatible", "IE=edge" );
	}

	protected function extendMailPageByTemplateStyles( TemplateEntity $template ): void
	{
		if( is_string( $template->styles ?? '' ) )
			$template->styles	= explode( '|', trim( $template->styles ?? '' ) );

		$styles		= "";
		foreach( $template->styles ?? [] as $url ){
			if( '' === trim( $url ) )
				continue;
			if( file_exists( $this->cachePath.md5( $url ) ) )
				$styles		.= FileReader::load( $this->cachePath.md5( $url ) );
			else{
				$content	= NetReader::readUrl( $url );
				FileWriter::save( $this->cachePath.md5( $url ), $content );
				$styles		.= $content;
			}
		}
		$styles		.= trim( $template->style );
		if( ( $styles = trim( CssFileCompressor::compressString( $styles ) ) ) )
			$this->page->addHead( HtmlTag::create( 'style', $styles ) );

		$this->page->addHead( "<!--[if mso]><style>* {font-family: sans-serif !important;}</style><![endif]-->" );
	}

	protected function extendMailPageByTracking(): void
	{
		$this->data['tracking']	= '';
		if( (bool) ( $this->data['preview'] ?? FALSE ) )
			return;
		if( '' === trim( $this->data['linkTracking'] ?? '' ) )							//  tracking link is not defined
			return;
		$this->data['tracking']	= HtmlTag::create( 'img', NULL, [					//  create tracking pixel image
			'src' => $this->data['linkTracking']												//  ... pointing to tracking URL
		] );
	}

	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generate(): static
	{
		$hasNewsletterId	= 0 !== (int) ( $this->data['newsletterId'] ?? 0 );
		$hasTemplateId		= 0 !== (int) ( $this->data['templateId'] ?? 0 );
		$hasReaderId		= 0 !== (int) ( $this->data['readerId'] ?? 0 );
		$hasReaderLetterId	= 0 !== (int) ( $this->data['readerLetterId'] ?? 0 );

		$hasTemplate		= is_object( $this->data['template'] ?? NULL );
		$hasNewsletter		= is_object( $this->data['newsletter'] ?? NULL );
		$hasReaderLetter	= is_object( $this->data['readerLetter'] ?? NULL );

		$this->mail->addHeaderPair( 'X-Auto-Response-Suppress', 'All' );

		if( $hasReaderLetter )
			return $this->generateByReaderLetter( $this->data['readerLetter'] );
		else if( $hasReaderLetterId )
			return $this->generateByReaderLetter( $this->data['readerLetterId'] );

		if( $hasNewsletter ){
			if( $hasReaderId )
				return $this->generateByNewsletterAndReader( $this->data['newsletter'], $this->data['readerId'] );
			return $this->generateByNewsletter( $this->data['newsletter'] );
		}
		if( $hasNewsletterId ){
			if( $hasReaderId )
				return $this->generateByNewsletterAndReader( $this->data['newsletterId'], $this->data['readerId'] );
			return $this->generateByNewsletter( $this->data['newsletterId'] );
		}

		if( $hasTemplate )
			return $this->generateByTemplate( $this->data['template'] );
		if( $hasTemplateId )
			return $this->generateByTemplate( $this->data['templateId'] );
		return $this;
	}

	/**
	 *	@param		ReaderLetterEntity|int|string		$readerLetterObjectOrId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByReaderLetter( ReaderLetterEntity|int|string $readerLetterObjectOrId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );

		if( is_object( $readerLetterObjectOrId ) )
			$readerLetter	= $readerLetterObjectOrId;
		else{
			$logic->checkReaderLetterId( $readerLetterObjectOrId );
			$readerLetter	= $logic->getReaderLetter( $readerLetterObjectOrId );
		}

		$reader			= $logic->getReader( $readerLetter->newsletterReaderId );
		$newsletter		= $logic->getNewsletter( $readerLetter->newsletterId );

		/** @var TemplateEntity $template */
		$template		= $logic->getTemplate( $newsletter->newsletterTemplateId );
		$this->templateId	= $template->mailTemplateId;

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setData( $this->data );
		$helper->setReaderLetter( $readerLetter );
		$helper->setReader( $reader );
		$helper->setNewsletter( $newsletter );
		$helper->setTemplate( $template );
		$helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML_TRACKING );

		$this->extendMailPageByTemplateStyles( $template );

		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		NewsletterEntity|int|string		$newsletterObjectOrId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByNewsletter( NewsletterEntity|int|string $newsletterObjectOrId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		if( is_object( $newsletterObjectOrId ) )
			$newsletter		= $newsletterObjectOrId;
		else{
			$logic->checkNewsletterId( $newsletterObjectOrId );
			$newsletter	= $logic->getNewsletter( $newsletterObjectOrId );
		}

		/** @var TemplateEntity $template */
		$template	= $logic->getTemplate( $newsletter->newsletterTemplateId );
		$this->templateId	= $template->mailTemplateId;

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setData( $this->data );
		$helper->setNewsletter( $newsletter );
		$helper->setTemplate( $template );

		$this->extendMailPageByTemplateStyles( $template );

		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		NewsletterEntity|int|string		$newsletterObjectOrId
	 *	@param		ReaderEntity|int|string			$readerObjectOrId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByNewsletterAndReader( NewsletterEntity|int|string $newsletterObjectOrId, ReaderEntity|int|string $readerObjectOrId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		if( is_object( $newsletterObjectOrId ) )
			$newsletter	= $newsletterObjectOrId;
		else{
			$logic->checkNewsletterId( $newsletterObjectOrId );
			$newsletter	= $logic->getNewsletter( $newsletterObjectOrId );
		}

		if( is_object( $readerObjectOrId ) )
			$reader	= $readerObjectOrId;
		else{
			$logic->checkReaderId( $readerObjectOrId );
			$reader	= $logic->getReader( $readerObjectOrId );
		}

		/** @var TemplateEntity $template */
		$template	= $logic->getTemplate( $newsletter->newsletterTemplateId );
		$this->templateId	= $template->mailTemplateId;

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setData( $this->data );
		$helper->setNewsletter( $newsletter );
		$helper->setTemplate( $template );
		$helper->setReader( $reader );

		$this->extendMailPageByTemplateStyles( $template );

		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		TemplateEntity|int|string		$templateObjectOrId
	 *	@return		static
	 */
	protected function generateByTemplate( TemplateEntity|int|string $templateObjectOrId ): static
	{
		if( is_object( $templateObjectOrId ) )
			$template	= $templateObjectOrId;
		else{
			$logic		= new Logic_Newsletter( $this->env );
			$logic->checkTemplateId( $templateObjectOrId );
			/** @var TemplateEntity $template */
			$template	= $logic->getTemplate( $templateObjectOrId );
		}

		$this->templateId	= $template->mailTemplateId;
		$this->extendMailPageByTemplateStyles( $template );

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setTemplate( $template );
		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		View_Helper_Newsletter_Mail		$helper
	 *	@return		static
	 */
	protected function generateFromPreparedHelper( View_Helper_Newsletter_Mail $helper ): static
	{
		$helper->setData( $this->data );

		$this->extendMailPageByHeads();
		$this->extendMailPageByBrowserSupport();
		$this->extendMailPageByTracking();

		$html	= $helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML )->render();
		$html	= $this->applyPremailer( $html );

		$replacements	= array_intersect_key( $helper->getData(), array_flip( $this->additionalPlaceholderKeys ) );
		$this->setHtml( $html, $this->templateId, $replacements );

		$plainText		= $helper->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN )->render();
		$replacements	= array_intersect_key( $helper->getData(), array_flip( $this->additionalPlaceholderKeys ) );
		$this->setText( $plainText, $this->templateId, $replacements );

		return $this;
	}
}
