<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ace extends AjaxController{

	public function save(){
		$request		= $this->env->getRequest();
		$language		= $this->env->getLanguage()->getLanguage();
		$pathLocales	= $this->env->getConfig()->get( 'path.locales' );
		$filePath		= $pathLocales.$language.'/html/index/content.html';
		try{
			$result		= \FS_File_Editor::save( $filePath, $request->get( 'content' ) );
			$this->respondData( $result );
		}
		catch( Exception $e ){
			$this->respondError( $e->getCode(), $e->getMessage(), 404 );
		}
	}
}
?>
