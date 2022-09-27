<?php

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Module extends Controller{

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
		$this->categories	= $this->logic->getCategories();
#		$this->envApp		= $this->env->getRemote();
#		print_m( $this->envApp );
#		die;
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
/*		if( !$this->env->getSession()->get( 'instanceId' ) ){
			$words	= $this->getWords( 'msg' );
			$this->messenger->noteError( $words['noInstanceSelected'] );
			$this->restart( 'viewer', TRUE );
		}*/
	}

	public function filter(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $request->has( 'reset' ) ){
			$session->remove( 'filter-modules-types' );
			$session->remove( 'filter-modules-query' );
			$session->remove( 'filter-modules-categories' );
			$session->remove( 'filter-modules-sources' );
		}
		if( $request->get( 'filter' ) ){
			$session->set( 'filter-modules-types', $request->get( 'filter_types' ) );
			$session->set( 'filter-modules-query', $request->get( 'filter_query' ) );
			$session->set( 'filter-modules-categories', $request->get( 'filter_category' ) );
			$session->set( 'filter-modules-sources', $request->get( 'filter_source' ) );
		}
		$this->restart( '', TRUE );
	}

	public function ajaxEditConfig( $moduleId, $key, $value ){
		$fileName	= $this->env->pathConfig.'modules/'.$moduleId.'.xml';
		$module		= XML_ElementReader::readFile( $fileName );
		foreach( $module->config as $pair )
			if( $pair->getAttribute( 'name' ) == $key )
				$pair->{0}	= $value;
		return FileEditor::save( $fileName, $module->asXML() );
	}

	public function ajaxAddConfig( $moduleId, $key, $value ){

	}

	public function index( $page = 0 ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$limit		= 15;
		$offset		= $page * $limit;
		$filters	= array(
			'types'			=> $session->get( 'filter-modules-types' ),
			'query'			=> $session->get( 'filter-modules-query' ),
			'categories'	=> $session->get( 'filter-modules-categories' ),
			'sources'		=> $session->get( 'filter-modules-sources' ),
		);

		$modelSource	= new Model_ModuleSource( $this->env );

		$this->addData( 'modules', $this->logic->model->getAll( $filters, $limit, $offset ) );
		$this->addData( 'modulesTotal', $this->logic->model->countAll( $filters ) );
		$this->addData( 'categories', $this->categories );
		$this->addData( 'sources', $modelSource->getAll() );
		$this->addData( 'filters', $filters );
		$this->addData( 'limit', $limit );
		$this->addData( 'offset', $offset );
		$this->addData( 'page', $page );
/*		$this->addData( 'modulesAvailable', $this->logic->model->getAvailable() );
		$this->addData( 'modulesInstalled', $this->logic->model->getInstalled() );
		$this->addData( 'modulesNotInstalled', $this->logic->model->getNotInstalled() );
*/	}

	public function showRelationGraph( $moduleId, /*$instanceId,*/ $direction = 'out', $type = 'needs', $recursive = FALSE ){
		$solver	= new Logic_Module_Relation( $this->logic );														//	calculator for module installation order
		if( $direction == "in" )
			$this->addData( 'graph', $solver->renderRelatingGraph( $moduleId, $type, $recursive ) );												//  load module and related modules
		else
			$this->addData( 'graph', $solver->renderGraph( $moduleId, $type, $recursive ) );												//  load module and related modules
	}

	public function viewCode( $moduleId, $type, $fileName ){
		$pathModule	= $this->logic->getModulePath( $moduleId );
		$pathFile	= '';
		$xmpClass	= '';
		switch( $type ){
			case 'class':
				$pathFile	= 'classes/';
				$xmpClass	= 'php';
				break;
			case 'locale':
				$pathFile	= 'locales/';
				$xmpClass	= 'ini';
				break;
			case 'script':
				$pathFile	= 'js/';
				$xmpClass	= 'js';
				break;
			case 'style':
				$pathFile	= 'css/';
				$xmpClass	= 'css';
				break;
			case 'template':
				$pathFile	= 'templates/';
				$xmpClass	= 'php';
				break;
		}
		if( !file_exists( $pathModule.$pathFile.$fileName ) )
			die( 'Invalid file: '.$pathModule.$pathFile.$fileName );
		$content	= FileReader::load( $pathModule.$pathFile.$fileName );
		$code		= HtmlTag::create( 'xmp', $content, array( 'class' => 'code '.$xmpClass ) );
		$body		= '<h2>'.$moduleId.' - '.$fileName.'</h2>'.$code;
		$page		= new HtmlPage();
		$page->addStylesheet( 'css/reset.css' );
		$page->addStylesheet( 'css/typography.css' );
		$page->addStylesheet( 'css/xmp.formats.css' );
		$page->addBody( $body );
		print( $page->build( array( 'style' => 'margin: 1em' ) ) );
		exit;
	}
}
?>
