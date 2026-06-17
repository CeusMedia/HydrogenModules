<?php /** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpMultipleClassDeclarationsInspection */

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
	public const MODE_PLAIN			= 0;
	public const MODE_HTML			= 1;
	public const MODE_HTML_TRACKING	= 2;

	protected Environment $env;
	protected Logic_Newsletter $logic;
	protected int $mode									= self::MODE_PLAIN;
	protected array $data								= [];
	protected array $originalData						= [];
	protected ?Entity_Newsletter $newsletter			= NULL;
	protected ?Entity_Newsletter_Reader $reader			= NULL;
	protected ?Entity_Newsletter_Reader_Letter $letter	= NULL;
	protected ?Entity_Newsletter_Template $template		= NULL;
	protected string $cachePath							= 'contents/cache/';

	public function __construct( $env/*, $templateId = NULL*/ )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Newsletter( $env );
		if( !file_exists( $this->cachePath ) )
			FolderEditor::createFolder( $this->cachePath );
	}

	public function getData(): array
	{
		return $this->data;
	}

	/**
	 *	@return		string
	 *	@throws		IoException
	 */
	public function render(): string
	{
		if( !$this->template )
			throw new RuntimeException( 'No mail template set' );
//		if( !$this->data )
//			throw new RuntimeException( 'No mail data set' );
		if( in_array( $this->mode, [self::MODE_HTML, self::MODE_HTML_TRACKING] ) )
			return $this->renderHtml();
		return $this->renderPlain();
	}

	public function setNewsletter( Entity_Newsletter $newsletter ): self
	{
		$this->newsletter	= $newsletter;
		return $this;
	}

	public function setTemplate( Entity_Newsletter_Template $template ): self
	{
		$this->template	= $template;
		return $this;
	}

	public function setReader( Entity_Newsletter_Reader $reader ): self
	{
		$this->reader	= $reader;
		return $this;
	}

	public function setReaderLetter( Entity_Newsletter_Reader_Letter $readerLetter ): self
	{
		$this->letter	= $readerLetter;
		return $this;
	}

	public function setData( $data ): self
	{
		$this->originalData	= $data;
		$this->data	= $data;
		return $this;
	}

	/**
	 *	Set rendering mode, one of MODE_PLAIN, MODE_HTML, MODE_HTML_TRACKING.
	 *	Runs prepareData.
	 *	@param		int		$mode
	 *	@return		self
	 */
	public function setMode( int $mode = self::MODE_PLAIN ): self
	{
		if( [] === $this->data )
			throw new RuntimeException( 'Set data, first!' );
		$this->mode	= $mode;
		$this->prepareData( $mode );
		return $this;
	}

	/**
	 *	@param		int|string		$newsletterId
	 *	@return		self
	 */
	public function setNewsletterId( int|string $newsletterId ): self
	{
		$this->logic->checkNewsletterId( $newsletterId, TRUE );
		$this->newsletter	= $this->logic->getNewsletter( $newsletterId );
		$this->setTemplateId( $this->newsletter->newsletterTemplateId );
		return $this;
	}

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		self
	 */
	public function setReaderLetterId( int|string $readerLetterId ): self
	{
		$this->logic->checkReaderLetterId( $readerLetterId, TRUE );
		$this->setReaderLetter( $this->logic->getReaderLetter( $readerLetterId ) );
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
	 * @todo finish impl
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
		$data		= $this->originalData;
		$words		= $this->env->getLanguage()->getWords( 'resource/newsletter' );
		$w			= (object) $words['send'];

		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
		$data['baseUrl']		= $baseUrl;

		if( $this->template ){
			$data['templateId']		= $this->template->newsletterTemplateId;
			$data['linkTracking']		= '';
			$data['imprint']		= $this->template->imprint;
		}

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

		if( $this->reader && $this->letter ){
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

		if( (bool) ( $this->data['preview'] ?? FALSE ) ){
			if( $mode === self::MODE_HTML ){
				$data['linkView']		= "javascript: alert('Disabled in preview.'); void(0);";
				$data['linkUnregister']	= "javascript: alert('Disabled in preview.'); void(0);";
				$data['linkTracking']	= "";
				$data['tracking']		= "";
			}
			else if( $mode === self::MODE_PLAIN ){
				$data['linkView']		= "[Disabled in preview]";
				$data['linkUnregister']	= "[Disabled in preview]";
				$data['linkTracking']	= "";
				$data['tracking']		= "[trackingLink=[#trackingUrl#]]";
			}
		}
		
//		print_m( $data ); die();
/*		else{
			$urlTrack	= 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';			//  just embed an empty image
		}*/
		$this->data	= $data;
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

	protected function makeHtmlLinksTrackable( string $html ): string
	{
		if( NULL === $this->reader || NULL === $this->letter )
			return $html;

		$dom	= new DOMDocument();
		libxml_use_internal_errors( TRUE );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		foreach( $dom->getElementsByTagName( 'a' ) as $anchor ){
			$url	= $anchor->getAttribute( 'href' );
			if( !str_starts_with( $url, 'http' ) )
				continue;
			if( str_contains( $url, 'info/newsletter/' ) )
				continue;
			$model	= Model_Newsletter_Link::getInstance( $this->env );
			$link	= $model->getByIndex( 'url', $url );
			if( NULL !== $link )
				$linkId	= $link->newsletterLinkId;
			else{
				$linkId	= $model->add( [
					'url'		=> $url,
					'title'		=> trim( $anchor->textContent ),
					'timestamp'	=> time(),
				] );
			}
			$baseUrl	= $this->data['baseUrl'] ?? $this->env->url;
			$anchor->setAttribute( 'href', $baseUrl.join( '/', [
					'info/newsletter/track',
					$this->letter->newsletterReaderLetterId,
					$linkId,
				] ) );
		}
		return $dom->saveHTML();
	}

	/**
	 *	@param		boolean		$strict
	 *	@return		string
	 *	@throws		IoException
	 */
	protected function renderHtml( bool $strict = TRUE ): string
	{
		$this->data['imprint']	= $this->renderImprint( TRUE );

		if( $this->newsletter )
			$content	= $this->newsletter->html;													//  get HTML from newsletter
		else if( $this->template )
			$content	= $this->template->html;													//  get HTML from newsletter template
		else
			throw new RuntimeException( 'Neither newsletter not template set' );

		foreach( $this->data as $key => $value )													//  iterate template content data
			$content	= str_replace( '[#'.$key.'#]', $value, $content );					//  replace placeholder
		$content	= $this->realizeColumns( $content, 1 );									//
		if( self::MODE_HTML_TRACKING === $this->mode )
			$content	= $this->makeHtmlLinksTrackable( $content );
		return $content;
	}

	/**
	 *	@param		bool		$asHtml
	 *	@return		string
	 */
	protected function renderImprint( bool $asHtml = FALSE ): string
	{
		$content	= $this->template->imprint ?? '';
		if( $asHtml ){
			$content	= preg_replace( "/\n/", "<br/>", $content );
			$content	= preg_replace( "/(https?:\/\/((\S+))\/?)/", '<a href="\\1">\\2</a>', $content );
			$content	= preg_replace( "/(\S+@\S+)/", '<a href="mailto:\\1">\\1</a>', $content );
		}
		return $content;
	}

	/**
	 *	@return		string
	 */
	protected function renderPlain(): string
	{
		$this->data['imprint']	= $this->renderImprint();

		if( $this->newsletter )
			$content	= $this->newsletter->plain;													//  get plaintext from newsletter
		else if( $this->template )
			$content	= $this->template->plain;													//  get plaintext from newsletter template
		else
			throw new RuntimeException( 'Neither newsletter not template set' );

		foreach( $this->data as $key => $value )
			$content	= str_replace( '[#'.$key.'#]', $value, $content );
		$content	= $this->realizeColumns( $content, 0 );
		return wordwrap( $content, 78 );
	}
}
