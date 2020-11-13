<?php
class View_Helper_Newsletter_Mail
{
	const MODE_PLAIN		= 0;
	const MODE_HTML			= 1;

	protected $cachePath	= "contents/cache/";
	protected $data			= array();
	protected $mode			= self::MODE_PLAIN;
	protected $template;
	protected $letter;
	protected $reader;
	protected $newsletter;

	public function __construct( $env/*, $templateId = NULL*/ )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Newsletter( $env );
		if( !file_exists( $this->cachePath ) )
			FS_Folder_Editor::createFolder( $this->cachePath );
	}

	public function render()
	{
		if( !$this->template )
			throw new RuntimeException( 'No mail template set' );
		if( !$this->data )
			throw new RuntimeException( 'No mail data set' );
		if( $this->mode == self::MODE_HTML )
			return $this->renderHtml();
		else
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

	public function setReaderId( $readerId ): self
	{
		$this->logic->checkReaderId( $readerId );
		$this->reader	= $this->logic->getReader( $readerId );
		return $this;
	}

	public function setTemplateId( $templateId ): self
	{
		$this->logic->checkTemplateId( $templateId, TRUE );
		$this->template	= $this->logic->getTemplate( $templateId );
		$this->template->styles		= $this->logic->getTemplateAttributeList( $templateId, 'styles' );
//		$this->template->scripts	= $this->logic->getTemplateAttributeList( $templateId, 'scripts' );
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		$mode		Mail format: 0 - Plain, 1 - HTML
	 */
	protected function callbackReplacePlainColumns( $matches )
	{
		$columns	= $matches[1];
		$content	= $matches[2];

		xmp( $content );
		$lines		= explode( "-##-", wordwrap( $content, floor( 78 / $columns ), "-##-" ) );
		print_m( $lines );

		die;
	}

	protected function prepareData( $mode = self::MODE_PLAIN )
	{
		$data		= $this->data;
		$words		= $this->env->getLanguage()->getWords( 'resource/newsletter' );
		$w			= (object) $words['send'];

		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();
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
			$data['tracking']			= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $urlTrack ) );
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
//				$content	= preg_replace_callback( $pattern, array( $this, 'callbackReplacePlainColumns' ), $content );
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

	protected function renderHtml( $strict = TRUE )
	{
		$data	= $this->prepareData( self::MODE_HTML );
		$data['imprint']	= $this->renderImprint( TRUE );
		$page		= new UI_HTML_PageFrame();
		$page->addHead( UI_HTML_Tag::create( 'meta', NULL, array( 'charset' => 'utf-8' ) ) );
		$page->addHead( UI_HTML_Tag::create( 'meta', NULL, array( 'name' => 'x-apple-disable-message-reformatting' ) ) );
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
				$styles		.= File_Reader::load( $this->cachePath.md5( $url ) );
			else{
				$content	= Net_Reader::readUrl( $url );
				File_Writer::save( $this->cachePath.md5( $url ), $content );
				$styles		.= $content;
			}
		}
		$styles		.= trim( $this->template->style );
		if( ( $styles = trim( File_CSS_Compressor::compressString( $styles ) ) ) )
			$page->addHead( UI_HTML_Tag::create( 'style', $styles ) );

		$page->addHead( "<!--[if mso]><style>* {font-family: sans-serif !important;}</style><![endif]-->" );

/*		$scripts		= array();
		foreach( $this->template->scripts as $url ){
			if( file_exists( $this->cachePath.md5( $url ) ) )
				$scripts[]	= File_Reader::load( $this->cachePath.md5( $url ) );
			else{
				$content	= Net_Reader::readUrl( $url );
				File_Writer::save( $this->cachePath.md5( $url ), $content );
				$scripts[]	= $content;
			}
		}
		$scripts[]	= trim( $this->template->script );
		$scripts	= trim( join( "\n", $scripts ) );
		if( strlen( $scripts ) )
			$page->addHead( UI_HTML_Tag::create( 'script', $scripts ) );*/

		$data['tracking']	= '';
		$isPreview	= isset( $data['preview'] ) && $data['preview'];
		if( !$isPreview ){
			$script	= 'document.getElementById("browser-link").remove();';							//  script to remove browser link in browser view
			$page->addScript( 'window.addEventListener("load", function(){'.$script.'});' );		//  add script to HTML page
			if( isset( $data['linkTracking'] ) && $data['linkTracking'] ){							//  tracking link is defined
				$data['tracking']	= UI_HTML_Tag::create( 'img', NULL, array(						//  create tracking pixel image
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

	protected function renderPlain()
	{
		$data	= $this->prepareData( self::MODE_PLAIN );
		$data['imprint']	= $this->renderImprint();
		$content	= $this->template->plain;
		foreach( $data as $key => $value )
			$content	= str_replace( '[#'.$key.'#]', $value, $content );
		$content	= $this->realizeColumns( $content, 0 );
		$content	= wordwrap( $content, 78 );
		return $content;
	}
}
