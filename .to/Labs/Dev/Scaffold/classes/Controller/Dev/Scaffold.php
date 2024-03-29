<?php

use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Dev_Scaffold extends Controller{

	protected $pathTemplates	= './templates/dev/scaffold/templates/';

	public function controller(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$classKey	= $request->get( 'class_key' );
		$classKey	= preg_replace("/[^a-z0-9_]+/i", '', $classKey );
		$this->addData( 'classKey', $classKey );

		if( $request->get( 'create' ) || $request->get( 'preview' ) ){
			if( !trim( $classKey ) )
				$messenger->noteError( 'Class key is missing' );
			else{
				$template	= $this->pathTemplates.'controller.tmpl';
				$code		= UI_Template::render( $template, ['classKey'	=> $classKey] );
				$this->addData( 'code', $code );

				if( $request->get( 'create' ) ){
					$parts		= explode( '_', $classKey );
					$className	= array_pop( $parts );
					$classPath	= 'classes/Controller/'.join( '/', $parts ).( $parts ? '/' : '' );
					$this->saveFile( $classPath.$className.'.php5', $code );
					$messenger->noteSuccess( 'Controller class "'.$classKey.'" created.' );
					$this->restart( './scafold' );
				}
			}
		}
	}

	public function logic(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$classKey	= $request->get( 'class_key' );
		$classKey	= preg_replace("/[^a-z0-9_]+/i", '', $classKey );
		$this->addData( 'classKey', $classKey );

		if( $request->get( 'create' ) || $request->get( 'preview' ) ){
			if( !trim( $classKey ) )
				$messenger->noteError( 'Class key is missing' );
			else{
				$template	= $this->pathTemplates.'logic.tmpl';
				$code		= UI_Template::render( $template, ['className'	=> $classKey] );
				$this->addData( 'code', $code );

				if( $request->get( 'create' ) ){
					$parts		= explode( '_', $classKey );
					$className	= array_pop( $parts );
					$classPath	= 'classes/Logic/'.join( '/', $parts ).( $parts ? '/' : '' );
					$this->saveFile( $classPath.$className.'.php5', $code );
					$messenger->noteSuccess( 'Logic class "'.$classKey.'" created.' );
					$this->restart( './scafold' );
				}
			}
		}
	}

	public function model(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$classKey	= $request->get( 'class_key' );
		$classKey	= preg_replace("/[^a-z0-9_]+/i", '', $classKey );
		$this->addData( 'classKey', $classKey );

		if( $request->get( 'create' ) || $request->get( 'preview' ) ){
			if( !trim( $classKey ) )
				$messenger->noteError( 'Class key is missing' );
			else{
				$template	= $this->pathTemplates.'model.tmpl';
				$code		= UI_Template::render( $template, ['className'	=> $classKey] );
				$this->addData( 'code', $code );

				if( $request->get( 'create' ) ){
					$parts		= explode( '_', $classKey );
					$className	= array_pop( $parts );
					$classPath	= 'classes/Model/'.join( '/', $parts ).( $parts ? '/' : '' );
					$this->saveFile( $classPath.$className.'.php5', $code );
					$messenger->noteSuccess( 'Model class "'.$classKey.'" created.' );
					$this->restart( './scafold' );
				}
			}
		}
	}

	public function view(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$classKey	= $request->get( 'class_key' );
		$classKey	= preg_replace("/[^a-z0-9_]+/i", '', $classKey );
		$this->addData( 'classKey', $classKey );

		if( $request->get( 'create' ) || $request->get( 'preview' ) ){
			if( !trim( $classKey ) )
				$messenger->noteError( 'Class key is missing' );
			else{
				$template	= $this->pathTemplates.'view.tmpl';
				$code		= UI_Template::render( $template, ['classKey'	=> $classKey] );
				$this->addData( 'code', $code );

				if( $request->get( 'create' ) ){
					$parts		= explode( '_', $classKey );
					$className	= array_pop( $parts );
					$classPath	= 'classes/View/'.join( '/', $parts ).( $parts ? '/' : '' );
					$this->saveFile( $classPath.$className.'.php5', $code );
					$messenger->noteSuccess( 'View class "'.$classKey.'" created.' );
					$this->restart( './scafold' );
				}
			}
		}
	}

	public function template(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$fileKey	= $request->get( 'file_key' );
		$fileKey	= preg_replace("/[^a-z0-9\/\.]+/i", '', strtolower( $fileKey ) );
		$this->addData( 'fileKey', $fileKey );

		if( $request->get( 'create' ) || $request->get( 'preview' ) ){
			if( !trim( $fileKey ) )
				$messenger->noteError( 'File key is missing' );
			else{
				$template	= $this->pathTemplates.'template.empty.tmpl';
				$code		= UI_Template::render( $template, ['classKey'	=> $classKey] );
				$this->addData( 'code', $code );

				if( $request->get( 'create' ) ){
					$this->saveFile( 'templates/'.$fileKey.'.php', $code );
					$messenger->noteSuccess( 'Template file "'.$fileKey.'" created.' );
					$this->restart( './scafold' );
				}
			}
		}
	}

	public function index(){
	}

	protected function saveFile( $filePath, $content, $mode = 0777 ){
		FolderEditor::createFolder( dirname( $filePath ), $mode );
		$e	= new FileEditor( $filePath );
		$e->writeString( $content );
		$e->setPermissions( 0777 );
	}
}
?>
