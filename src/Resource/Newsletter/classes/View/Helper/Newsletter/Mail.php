<?php

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\FS\File\CSS\Compressor as CssFileCompressor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Newsletter_Mail
{
	const MODE_PLAIN		= 0;
	const MODE_HTML			= 1;

	protected Environment $env;
	protected Logic_Newsletter $logic;
	protected string $cachePath		= "contents/cache/";
	protected array $data			= [];
	protected int $mode				= self::MODE_PLAIN;
	protected ?object $template		= NULL;
	protected ?object $letter		= NULL;
	protected ?object $reader		= NULL;
	protected ?object $newsletter	= NULL;

	public function __construct( $env/*, $templateId = NULL*/ )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Newsletter( $env );
		if( !file_exists( $this->cachePath ) )
			FolderEditor::createFolder( $this->cachePath );
	}

	/**
	 *	@return		string
	 *	@throws		IoException
	 */
	public function render(): string
	{
		if( !$this->template )
			throw new RuntimeException( 'No mail template set' );
		if( !$this->data )
			throw new RuntimeException( 'No mail data set' );
		if( $this->mode == self::MODE_HTML )
			return $this->renderHtml();
		return $this->renderPlain();
	}

	public function setData( $data ): self
	{
		$this->data	= $data;
		return $this;
	}

	public function setMode( $mode = self::MODE_PLAIN ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	public function setNewsletterId( $newsletterId ): self
	{
		$this->logic->checkNewsletterId( $newsletterId, TRUE );
		$this->newsletter	= $this->logic->getNewsletter( $newsletterId );
		$this->setTemplateId( $this->newsletter->newsletterTemplateId );
		return $this;
	}

	public function setReaderLetterId( $readerLetterId ): self
	{
		$this->logic->checkReaderLetterId( $readerLetterId, TRUE );
		$this->letter	= $this->logic->getReaderLetter( $readerLetterId );
		$this->setNewsletterId( $this->letter->newsletterId );
		$this->setReaderId( $this->letter->newsletterReaderId );
		return $this;
	}

	/**
	 *	@param		int|string		$readerId
	 *	@return		self
	 */
	public function setReaderId( int|string $readerId ): self
	{
		$this->logic->checkReaderId( $readerId );
		$this->reader	= $this->logic->getReader( $readerId );
		return $this;
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		self
	 */
	public function setTemplateId( int|string $templateId ): self
	{
		$this->logic->checkTemplateId( $templateId, TRUE );
		$this->template	= $this->logic->getTemplate( $templateId );
		$this->template->styles		= $this->logic->getTemplateAttributeList( $templateId, 'styles' );
//		$this->template->scripts	= $this->logic->getTemplateAttributeList( $templateId, 'scripts' );
		return $this;
	}

	//  --  PROTECTED  --  //

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

	protected function prepareData( int $mode = self::MODE_PLAIN ): array
	{
		$data		= $this->data;
		$words		= $this->env->getLanguage()->getWords( 'resource/newsletter' );
		$w			= (object) $words['send'];

		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
		$data['baseUrl']		= $baseUrl;
		$data['templateId']		= $this->template->newsletterTemplateId;

		if( $this->newsletter ){
			$data['nr']				= $this->newsletter->newsletterId;
			$data['newsletterId']	= $this->newsletter->newsletterId;
			$data['senderAddress']	= $this->newsletter->senderAddress;
			$data['senderName']		= $this->newsletter->senderName;
			$data['subject']		= $this->newsletter->subject;
			$data['content']		= $this->newsletter->plain;
			if( $mode === self::MODE_HTML )
				$data['content']	= $this->newsletter->html;
		}

		if( $this->reader ){
			$confirmKey	= substr( md5( 'InfoNewsletterSalt:'.$this->reader->newsletterReaderId ), 10, 10 );
			$urlConfirm	= $baseUrl.'info/newsletter/confirm/'.$this->reader->newsletterReaderId.'/'.$confirmKey;
			$data['prefix']				= $this->reader->prefix;
			$data['firstname']			= $this->reader->firstname;
			$data['surname']			= $this->reader->surname;
			$data['readerId']			= $this->reader->newsletterReaderId;
			$data['registeredAt']		= date( $w->formatRegisteredAt, $this->reader->registeredAt );
			$data['registerDate']		= date( $w->formatRegisterDate, $this->reader->registeredAt );
			$data['registerTime']		= date( $w->formatRegisterTime, $this->reader->registeredAt );
			$data['salutation']			= $words['salutations'][$this->reader->gender];
			$data['linkConfirm']		= $urlConfirm;
		}

		if( $this->letter ){
			$data['readerId']			= $this->reader->newsletterReaderId;
			$emailHash	= base64_encode( $this->reader->email );
			$urlView	= $baseUrl.'info/newsletter/view/'.$this->letter->newsletterReaderLetterId;
			$urlOptOut	= $baseUrl.'info/newsletter/unregister/'.$emailHash.'/'.$this->letter->newsletterReaderLetterId;
			$urlTrack	= $baseUrl.'info/newsletter/track/'.$this->letter->newsletterReaderLetterId;
			$data['linkView']			= $urlView;
			$data['linkUnregister']		= $urlOptOut;
			$data['linkTracking']		= $urlTrack;
			$data['tracking']			= HtmlTag::create( 'img', NULL, ['src' => $urlTrack] );
		}
//		print_m( $data ); die();
/*		else{
			$urlTrack	= 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';			//  just embed an empty image
		}*/
		return $data;
	}

	protected function realizeColumns( $content, $mode = 0 )
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
	 *	@param		boolean		$strict
	 *	@return		string
	 *	@throws		IoException
	 */
	protected function renderHtml( bool $strict = TRUE ): string
	{
		$data	= $this->prepareData( self::MODE_HTML );
		$data['imprint']	= $this->renderImprint( TRUE );
		$page		= new HtmlPage();
		$page->addHead( HtmlTag::create( 'meta', NULL, ['charset' => 'utf-8'] ) );
		$page->addHead( HtmlTag::create( 'meta', NULL, ['name' => 'x-apple-disable-message-reformatting'] ) );
		$page->addHead( '<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->' );
		$page->addMetaTag( "name", "viewport", "width=device-width" );
		$page->addMetaTag( "http-equiv", "X-UA-Compatible", "IE=edge" );
		$page->addMetaTag( "name", "viewport", "width=device-width" );
		$page->setBaseHref( $data['baseUrl'] );

		if( isset( $data['title'] ) )
			$page->setTitle( $data['title'] );

		$styles		= "";
		foreach( $this->template->styles as $url ){
			if( file_exists( $this->cachePath.md5( $url ) ) )
				$styles		.= FileReader::load( $this->cachePath.md5( $url ) );
			else{
				$content	= NetReader::readUrl( $url );
				FileWriter::save( $this->cachePath.md5( $url ), $content );
				$styles		.= $content;
			}
		}
		$styles		.= trim( $this->template->style );
		if( ( $styles = trim( CssFileCompressor::compressString( $styles ) ) ) )
			$page->addHead( HtmlTag::create( 'style', $styles ) );

		$page->addHead( "<!--[if mso]><style>* {font-family: sans-serif !important;}</style><![endif]-->" );

/*		$scripts		= [];
		foreach( $this->template->scripts as $url ){
			if( file_exists( $this->cachePath.md5( $url ) ) )
				$scripts[]	= FileReader::load( $this->cachePath.md5( $url ) );
			else{
				$content	= NetReader::readUrl( $url );
				FileWriter::save( $this->cachePath.md5( $url ), $content );
				$scripts[]	= $content;
			}
		}
		$scripts[]	= trim( $this->template->script );
		$scripts	= trim( join( "\n", $scripts ) );
		if( strlen( $scripts ) )
			$page->addHead( HtmlTag::create( 'script', $scripts ) );*/

		$data['tracking']	= '';
		$isPreview	= isset( $data['preview'] ) && $data['preview'];
		if( !$isPreview ){
			$script	= 'document.getElementById("browser-link").remove();';							//  script to remove browser link in browser view
			$page->addScript( 'window.addEventListener("load", function(){'.$script.'});' );		//  add script to HTML page
			if( isset( $data['linkTracking'] ) && $data['linkTracking'] ){							//  tracking link is defined
				$data['tracking']	= HtmlTag::create( 'img', NULL, array(						//  create tracking pixel image
					'src' => $data['linkTracking']													//  ... pointing to tracking URL
				) );
			}
		}

		$content	= $this->template->html;														//  get HTML template
		foreach( $data as $key => $value )															//  iterate template content data
			$content	= str_replace( '[#'.$key.'#]', $value, $content );							//  replace placeholder
		if( $strict )
			$content	= preg_replace( "/\[#.+#\]/U", "", $content );								//  remove not replace placeholders
		$content	= $this->realizeColumns( $content, 1 );											//
		$page->addBody( $content );																	//  set final HTML as page body
		return $page->build( array(																	//  return rendered HTML page
			'class'		=> 'mail',
			'style'		=> 'mso-line-height-rule: exactly;',
		) );
	}

	protected function renderImprint( $asHtml = FALSE )
	{
		$content	= $this->template->imprint;
		if( $asHtml ){
			$content	= preg_replace( "/\n/", "<br/>", $content );
			$content	= preg_replace( "/(https?:\/\/(([^\s]+))\/?)/", '<a href="\\1">\\2</a>', $content );
			$content	= preg_replace( "/([^\s]+@[^\s]+)/", '<a href="mailto:\\1">\\1</a>', $content );
		}
		return $content;
	}

	protected function renderPlain(): string
	{
		$data	= $this->prepareData();
		$data['imprint']	= $this->renderImprint();
		$content	= $this->template->plain;
		foreach( $data as $key => $value )
			$content	= str_replace( '[#'.$key.'#]', $value, $content );
		$content	= $this->realizeColumns( $content, 0 );
		return wordwrap( $content, 78 );
	}
}
