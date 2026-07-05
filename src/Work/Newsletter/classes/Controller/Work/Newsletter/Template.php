<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;
use CeusMedia\HydrogenFramework\View;

class Controller_Work_Newsletter_Template extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected Logic_Newsletter_Editor $logic;

	/**	@var	Messenger					$messenger */
	protected Messenger $messenger;

	/**	@var	HttpRequest					$request */
	protected HttpRequest $request;

	/**	@var	PartitionSession			$session */
	protected PartitionSession $session;

	protected ?Logic_Limiter $limiter		= NULL;

	protected Model_Newsletter_Theme $modelTheme;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		$pathDefaults	= 'html/work/newsletter/template/';
		$words			= (object) $this->getWords( 'add' );
		$copyTemplateId	= (int) $this->request->get( 'templateId' );
		if( $this->request->has( 'save' ) ){
			$data			= $this->request->getAll();
			if( $this->logic->getTemplates( ['title' => $data['title']] ) ){
				$this->messenger->noteError( $words->msgErrorTitleExists );
			}
			else{
				if( $copyTemplateId ){
					$data				= (array) $this->logic->getTemplate( $copyTemplateId );
					$data['title']		= $this->request->get( 'title' );
					unset( $data['newsletterTemplateId'] );
					unset( $data['status'] );
				}
				$data['createdAt']	= time();
				$data['modifiedAt']	= time();
				$templateId		= $this->logic->addTemplate( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->setContentTab( $templateId, 0 );
			}
		}
		$templates		= $this->logic->getTemplates( [], ['title' => 'ASC'] );
		$template		= (object) [
			'title'			=> $this->request->get( 'title' ),
			'templateId'	=> (int) $this->request->get( 'templateId' ),
			'style'			=> $this->view->loadContentFile( $pathDefaults.'default.css' ),
			'html'			=> $this->view->loadContentFile( $pathDefaults.'default.html' ),
			'plain'			=> $this->view->loadContentFile( $pathDefaults.'default.txt' ),
			'imprint'		=> $this->view->loadContentFile( $pathDefaults.'imprint.txt' ),
			'senderAddress'	=> '',
			'senderName'	=> '',
		];
		if( $copyTemplateId ){
			$template	= $this->logic->getTemplate( $copyTemplateId );
		}

		if( $this->request->has( 'plain' ) )
			$template->plain	= $this->request->get( 'plain' );
		if( $this->request->has( 'html' ) )
			$template->html	= $this->request->get( 'html' );
		if( $this->request->has( 'style' ) )
			$template->style	= $this->request->get( 'style' );

		$this->addData( 'templates', $templates );
		$this->addData( 'template', $template );

		$model			= new Model_Newsletter_Template( $this->env );
		$totalTemplates	= $model->count();
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Template:maxItems', $totalTemplates + 1 ) ){
			$this->messenger->noteNotice( 'Limit erreicht. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'totalTemplates', $totalTemplates );
		$this->addData( 'mailTemplates', Logic_Mail::getInstance( $this->env )->getTemplates() );
	}

	public function addStyle( int $templateId, $url = NULL ): void
	{
		$url	= strlen( trim( $url ) ) ? $url : $this->request->get( 'style_url' );				//
		$this->logic->addTemplateStyle( $templateId, $url );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	/**
	 *	Displays template style.
	 *	This method is used to insert template style into TinyMCE editors.
	 *	@access		public
	 *	@param		int|string		$templateId		ID of template
	 *	@param		boolean			$inEditor		Flag: set additional style for TinyMCE editor
	 *	@return		never
	 *	@throws		ReflectionException
	 */
	public function ajaxGetStyle( int|string $templateId, bool $inEditor = FALSE ): never
	{
		header( 'Content-Type: text/css' );
		$template	= $this->logic->getTemplate( $templateId );
		print $template->style;

		if( $inEditor ){
			print $this->logic->getMailTemplateFromTemplate( $template )->css;
			$pathThemeStyle	= $this->env->getPage()->getThemePath().'css/';
			print FileReader::load( $pathThemeStyle.'module.work.newsletter.css' );
		}
		exit;
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function edit( int|string $templateId ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkTemplateId( $templateId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $templateId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$this->handleEditRequest( $templateId, $words );
		}
		$conditions		= ['newsletterTemplateId' => $templateId];
		$orders			= ['newsletterId' => 'DESC'];
		$limits			= [0, 10];
		$newsletters	= $this->logic->getNewsletters( $conditions, $orders, $limits );

		$conditions		= ['newsletterTemplateId' => $templateId, 'status' => 2];
		$isUsed			= count( $this->logic->getNewsletters( $conditions ) );

		$tab			= $this->session->get( 'work.newsletter.template.content.tab' );
		$format			= $tab == 2 ? 'text' : 'html';

		$this->addData( 'newsletters', $newsletters );
		$this->addData( 'templateId', $templateId );
		$this->addData( 'template', $this->logic->getTemplate( $templateId ) );
		$this->addData( 'styles', $this->logic->getTemplateAttributeList( $templateId, 'styles' ) );
		$this->addData( 'isUsed', $isUsed );
		$this->addData( 'format', $format );
		$this->addData( 'mailTemplates', Logic_Mail::getInstance( $this->env )->getTemplates() );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function export( int|string $templateId ): void
	{
		$words		= (object) $this->getWords( 'export' );
		if( !$this->logic->checkTemplateId( $templateId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $templateId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$this->modelTheme->createFromTemplate( $templateId, $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$this->addData( 'template', $this->logic->getTemplate( $templateId ) );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index(): void
	{
		$conditions		= [];
		$orders			= ['title' => 'ASC'];
		$this->addData( 'templates', $this->logic->getTemplates( $conditions, $orders ) );
		if( $this->env->getAcl()->has( 'work/newsletter/template', 'installTheme' ) )
			$this->addData( 'themes', $this->modelTheme->getAll() );
	}

	/**
	 *	@param		string		$themeId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function installTheme( string $themeId ): void
	{
		$theme		= $this->modelTheme->getFromId( $themeId );
		$pathThemes	= $this->logic->getNewsletterThemesPath();
		if( NULL === $theme ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}

		$imprint	= $this->getView()->loadContentFile( 'html/work/newsletter/template/imprint.txt' );
		if( $theme->imprint )
			$imprint	= $theme->imprint;

		$data	= [
			'creatorId'		=> $this->env->getSession()->get( Logic_Authentication::$sessionKeyAuthUserId ),
			'themeId'		=> $themeId,
			'status'		=> 0,
			'title'			=> $theme->title,
			'version'		=> $theme->version,
			'description'	=> $theme->description,
			'senderName'	=> $theme->sender->name,
			'senderAddress'	=> $theme->sender->address,
			'authorName'	=> $theme->author->name,
			'authorEmail'	=> $theme->author->email,
			'authorCompany'	=> $theme->author->company,
			'authorUrl'		=> $theme->author->url,
			'license'		=> $theme->license,
			'licenseUrl'	=> $theme->licenseUrl,
			'imprint'		=> $imprint,
			'createdAt'		=> strtotime( $theme->created ),
			'modifiedAt'	=> strtotime( $theme->modified ),
			'html'			=> FileReader::load( $pathThemes.$theme->folder.'/template.html' ),
			'plain'			=> FileReader::load( $pathThemes.$theme->folder.'/template.txt' ),
			'style'			=> FileReader::load( $pathThemes.$theme->folder.'/template.css' ),
		];
		$templateId	= $this->logic->addTemplate( $data );
		if( isset( $theme->styles ) && is_array( $theme->styles ) )
			foreach( $theme->styles as $styleUrl )
				$this->logic->addTemplateStyle( $templateId, $styleUrl );
		$words	= (object) $this->getWords( 'install' );
		$this->messenger->noteSuccess( $words->msgSuccess, $theme->title );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	/**
	 *	@param		string			$format
	 *	@param		int|string		$templateId
	 *	@param		bool			$simulateOffline
	 *	@return		void
	 */
	public function preview( string $format, int|string $templateId, bool $simulateOffline = FALSE ): void
	{
		try{
			$words		= (object) $this->getWords( 'preview' );
			$template	= $this->logic->getTemplate( $templateId );
			$data		= array_merge( [
				'title'				=> sprintf( $words->title, $template->title ),
				'content'			=> wordwrap( $words->content ),
				'salutation'		=> $words->salutation,
				'prefix'			=> $words->prefix,
				'firstname'			=> $words->firstname,
				'surname'			=> $words->surname,
				'registerDate'		=> $words->registerDate,
				'registerTime'		=> $words->registerTime,
				'linkUnregister'	=> "javascript:alert('".$words->alertDisabledInPreview."')",
				'linkView'			=> "javascript:alert('".$words->alertDisabledInPreview."')",
				'preview'			=> TRUE,
			], [
				'newsletterId'	=> 0,
				'templateId'	=> $templateId,
				'preview'		=> TRUE,
			] );
			if( strtolower( $format ) == 'text' ){
				$data['linkUnregister']	= '['.$words->alertDisabledInPreview.']';
				$data['linkView']		= '['.$words->alertDisabledInPreview.']';
			}

			$mail		= new Mail_Newsletter( $this->env, $data );
			$response	= $this->env->getResponse();
			switch( strtolower( trim( $format ) ) ){
				case 'css':
					$content	= $template->style;
					if( '0' !== (string) $template->mailTemplateId ){
						$mailTemplate	= $this->logic->getMailTemplateFromTemplate( $template );
						$content	.= $mailTemplate->css;
					}
					$response->setHeader( 'Content-Type', 'text/css' );
					break;
				case 'html':
					$content	= $mail->getContent( Mail_Abstract::CONTENT_TYPE_HTML_RENDERED );
					$response->setHeader( 'Content-Type', 'text/html' );
					break;
				case 'text':
				default:
					$content	= $mail->getContent( Mail_Abstract::CONTENT_TYPE_TEXT_RENDERED );
					$response->setHeader( 'Content-Type', 'text/plain' );
					break;
			}
			$response->setBody( $content );
			$response->send();
			die;
		}
		catch( Exception $e ){
//			print( "There has been an error." );
			HtmlExceptionPage::display( $e );
		}
		exit;
	}

	public function previewTheme( string $themeId ): void
	{
		try{
			$path	= $this->logic->getNewsletterThemesPath();
			/** @var ?Entity_Newsletter_Theme $theme */
			$theme	= $this->modelTheme->get( $themeId );

			$css	= FileReader::load( $path.$theme->folder.'/template.css' );
			$html	= FileReader::load( $path.$theme->folder.'/template.html' );

			$view		= new View( $this->env );
			$imprint	= $view->loadContentFile( 'html/work/newsletter/template/imprint.txt' );
			$imprint	= preg_replace( "/(https?:\/\/(\S+)\/?)/", '<a href="\\1">\\2</a>', $imprint );
			$imprint	= preg_replace( "/(\S+@\S+)/", '<a href="mailto:\\1">\\1</a>', $imprint );
			$imprint	= preg_replace( "/\n/", "<br/>", $imprint );
			$html		= str_replace( "[#imprint#]", $imprint, $html );
			$words		= $this->getWords( 'preview' );
			$words['title']	= sprintf( $words['title'], $theme->title );
			foreach( $words as $key => $value )
				$html	= str_replace( "[#".$key."#]", $value, $html );
			$html	= preg_replace( "/\[#.+#\]/", '', $html );
			$page	= new HtmlPage();
			foreach( explode( ',', $theme->styles ) as $style )
				if( '' !== trim( $style ) )
					$page->addStylesheet( (string) $style );
			$page->addHead( HtmlTag::create( 'style', $css ) );
			$page->addBody( $html );

			$mail	= new Mail_Example( $this->env, [
				'html'	=> $html,
			] );
			$html	= $mail->getContent( Mail_Abstract::CONTENT_TYPE_HTML_RENDERED );
			print( $html );
			exit;

			print( $page->build( ['class' => 'mail'] ) );
			exit;

		}
		catch( Exception $e ){
			$this->messenger->noteError( $e->getMessage() );
//			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 */
	public function remove( int|string $templateId ): void
	{
		$this->logic->removeTemplate( $templateId );
		$words	= (object) $this->getWords( 'remove' );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './work/newsletter/template' );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		$index
	 *	@return		void
	 */
	public function removeStyle( int|string $templateId, $index ): void
	{
		$this->logic->removeTemplateStyle( $templateId, $index );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	public function setContentTab( int|string $templateId, $tabKey ): void
	{
		$this->session->set( 'work.newsletter.template.content.tab', $tabKey );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	public function viewTheme( string $themeId ): void
	{
		try{
			$this->addData( 'theme', $this->modelTheme->getFromId( $themeId ) );
			$this->addData( 'themePath', $this->logic->getNewsletterThemesPath() );
		}
		catch( Exception ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
		$this->modelTheme	= new Model_Newsletter_Theme( $this->env, $this->logic->getNewsletterThemesPath() );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );
		if( $this->env->getModules()->has( 'Resource_Limiter' ) )
			$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );
		$this->addData( 'pathNewsletterThemes', $this->logic->getNewsletterThemesPath() );
	}

	/**
	 *	This catches all POST requests on template properties:
	 *	- Details
	 *	- HTML
	 *	- Plaintext
	 *	- CSS
	 *	Style files are handled by addStyle and removeStyle.
	 *	@param		int|string	$templateId
	 *	@param		object		$words
	 *	@return		void
	 */
	protected function handleEditRequest( int|string $templateId, object $words ): void
	{
		$mayBeComing = [
			'mailTemplateId',
			'title',
			'senderAddress',
			'senderAddress',
			'senderName',
			'authorName',
			'authorEmail',
			'authorCompany',
			'authorUrl',
			'imprint',
			'html',
			'plain',
			'style',
		];
		$update	= [];
		if( $this->request->has( 'title' ) )
			$update['status']	= (int) $this->request->get( 'status', 0 );
		foreach( $mayBeComing as $may )
			if( $this->request->has( $may ) )
				$update[$may] = $this->request->get( $may );

		if( [] !== $update )
			$this->logic->editTemplate( $templateId, $update );

		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( 'edit/'.$templateId, TRUE );
	}
}
