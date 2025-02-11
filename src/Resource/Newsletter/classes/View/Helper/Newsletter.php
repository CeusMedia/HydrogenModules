<?php

use CeusMedia\Common\FS\File\CSS\Compressor as CssCompressor;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo	transform to real helper with render and setters
 */
class View_Helper_Newsletter
{
	protected Environment $env;
	protected Logic_Newsletter $logic;
	protected string $cachePath	= 'cache/';
	protected bool $preview		= FALSE;
	protected object $template;

	/**
	 *	@param		Environment		$env
	 *	@param		int|string		$templateId
	 *	@param		bool			$preview
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function __construct( Environment $env, int|string $templateId, bool $preview = FALSE )
	{
		$this->env		= $env;
		$this->preview	= $preview;
		$this->logic	= new Logic_Newsletter( $env );
		$this->logic->checkTemplateId( $templateId, TRUE );
		$this->template	= $this->logic->getTemplate( $templateId );
		$this->template->styles		= $this->logic->getTemplateAttributeList( $templateId, 'styles' );
	}

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		Mail_Newsletter
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generateMail( int|string $readerLetterId ): Mail_Newsletter
	{
		$this->logic->checkReaderLetterId( $readerLetterId );
		$readerLetter	= $this->logic->getReaderLetter( $readerLetterId );
		$newsletter		= $this->logic->getNewsletter( $readerLetter->newsletterId );
		$helper			= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId, $this->preview );
		$data			= $helper->prepareReaderDataForLetter( $readerLetterId );
//print_m( $data );die;
		return new Mail_Newsletter( $this->env, $data );
	}

	/**
	 *	@deprecated use View_Helper_Newsletter_Mail::prepareData instead
	 *	@param		int|string		$newsletterId
	 *	@param		int|string		$newsletterReaderId
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function prepareReaderDataForNewsletter( int|string $newsletterId, int|string $newsletterReaderId )
	{
		throw new Exception( 'Method View_Helper_Newsletter::prepareReaderDataForNewsletter is deprecated' );
		$readerLetterId	= 0;
//		$queueId		= 0;
		$reader			= $this->logic->getReader( $newsletterReaderId );
		$words			= $this->env->getLanguage()->getWords( 'resource/newsletter' );
		$w				= (object) $words['send'];

		$baseUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();

		$emailHash	= base64_encode( $reader->email );
		$confirmKey	= substr( md5( 'InfoNewsletterSalt:'.$newsletterReaderId ), 10, 10 );
		$urlView	= $baseUrl.'info/newsletter/view/'.$readerLetterId;
		$urlConfirm	= $baseUrl.'info/newsletter/confirm/'.$newsletterReaderId.'/'.$confirmKey;
		$urlOptOut	= $baseUrl.'info/newsletter/unregister/'.$emailHash.'/'.$readerLetterId;
		$urlTrack	= $baseUrl.'info/newsletter/track/'.$readerLetterId;
		if( $this->preview )																								//  dry mode -> not tracking
			$urlTrack	= 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';			//  just embed an empty image

		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		return [
			'baseUrl'			=> $baseUrl,
			'templateId'		=> $newsletter->newsletterTemplateId,
			'newsletterId'		=> $newsletter->newsletterId,
			'readerId'			=> $newsletterReaderId,
			'senderAddress'		=> $newsletter->senderAddress,
			'senderName'		=> $newsletter->senderName,
			'subject'			=> $newsletter->subject,
			'nr'				=> $newsletterId,
			'salutation'		=> $words['salutations'][$reader->gender],
			'registeredAt'		=> date( $w->formatRegisteredAt, $reader->registeredAt ),
			'registerDate'		=> date( $w->formatRegisterDate, $reader->registeredAt ),
			'registerTime'		=> date( $w->formatRegisterTime, $reader->registeredAt ),
			'tracking'			=> HtmlTag::create( 'img', NULL, ['src' => $urlTrack] ),
			'linkConfirm'		=> $urlConfirm,
			'linkUnregister'	=> $urlOptOut,
			'linkView'			=> $urlView,
		];
	}

	/**
	 *	@todo		 correct urls
	 *	@todo		 code doc
	 *	@param		int|string		$readerLetterId
	 *	@deprecated use View_Helper_Newsletter_Mail::prepareData instead
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function prepareReaderDataForLetter( int|string $readerLetterId )
	{
		throw new Exception( 'Method View_Helper_Newsletter::prepareReaderDataForLetter is deprecated' );
		$letter			= $this->logic->getReaderLetter( $readerLetterId );
		$reader			= $this->logic->getReader( $letter->newsletterReaderId );
		$words			= $this->env->getLanguage()->getWords( 'resource/newsletter' );
		$w				= (object) $words['send'];

		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();

		$emailHash	= base64_encode( $reader->email );
		$urlView	= $baseUrl.'info/newsletter/view/'.$readerLetterId;
		$urlOptOut	= $baseUrl.'info/newsletter/unregister/'.$emailHash.'/'.$readerLetterId;
		$urlTrack	= $baseUrl.'info/newsletter/track/'.$readerLetterId;
		if( $this->preview )																								//  dry mode -> not tracking
			$urlTrack	= 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';			//  just embed an empty image

		$newsletter		= $this->logic->getNewsletter( $letter->newsletterId );
		return [
			'baseUrl'			=> $baseUrl,
			'templateId'		=> $newsletter->newsletterTemplateId,
			'newsletterId'		=> $newsletter->newsletterId,
			'readerId'			=> $letter->newsletterReaderId,
			'senderAddress'		=> $newsletter->senderAddress,
			'senderName'		=> $newsletter->senderName,
			'subject'			=> $newsletter->subject,
			'nr'				=> $letter->newsletterId,
			'salutation'		=> $words['salutations'][$reader->gender],
			'registeredAt'		=> date( $w->formatRegisteredAt, $reader->registeredAt ),
			'registerDate'		=> date( $w->formatRegisterDate, $reader->registeredAt ),
			'registerTime'		=> date( $w->formatRegisterTime, $reader->registeredAt ),
			'tracking'			=> HtmlTag::create( 'img', NULL, ['src' => $urlTrack] ),
			'linkUnregister'	=> $urlOptOut,
			'linkView'			=> $urlView,
		];
	}

	/**
	 *	@param		array		$matches
	 */

	protected function callbackReplacePlainColumns( array $matches )
	{
		$columns	= $matches[1];
		$content	= $matches[2];

		xmp( $content );
		$lines		= explode( "-##-", wordwrap( $content, floor( 78 / $columns ), "-##-" ) );
		print_m( $lines );

		die;
	}

	protected function realizeColumns( string $content, int $mode = 0 ): string
	{
		switch( $mode ){
			case 0:
//				$pattern	= "/\+col([0-9])\r?\n(.+)\r?\n-col[0-9]/s";
//				$content	= preg_replace_callback( $pattern, [$this, 'callbackReplacePlainColumns'], $content );
				break;
			case 1:
				$pattern	= "/\+col([0-9])/";
				$replace	= '<div class="layout-mail-\\1-columns">';
				$content	= preg_replace( $pattern, $replace, $content );
				$pattern	= "/\-col[0-9]/";
				$replace	= '</div>';
				$content	= preg_replace( $pattern, $replace, $content );
				break;
		}
		return $content;
	}

	/**
	 *	@todo		check if deprecated
	 *	@param		int|string			$newsletterId
	 *	@param		int|string|NULL		$readerId
	 *	@param		array				$data
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderNewsletterPlain( int|string $newsletterId, int|string|NULL $readerId = NULL, array $data = [] ): string
	{
		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		$helper		= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId );
		$data['title']		= $newsletter->heading;
		$data['content']	= wordwrap( $newsletter->plain, 78 );
		if( $readerId ){
			$reader		= $this->logic->getReader( $readerId );
			$data['prefix']			= $reader->prefix;
			$data['firstname']		= $reader->firstname;
			$data['surname']		= $reader->surname;
			$data['email']			= $reader->email;
		}
		return $helper->renderPlain( $data );
	}

	/**
	 *	@todo		check if deprecated
	 *	@param		int|string			$newsletterId
	 *	@param		int|string|NULL		$readerId
	 *	@param		array				$data
	 *	@param		bool				$strict
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderNewsletterHtml( int|string $newsletterId, int|string|NULL $readerId = NULL, array $data = [], bool $strict = TRUE ): string
	{
		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		$helper		= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId, $this->preview );
		$data['title']		= $newsletter->heading;
		$data['content']	= $newsletter->html;
		if( $readerId ){
			$reader		= $this->logic->getReader( $readerId );
			$data['prefix']			= $reader->prefix;
			$data['firstname']		= $reader->firstname;
			$data['surname']		= $reader->surname;
			$data['email']			= $reader->email;
		}
		return $helper->renderHtml( $data, $strict );
	}

	/**
	 *	@todo		check if deprecated
	 *	@param		array				$data
	 *	@return		string
	 */
	public function renderPlain( array $data ): string
	{
		$content	= $this->template->plain;

		if( $this->preview ){
			$data['linkView']		= "[Disabled in preview]";
			$data['linkUnregister']	= "[Disabled in preview]";
			$data['tracking']		= "[trackingLink=[#trackingUrl#]]";
		}

		foreach( $data as $key => $value )
			$content	= str_replace( '[#'.$key.'#]', $value, $content );
//		$content	= $this->realizeColumns( $content, 0 );
//		$content	= wordwrap( $content, 78 );
		return $content;
	}

	/**
	 *	@param		array		$data
	 *	@param		bool		$strict
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderHtml( array $data, bool $strict = TRUE ): string
	{
		$page		= new HtmlPage();
		$cache		= $this->env->getCache();
//		$page->setBaseHref( $this->env->url );

		$baseUrl	= $this->env->url;
		if( isset( $data['baseUrl'] ) )
			$baseUrl	= $data['baseUrl'];
		else if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
		$page->setBaseHref( $baseUrl );

		if( isset( $data['title'] ) )
			$page->setTitle( $data['title'] );

		$styles		= '';
		foreach( $this->template->styles as $url ){
			$cacheKey	= 'newsletter_resource_'.md5( $url );

			if( $cache->has( $cacheKey ) )
				$styles		.= $cache->get( $cacheKey );
			else{
				$content	= NetReader::readUrl( $url );
				$cache->set( $cacheKey, $content );
				$styles		.= $content;
			}
		}
		$styles		.= trim( $this->template->style );
		if( ( $styles = trim( CssCompressor::compressString( $styles ) ) ) )
			$page->addHead( HtmlTag::create( 'style', $styles ) );

		if( $this->preview ){
			$data['linkView']		= "javascript: alert('Disabled in preview.'); void(0);";
			$data['linkUnregister']	= "javascript: alert('Disabled in preview.'); void(0);";
			$data['tracking']		= "";
		}

		$content	= $this->template->html;														//  get HTML template
#		$content	= str_replace( '[#content#]', $data['content'], $content );						//  at first insert content ...
#		$content	= str_replace( '[#title#]', $data['title'], $content );							//  ... and title by replacing placeholders
#		unset( $data['content'] );																	//  remove content from template content data
#		unset( $data['title'] );																	//  remove title from template content data
		foreach( $data as $key => $value )															//  iterate template content data
			$content	= str_replace( '[#'.$key.'#]', $value, $content );							//  replace placeholder
		if( $strict )
			$content	= preg_replace( "/\[#.+#\]/U", "", $content );								//  remove not replace placeholders
//		$content	= $this->realizeColumns( $content, 1 );											//
		$page->addBody( $content );																	//  set final HTML as page body
		return $page->build( ['class' => 'mail mail-newsletter'] );							//  return rendered HTML page
	}
}
