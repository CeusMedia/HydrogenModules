<?php

use CeusMedia\Common\FS\File\CSS\Compressor as CssFileCompressor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\API\Premailer as Premailer;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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

	protected function extendMailPageByTemplateStyles( Entity_Newsletter_Template $template ): void
	{
		if( is_string( $template->styles ) )
			$template->styles	= explode( '|', trim( $template->styles ) );

		$styles		= "";
		foreach( $template->styles as $url ){
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
		$data['tracking']	= '';
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

		$this->mail->addHeaderPair( 'X-Auto-Response-Suppress', 'All' );

		if( $hasReaderLetterId )
			return $this->generateByReaderLetter( $this->data['readerLetterId'] );

		if( $hasNewsletterId ){
			if( $hasReaderId )
				return $this->generateByNewsletterAndReader( $this->data['newsletterId'], $this->data['readerId'] );
			return $this->generateByNewsletter( $this->data['newsletterId'] );
		}

		if( $hasTemplateId )
			return $this->generateByTemplate( $this->data['templateId'] );
		return $this;
	}

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByReaderLetter( int|string $readerLetterId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		$logic->checkReaderLetterId( $readerLetterId );
		
		$readerLetter	= $logic->getReaderLetter( $readerLetterId );
		$reader			= $logic->getReader( $readerLetter->newsletterReaderId );
		$newsletter		= $logic->getNewsletter( $readerLetter->newsletterId );
		$template		= $logic->getTemplate( $newsletter->newsletterTemplateId );
		$this->templateId	= $template->mailTemplateId;

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setData( $this->data );
		$helper->setReaderLetter( $readerLetter );
		$helper->setReader( $reader );
		$helper->setNewsletter( $newsletter );
		$helper->setTemplate( $template );

		$this->extendMailPageByTemplateStyles( $template );

		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		int|string		$newsletterId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByNewsletter( int|string $newsletterId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		$logic->checkNewsletterId( $newsletterId );

		$newsletter	= $logic->getNewsletter( $newsletterId );
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
	 *	@param		int|string		$newsletterId
	 *	@param		int|string		$readerId
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function generateByNewsletterAndReader( int|string $newsletterId, int|string $readerId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		$logic->checkNewsletterId( $newsletterId );
		$logic->checkReaderId( $readerId );

		$newsletter	= $logic->getNewsletter( $newsletterId );
		$template	= $logic->getTemplate( $newsletter->newsletterTemplateId );
		$this->templateId	= $template->mailTemplateId;

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setData( $this->data );
		$helper->setNewsletter( $newsletter );
		$helper->setTemplate( $template );
		$helper->setReader( $logic->getReader( $readerId ) );

		$this->extendMailPageByTemplateStyles( $template );

		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		return $this->generateFromPreparedHelper( $helper );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		static
	 */
	protected function generateByTemplate( int|string $templateId ): static
	{
		$logic	= new Logic_Newsletter( $this->env );
		$logic->checkTemplateId( $templateId );

		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		$helper->setTemplateId( $templateId );

		$template	= $logic->getTemplate( $templateId );
		$this->templateId	= $template->mailTemplateId;

		$this->extendMailPageByTemplateStyles( $template );

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
		$replacements	= array_intersect_key( $helper->getData(), array_flip( $this->additionalPlaceholderKeys ) );
		$this->setHtml( $html, $this->templateId, $replacements );

		$plainText		= $helper->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN )->render();
		$replacements	= array_intersect_key( $helper->getData(), array_flip( $this->additionalPlaceholderKeys ) );
		$this->setText( $plainText, $this->templateId, $replacements );

		return $this;

	}

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate_old(): static
	{
		$logic	= new Logic_Newsletter( $this->env );
//		$this->data['mailTemplateId']	= 0;
//		$logic->checkTemplateId( $data['templateId'], TRUE );

//		$words		= (object) $this->getWords( 'work/newsletter' );
		$helper		= new View_Helper_Newsletter_Mail( $this->env );
		if( isset( $data['readerLetterId'] ) ){
			$helper->setReaderLetterId( $data['readerLetterId'] );
			$letter	= $logic->getReaderLetter( $data['readerLetterId'] );
			$data['newsletterId']	= $letter->newsletterId;
			$helper->setReaderId( $letter->newsletterReaderId );
		}
		else{
			if( !isset( $data['newsletterId'] ) )
				throw new RuntimeException( 'No newsletter ID set' );
			if( !isset( $data['readerId'] ) )
				throw new RuntimeException( 'No reader ID set' );
			$helper->setNewsletterId( $data['newsletterId'] );
			$helper->setReaderId( $data['readerId'] );
		}

		$newsletter	= $logic->getNewsletter( $data['newsletterId'] );
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		$this->mail->addHeaderPair( 'X-Auto-Response-Suppress', 'All' );
		$this->mail->setSender( $newsletter->senderAddress, $newsletter->senderName );

		$helper->setData( $data );
		$helper->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN );
		$plain	= $helper->render();
		$this->setText( $plain );

		$helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML_TRACKING );
		$html	= $helper->render();

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

		$this->setHtml( $html );
		return $this;
	}
}
