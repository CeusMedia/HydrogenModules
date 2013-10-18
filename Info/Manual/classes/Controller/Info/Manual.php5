<?php
class Controller_Info_Manual extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->config	= $this->env->getConfig()->getAll( 'module.info_manual.', TRUE );
		$this->path		= $this->config->get( 'path' );
		if( !file_exists( $this->path ) )
			throw new RuntimeException( 'Path "'.$this->path.'" is not existing' );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL ){
		$messenger		= $this->env->getMessenger();

		$file	= join( "/", func_get_args() );
		if( !strlen( $file ) )
			$file	= "index";

		$index	= new File_RecursiveRegexFilter( $this->path, "/\.md$/" );
		$list	= array();
		foreach( $index as $entry ){
			$pathName	= substr( $entry->getPathname(), strlen( $this->path ) );
			$list[$pathName]	= $entry->getFilename();
		}

		$content	= "";
		if( !in_array( $file.".md", array_keys( $list ) ) ){
			if( $file !== "index" ){
				$messenger->noteNotice( 'Seite nicht gefunden. Weiterleitung zur Übersicht.' );
				$this->restart( NULL, TRUE );
			}
			else{
				$messenger->noteError( 'Die Übersichtsseite "'.$this->path.'index.md" fehlt.' );
			}
		}
		else{
			$content	= File_Reader::load( $this->path.$file.".md" );
			$content	= preg_replace( "@\[(.+)\]\((\w*)\)@U", "[\\1](info/manual/\\2)", $content );
		}
		$this->addData( 'file', $file );
		$this->addData( 'content', $content );
		$this->addData( 'path', $path );
	}
}
?>
