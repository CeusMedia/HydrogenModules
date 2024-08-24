<?php

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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
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
		$template		= (object) array(
			'title'			=> $this->request->get( 'title' ),
			'templateId'	=> (int) $this->request->get( 'templateId' ),
			'style'			=> $this->view->loadContentFile( $pathDefaults.'default.css' ),
			'html'			=> $this->view->loadContentFile( $pathDefaults.'default.html' ),
			'plain'			=> $this->view->loadContentFile( $pathDefaults.'default.txt' ),
			'imprint'		=> $this->view->loadContentFile( $pathDefaults.'imprint.txt' ),
			'senderAddress'	=> '',
			'senderName'	=> '',
		);
		if( $copyTemplateId ){
			$template	= $this->logic->getTemplate( $copyTemplateId );
			$template->templateId	= (int) $this->request->get( 'templateId' );
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function ajaxGetStyle( int|string $templateId, bool $inEditor = FALSE ): never
	{
		$template	= $this->logic->getTemplate( $templateId );
		header( 'Content-Type: text/css' );
		print $template->style;
		if( $inEditor ){
			$pathThemeStyle	= $this->env->getPage()->getThemePath().'css/';
			print FileReader::load( $pathThemeStyle.'module.work.newsletter.css' );
		}
		exit;
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $templateId ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkTemplateId( $templateId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $templateId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$this->logic->editTemplate( $templateId, $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$conditions		= ['newsletterTemplateId' => $templateId];
		$newsletters	= $this->logic->getNewsletters( $conditions );

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
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function export( int|string $templateId ): void
	{
		$words		= (object) $this->getWords( 'export' );
		if( !$this->logic->checkTemplateId( $templateId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $templateId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
			$model->createFromTemplate( $templateId, $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$this->addData( 'template', $this->logic->getTemplate( $templateId ) );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$conditions		= [];
		$orders			= ['title' => 'ASC'];
		$this->addData( 'templates', $this->logic->getTemplates( $conditions, $orders ) );

		$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
		$this->addData( 'themes', $model->getAll() );
	}

	/**
	 *	@param		string		$themeId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function installTheme( string $themeId ): void
	{
		$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
		$theme	= $model->getFromId( $themeId );
		if( NULL === $theme ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}

		$imprint	= $this->getView()->loadcontentFile( 'html/work/newsletter/template/imprint.txt' );
		if( $theme->imprint )
			$imprint	= $theme->imprint;

		$data	= [
			'creatorId'		=> $this->env->getSession()->get( 'auth_user_id' ),
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
			'html'			=> FileReader::load( 'contents/themes/'.$theme->folder.'/template.html' ),
			'plain'			=> FileReader::load( 'contents/themes/'.$theme->folder.'/template.txt' ),
			'style'			=> FileReader::load( 'contents/themes/'.$theme->folder.'/template.css' ),
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function preview( string $format, int|string $templateId, bool $simulateOffline = FALSE ): void
	{
		try{
			$words		= (object) $this->getWords( 'preview' );
			$template	= $this->logic->getTemplate( $templateId );
			$data		= array(
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
			);
			if( strtolower( $format ) == 'text' ){
				$data['linkUnregister']	= '['.$words->alertDisabledInPreview.']';
				$data['linkView']		= '['.$words->alertDisabledInPreview.']';
			}
			$mail	= new View_Helper_Newsletter_Mail( $this->env );
			$mail->setTemplateId( $templateId );
			$mail->setData( $data );
			switch( strtolower( $format ) ){
				case 'text':
					$mail->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN );
					$content	= HtmlTag::create( 'pre', $mail->render() );
					break;
				case 'html':
					$mail->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
					$content	= $mail->render();
					break;
				default:
					throw new InvalidArgumentException( 'Format "'.$format.'" is not supported' );
			}
			print( $content );
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
			$path	= 'contents/themes/';
			$model	= new Model_Newsletter_Theme( $this->env, $path );
			$theme	= $model->get( $themeId );

			$css	= FileReader::load( $path.$theme->id.'/template.css' );
			$html	= FileReader::load( $path.$theme->id.'/template.html' );

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
			foreach( $theme->style as $style )
				$page->addStylesheet( (string) $style );
			$page->addHead( HtmlTag::create( 'style', $css ) );
			$page->addBody( $html );

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
			$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
			$this->addData( 'theme', $model->getFromId( $themeId ) );
			$this->addData( 'themePath', 'contents/themes/' );
		}
		catch( Exception ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}
	}

	protected function __onInit(): void
	{
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );
		if( $this->env->getModules()->has( 'Resource_Limiter' ) )
			$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );
	}
}
