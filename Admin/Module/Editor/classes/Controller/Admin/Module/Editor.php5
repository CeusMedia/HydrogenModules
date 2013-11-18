<?php
class Controller_Admin_Module_Editor extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Admin_Module after cleanup

	/** @var	CMF_Hydrogen_Environment_Resource_Module_Editor	$editor		Module XML editor instance */
	protected $editor;
	/** @var	View_Helper_Module								$helper		Modile View helper instance */
	protected $helper;
	/**	@var	Logic_Module									$logic		Module logic instance */
	protected $logic;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	Messenger Object */
	protected $messenger;
	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
	protected $request;

	protected function __onInit(){
		$this->env->clock->profiler->tick( 'Controller_Admin_Module_Editor::init: start' );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
		$this->editor		= new CMF_Hydrogen_Environment_Resource_Module_Editor( $this->env->getRemote() );
		$this->helper		= new View_Helper_Module( $this->env );

		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.editor.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
		if( !$this->env->getSession()->get( 'instanceId' ) ){
			$words	= $this->getWords( 'msg' );
			$this->messenger->noteError( $words['noInstanceSelected'] );
			$this->restart( 'admin/module/viewer' );
		}
		$this->modules	= (object) array(
			'all'		=> $this->logic->model->getAll(),
			'missing'	=> $this->logic->listModulesMissing( 1 ),
			'possible'	=> $this->logic->listModulesPossible( 1 ),
		);

		if( 0 && $this->modules->missing ){
			$list	= array();
			$first	= NULL;
			foreach( $this->modules->missing as $moduleId => $relations ){
				$first	= $first ? $first : $moduleId;
				$label	= $title	= $moduleId;
				foreach( $this->modules->all as $module ){
					if( $module->id == $moduleId ){
						$descLines	= explode( "\n", $module->description );
						$descFirst	= addslashes( trim( array_shift( $descLines ) ) );
						$title		= $module->title;
						$label		= $descFirst ? '<acronym title="'.$descFirst.'">'.$title.'</acronym>' : $title;
					}
				}
				$url		= './admin/module/viewer/'.$moduleId;
				$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) );
				$span		= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'icon module module-status-4' ) );
				$list[]		= UI_HTML_Tag::create( 'li', $span, array() );
			}
			$list	= UI_HTML_Tag::create( 'ul', $list );
			$this->messenger->noteError( 'Folgende Module müssen installiert werden:<br/>'.$list );
			$this->restart( 'admin/module/viewer/index/'.$first );
		}
	}

	public function addAuthor( $moduleId ){
		$data	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( !strlen( trim( $data->get( 'name' ) ) ) )												//  no name provided
			$this->env->getMessenger()->noteError( 'Der Name des Autors fehlt.' );
		else{
			$this->editor->addAuthor( $moduleId, $data->get( 'name' ), $data->get( 'email' ) );
			$this->env->getMessenger()->noteSuccess( 'Author added.' );								//  show success message
		}
		$this->restart( './admin/module/editor/view/'.$moduleId.'?tab=general' );
	}

	public function addCompany( $moduleId ){
		$data	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( !strlen( trim( $data->get( 'name' ) ) ) )												//  no name provided
			$this->env->getMessenger()->noteError( 'Der Name des Unternehmens fehlt.' );
		else{
			$this->editor->addCompany( $moduleId, $data->get( 'name' ), $data->get( 'site' ) );
			$this->env->getMessenger()->noteSuccess( 'Company added.' );							//  show success message
		}
		$this->restart( './admin/module/editor/view/'.$moduleId.'?tab=general' );
	}

	public function addConfig( $moduleId ){
		$data	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $data->get( 'type' ) === "boolean" )
			$data->set( 'value', trim( $data->get( 'value_boolean' ) ) );
		if( !strlen( trim( $data->get( 'name' ) ) ) )												//  no name provided
			$this->env->getMessenger()->noteError( 'Kein Name angegeben.' );
		else{
			$this->editor->addConfig($moduleId,
				$data->get( 'name' ),
				$data->get( 'type' ),
				$data->get( 'value' ),
				$data->get( 'values' ),
				$data->get( 'mandatory' ),
				$data->get( 'protected' )
			);
			$this->env->getMessenger()->noteSuccess( 'Config added.' );								//  show success message
		}
		$this->restart( './admin/module/editor/view/'.$moduleId.'?tab=config' );
	}

	/**
	 *	Adds a file to module XML.
	 *	Uses POST values: type, resource, source?, load?
	 *	@access		public
	 *	@param		string		$moduleId		Module ID
	 */
	public function addFile( $moduleId ){
		$data		= $this->env->getRequest()->getAllFromSource( 'post' );
		$type		= $data->get( 'type' );
		$resource	= $data->get( 'resource' );
		$source		= $data->get( 'source_'.$type );
		$load		= $data->get( 'load' );
		
		if( !strlen( trim( $data->get( 'type' ) ) ) )												//  no type provided
			$this->env->getMessenger()->noteError( 'Kein Dateityp angegeben.' );
		else if( !strlen( trim( $data->get( 'resource' ) ) ) )										//  no resource provided
			$this->env->getMessenger()->noteError( 'Keine Datei angegeben.' );
		else{
			$this->editor->addFile( $moduleId, $type, $resource, $source, $load );
			$this->env->getMessenger()->noteSuccess( 'Resource added.' );							//  show success message
		}
		$this->restart( $moduleId.'?tab=resources', TRUE );											//  restart view of resources
	}

	public function addLink( $moduleId ){
		$request	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( !trim( $request->get( 'path' ) ) )														//  no path provided
			$this->env->getMessenger()->noteError( 'Kein Link-Pfad angegeben.' );
		else{
			$this->editor->addLink(
				$moduleId,
				$request->get( 'path' ),
				$request->get( 'label' ),
				$request->get( 'access' ),
				$request->get( 'language' ),
				$request->get( 'rank' )
			);
			$this->env->getMessenger()->noteSuccess( 'Link added.' );								//  show success message
		}
		$this->restart( './admin/module/editor/view/'.$moduleId.'?tab=links' );
	}

	public function addRelation( $moduleId ){
		$data		= $this->env->getRequest()->getAllFromSource( 'post' );
		$this->editor->addRelation( $moduleId, $data->get( 'type' ), $data->get( 'module' ) );
		$this->restart( $moduleId.'?tab=relations', TRUE );											//  restart view of relations
	}

	public function addSql( $moduleId ){
		$request	= $this->env->getRequest();
		$event		= $request->get( 'event' );
		$type		= $request->get( 'type' );
		$ddl		= $request->get( 'ddl' );
		if( trim( $ddl ) ){
			$this->editor->addSql( $moduleId, $ddl, $event, $type );
			$this->env->getMessenger()->noteSuccess( 'SQL added.' );								//  show success message
		}
		$this->restart( $moduleId.'?tab=database', TRUE );											//  restart view of database
	}
	
	public function edit( $moduleId ){
		$request	= $this->env->getRequest();
		$module		= $this->logic->model->getLocalModuleXml( $moduleId, TRUE );					//  load module XML
		
		$title			= $request->get( 'edit_title' );
		$version		= $request->get( 'edit_version' );
		$description	= $request->get( 'edit_description' );
#		$title			= $request->get( 'title' );
		
		if( $title )
			if( $module->title != $title )
				$module->title	= $title;

		if( $version )
			if( $module->version != $version )
				$module->version	= $version;

		$this->logic->model->setLocalModuleXml( $moduleId, $module );								//  save modified module XML
		$this->env->getMessenger()->noteSuccess( 'Module saved.' );									//  show success message
		$this->restart( $moduleId, TRUE );															//  restart view of resources
	}

	public function editConfig( $moduleId ){
		$request	= $this->env->getRequest();
		$pairs		= $request->get( 'config' );
		$this->logic->configureLocalModule( $moduleId, $pairs );
		$this->logic->invalidateFileCache( $this->env->getRemote() );
		$this->messenger->noteSuccess( 'Saved.' );
		$this->restart( './admin/module/editor/view/'.$moduleId.'?tab=config' );
	}

	public function editLink( $moduleId, $number ){
		$request	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( !trim( $request->get( 'path' ) ) )														//  no path provided
			$this->env->getMessenger()->noteError( 'Kein Link-Pfad angegeben.' );
		else{
			try{
				$this->editor->editLink(
					$moduleId,
					$number,
					$request->get( 'path' ),
					$request->get( 'link' ),
					$request->get( 'label' ),
					$request->get( 'access' ),
					$request->get( 'language' ),
					$request->get( 'rank' )
				);
				$this->env->getMessenger()->noteSuccess( 'Link saved.' );							//  show success message
			}
			catch( OutOfRangeException $e ){
				$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
			}
		}
		$this->restart( 'view/'.$moduleId.'?tab=links', TRUE );
	}
	
	public function commit( $moduleId, $move = NULL ){
		$request	= $this->env->getRequest()->getAllFromSource( 'post' );
		$this->logic->importLocalModuleToRepository( $moduleId, $request->get( 'source' ), $move );
		$this->restart( $moduleId, TRUE );
	}
	
	public function export( $moduleId, $move = NULL ){
		$this->logic->importLocalModuleToRepository( $moduleId, $move );
		$this->restart( $moduleId, TRUE );
	}

	public function index( $moduleId = NULL ){
		if( $moduleId )
			return $this->redirect( 'admin/module/editor', 'view', array( $moduleId ) );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function remove( $moduleId, $verbose = NULL ){
		$words		= (object) $this->getWords( 'msg' );
		$module		= $this->logic->getModule( $moduleId );
		if( !$module )
			$this->restart( './admin/module/editor' );
		if( $this->logic->uninstallModule( $moduleId, $verbose ) ){
			$this->messenger->noteSuccess( $words->moduleUninstalled, $module->title );
			$this->restart( './admin/module/editor' );
		}
		else
			$this->messenger->noteError( $words->moduleNotUninstalled, $module->title );
		$this->restart( './admin/module/editor/view/'.$moduleId );
	}

	public function removeAuthor( $moduleId, $name ){
		$name	= base64_decode( $name );
		try{
			$this->editor->removeAuthor( $moduleId, $name );
#			$this->env->getMessenger()->noteSuccess( 'Company removed.' );							//  show success message
		}
		catch( OutOfRangeException $e ){
#			$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
		}
		$this->restart( 'view/'.$moduleId.'?tab=general', TRUE );
	}

	public function removeCompany( $moduleId, $name ){
		$name	= base64_decode( $name );
		try{
			$this->editor->removeCompany( $moduleId, $name );
#			$this->env->getMessenger()->noteSuccess( 'Company removed.' );							//  show success message
		}
		catch( OutOfRangeException $e ){
#			$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
		}
		$this->restart( 'view/'.$moduleId.'?tab=general', TRUE );
	}
	
	public function removeConfig( $moduleId, $name ){
		try{
			$this->editor->removeConfig( $moduleId, $name );
#			$this->env->getMessenger()->noteSuccess( 'Config removed.' );								//  show success message
		}
		catch( OutOfRangeException $e ){
#			$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
		}
		$this->restart( 'view/'.$moduleId.'?tab=config', TRUE );
	}

	public function removeFile( $moduleId, $type, $resource ){
		try{
			$this->editor->removeFile( $moduleId, $type, base64_decode( $resource ) );
#			$this->env->getMessenger()->noteSuccess( 'Link removed.' );								//  show success message
		}
		catch( OutOfRangeException $e ){
#			$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
		}
		$this->restart( 'view/'.$moduleId.'?tab=resources', TRUE );
	}

	public function removeIcon( $moduleId ){
	}
	
	public function removeLink( $moduleId, $number ){
		try{
			$this->editor->removeLink( $moduleId, $number );
			$this->env->getMessenger()->noteSuccess( 'Link removed.' );								//  show success message
		}
		catch( OutOfRangeException $e ){
			$this->env->getMessenger()->noteFailure( 'Der gewählte Link existiert nicht.' );
		}
		$this->restart( 'view/'.$moduleId.'?tab=relations', TRUE );
	}

	public function removeRelation( $moduleId, $type, $relatedModuleId ){
		$this->editor->removeRelation( $moduleId, $type, $relatedModuleId );
		$this->restart( 'view/'.$moduleId.'?tab=relations', TRUE );
	}

	public function saveXml( $moduleId ){
		$request	= $this->env->getRequest();
		$this->logic->model->setLocalModuleXml( $moduleId, $request->get( 'content' ) );
		$this->messenger->noteSuccess( 'Module XML saved.' );
		$this->restart( 'view/'.$moduleId.'?tab=xml', TRUE );
	}

	public function uploadIcon( $moduleId ){
		$request	= $this->env->getRequest();
		$image		= $request->get( 'image' );
		if( !empty( $image['error'] ) ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$message	= $handler->getErrorMessage( $image['error'] );
			$this->messenger->noteError( 'Upload gescheitert: '.$message );
		}
		else if( $image['type'] !== 'image/png' ){
			$this->messenger->noteError( 'Es werden nur PNG-Bilddateien unterstützt.' );
		}
		else{
			$img		= new UI_Image( $image['tmp_name'] );
			if( $img->getWidth() > 128 )
				$this->messenger->noteError( 'Das Bild ist zu breit <small><em>(max. 128 Pixel)</em></small>.' );
			if( $img->getHeight() > 128 )
				$this->messenger->noteError( 'Das Bild ist zu hoch <small><em>(max. 128 Pixel)</em></small>.' );
			if( $img->getWidth() < 16 )
				$this->messenger->noteError( 'Das Bild ist zu schmal <small><em>(mind. 16 Pixel)</em></small>.' );
			if( $img->getHeight() < 16 )
				$this->messenger->noteError( 'Das Bild ist zu niedrig <small><em>(mind. 16 Pixel)</em></small>.' );
		}
		if( !$this->messenger->gotError() ){
			$pathConfig	= 'config/modules/';
			if( $this->env->getRemote()->getConfig()->get( 'path.module.config' ) )
				$pathConfig	= $this->env->getRemote()->getConfig()->get( 'path.module.config' );
			$path		= $this->env->pathApp.$pathConfig;
			
			$target		= $path.$moduleId.'.png';
			move_uploaded_file( $image['tmp_name'], $target );
			$this->messenger->noteSuccess( 'Das Bild wurde hochgeladen.' );
		}
		$this->restart( './admin/module/editor/'.$moduleId );
	}
	
	public function view( $moduleId ){
		if( !$moduleId ){
			$this->messenger->noteError( "No module selected. Redirecting to list" );
			$this->restart( NULL, TRUE );
		}
		$module		= $this->logic->getModule( $moduleId );
		if( !$module ){
			$this->messenger->noteError( "Module not existing. Redirecting to list" );
			$this->restart( NULL, TRUE );
		}

		if( !$this->logic->model->isInstalled( $moduleId ) ){
			$this->messenger->noteError( "Module not installed. Redirecting to list" );
			$this->restart( NULL, TRUE );
		}

//		$this->messenger->noteNotice( "Modul in Bearbeitung: ".$this->helper->renderModuleLink( $moduleId, 1 ) );
		
		try{
//			$module->neededModules		= $this->logic->model->getAllNeededModules( $moduleId );
//			$module->supportedModules	= $this->logic->model->getAllSupportedModules( $moduleId );

			$missings = array_keys( $this->logic->model->getAllNeededModules( $moduleId, TRUE ) );
			if( 0 && $missings ){
				$list	= array();
				foreach( $missings as $missing ){
					$missingModule	= $this->logic->getModule( $missing );
					$status			= 4;
					if( !$missingModule ){
						$missingModule			= new stdClass();
						$missingModule->id		= $missing;
						$missingModule->title	= $missing;
						$status					= 0;
					}
					$url		= './admin/module/viewer/'.$missingModule->id;
					$link		= UI_HTML_Tag::create( 'a', $missingModule->title, array( 'href' => $url ) );
					$span		= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'icon module module-status-'.$status ) );
					$list[]		= UI_HTML_Tag::create( 'li', $span, array() );
				}
				$list			= UI_HTML_Tag::create( 'ul', join( $list ) );
				$msg			= 'Das Modul "%1$s" ist unvollständig installiert. Es fehlen folgende Module:<br/>%2$s';
				$this->messenger->noteError( $msg, $module->title, $list );
				$this->restart( './admin/module/installer/'.$missingModule->id.'/'.$module->id );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Problem bei den Abhängigkeiten: '.$e->getMessage() );
		}

		$this->addData( 'pathApp', $this->env->pathApp );
		$this->addData( 'configApp', $this->env->getRemote()->getConfig() );						//  assign config object of remote application
		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modules', $modules = $this->logic->model->getAll() );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'xml', $this->logic->model->getLocalModuleXml( $moduleId ) );
		$this->addData( 'linkNr', $this->env->getRequest()->get( 'linkNr' ) );

		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );

	}

	public function viewCode( $moduleId, $type, $fileName ){
		$fileName	= base64_decode( $fileName );
		$helper		= new View_Helper_ModuleCodeViewer( $this->env, $this->logic );
		try{
			print( $helper->render( $moduleId, $type, $fileName ) );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
		}
		exit;
	}
}
?>
