<?php
class Controller_Work_Newsletter_Template extends CMF_Hydrogen_Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected $logic;

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger */
	protected $messenger;

	/**	@var	object											$request */
	protected $request;

	/**	@var	object											$session */
	protected $session;

	protected $moduleConfig;

	protected $limiter;

	public function add()
	{
		$pathDefaults	= 'html/work/newsletter/template/';
		$words			= (object) $this->getWords( 'add' );
		$copyTemplateId	= (int) $this->request->get( 'templateId' );
		if( $this->request->has( 'save' ) ){
			$data			= $this->request->getAll();
			if( $this->logic->getTemplates( array( 'title' => $data['title'] ) ) ){
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
		$templates		= $this->logic->getTemplates( array(), array( 'title' => 'ASC' ) );
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

	public function addStyle( $templateId, $url = NULL )
	{
		$url	= strlen( trim( $url ) ) ? $url : $this->request->get( 'style_url' );				//
		$this->logic->addTemplateStyle( $templateId, $url );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	/**
	 *	Displays template style.
	 *	This method is used to insert template style into TinyMCE editors.
	 *	@access		public
	 *	@param		integer		$templateId		ID of template
	 *	@param		boolean		$inEditor		Flag: set additional style for TinyMCE editor
	 *	@return		void
	 */
	public function ajaxGetStyle( $templateId, $inEditor = FALSE )
	{
		$template	= $this->logic->getTemplate( $templateId );
		header( 'Content-Type: text/css' );
		print $template->style;
		if( $inEditor ){
			$pathThemeStyle	= $this->env->getPage()->getThemePath().'css/';
			print File_Reader::load( $pathThemeStyle.'module.work.newsletter.css' );
		}
		exit;
	}

	public function edit( $templateId )
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
		$conditions		= array( 'newsletterTemplateId' => $templateId );
		$newsletters	= $this->logic->getNewsletters( $conditions );

		$conditions		= array( 'newsletterTemplateId' => $templateId, 'status' => 2 );
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

	public function export( $templateId )
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

	public function index()
	{
		$conditions		= array();
		$orders			= array( 'title' => 'ASC' );
		$this->addData( 'templates', $this->logic->getTemplates( $conditions, $orders ) );

		$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
		$this->addData( 'themes', $model->getAll() );
	}

	public function installTheme( $themeId )
	{
		try{
			$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
			$theme	= $model->getFromId( $themeId );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}

		$imprint	= $this->getView()->loadcontentFile( 'html/work/newsletter/template/imprint.txt' );
		if( $theme->imprint )
			$imprint	= $theme->imprint;

		$data	= array(
			'creatorId'		=> $this->env->getSession()->get( 'userId' ),
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
			'html'			=> \FS_File_Reader::load( 'contents/themes/'.$theme->folder.'/template.html' ),
			'plain'			=> \FS_File_Reader::load( 'contents/themes/'.$theme->folder.'/template.txt' ),
			'style'			=> \FS_File_Reader::load( 'contents/themes/'.$theme->folder.'/template.css' ),
		);
		$templateId	= $this->logic->addTemplate( $data );
		if( isset( $theme->styles ) && is_array( $theme->styles ) )
			foreach( $theme->styles as $styleUrl )
				$this->logic->addTemplateStyle( $templateId, $styleUrl );
		$words	= (object) $this->getWords( 'install' );
		$this->messenger->noteSuccess( $words->msgSuccess, $theme->title );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function preview( $format, $templateId, $simulateOffline = FALSE )
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
					$content	= UI_HTML_Tag::create( 'pre', $mail->render() );
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
			UI_HTML_Exception_Page::display( $e );
		}
		exit;
	}

	public function previewTheme( $theme )
	{
		try{
			$path	= 'contents/themes/';
			$model	= new Model_Newsletter_Theme( $this->env, $path );
			$theme	= $model->get( $theme );

			$css	= \FS_File_Reader::load( $path.$theme->id.'/template.css' );
			$html	= \FS_File_Reader::load( $path.$theme->id.'/template.html' );

			$view		= new CMF_Hydrogen_View( $this->env );
			$imprint	= $view->loadContentFile( 'html/work/newsletter/template/imprint.txt' );
			$imprint	= preg_replace( "/(https?:\/\/(([^\s]+))\/?)/", '<a href="\\1">\\2</a>', $imprint );
			$imprint	= preg_replace( "/([^\s]+@[^\s]+)/", '<a href="mailto:\\1">\\1</a>', $imprint );
			$imprint	= preg_replace( "/\n/", "<br/>", $imprint );
			$html		= str_replace( "[#imprint#]", $imprint, $html );
			$words		= $this->getWords( 'preview' );
			$words['title']	= sprintf( $words['title'], $theme->title );
			foreach( $words as $key => $value )
				$html	= str_replace( "[#".$key."#]", $value, $html );
			$html	= preg_replace( "/\[#.+#\]/", '', $html );
			$page	= new \UI_HTML_PageFrame();
			foreach( $theme->style as $style )
				$page->addStylesheet( (string) $style );
			$page->addHead( UI_HTML_Tag::create( 'style', $css ) );
			$page->addBody( $html );

			print( $page->build( array( 'class' => 'mail' ) ) );
			exit;

		}
		catch( Exception $e ){
			$this->messenger->noteError( $e->getMessage() );
//			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}
	}

	public function remove( $templateId )
	{
		$this->logic->removeTemplate( $templateId );
		$words	= (object) $this->getWords( 'remove' );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './work/newsletter/template' );
	}

	public function removeStyle( $templateId, $index )
	{
		$this->logic->removeTemplateStyle( $templateId, $index );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	public function setContentTab( $templateId, $tabKey )
	{
		$this->session->set( 'work.newsletter.template.content.tab', $tabKey );
		$this->restart( './work/newsletter/template/edit/'.$templateId );
	}

	public function viewTheme( $themeId )
	{
		try{
			$model	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
			$this->addData( 'theme', $model->getFromId( $themeId ) );
			$this->addData( 'themePath', 'contents/themes/' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Invalid theme ID' );
			$this->restart( NULL, TRUE );
		}
	}

	protected function __onInit()
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
