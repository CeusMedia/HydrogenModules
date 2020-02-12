<?php
class Controller_Helper_Input_Resource extends CMF_Hydrogen_Controller {

	public function ajaxRender(){
		$paths		= (array) $this->env->getRequest()->get( 'paths' );
		$mode		= $this->env->getRequest()->get( 'mode' );
		$modalId	= $this->env->getRequest()->get( 'modalId' );
		$inputId	= $this->env->getRequest()->get( 'inputId' );

		switch( $mode ){
			case 'image':
				$extensions	= array( "png", "gif", "jpg", "jpeg", "jpe", "svg" );
				$mimeTypes	= (array) $this->env->getRequest()->get( 'mimeTypes' );
				break;
			case 'style':
				$extensions	= array( "css", "scss", "less" );
				$mimeTypes	= (array) $this->env->getRequest()->get( 'mimeTypes' );
				break;
		}

		$env	= $this->env;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$env	= Logic_Frontend::getRemoteEnv( $this->env );
		$realpath	= realpath( $env->path ).'/';

		$list		= array();
		foreach( $paths as $path ){
			if( !file_exists( $realpath.$path ) )
				continue;
			$sublist	= array();
			$index		= new \FS_File_RecursiveIterator( $realpath.$path );
			foreach( $index as $entry ){
				if( $entry->isDir() )
					continue;
				$relativePath	= substr( $entry->getPathname(), strlen( $realpath.$path ) );
				$ext			= pathinfo( $relativePath, PATHINFO_EXTENSION );
				$filePath		= pathinfo( $relativePath, PATHINFO_DIRNAME );
				$fileName		= pathinfo( $relativePath, PATHINFO_BASENAME );
				if( !in_array( $ext, $extensions ) )
					continue;
				$key		= strtolower( $relativePath );
				$sublist[$key]	= UI_HTML_Tag::create( 'li', array(
					self::renderThumbnail( $env, $mode, $path, $relativePath ),
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'span', $fileName ),
						'<br/>',
						UI_HTML_Tag::create( 'small', $filePath, array( 'class' => 'muted' ) ),
					), array(
						'class' => 'source-list-label',
					) )
				), array(
					'class'		=> 'source-list-item',
					'onclick'	=> 'HelperInputResource.setSourceItem(this)',
 				), array(
					'source-path'	=> $path.$relativePath,
 					'modal-id'		=> $modalId,
					'input-id'		=> $inputId
				) );
			}
			if( !$sublist )
				continue;
			ksort( $sublist );
			$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'unstyled' ) );
			$labelPath	= UI_HTML_Tag::create( 'div', 'Pfad: <strong>'.$path.'</strong>' );
			$list[]		= UI_HTML_Tag::create( 'li', $labelPath.$sublist, array( 'class' => 'source-list-path' ) );
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'id' => '', 'class' => 'unstyled modal-source-list' ) );
		print $list;
		exit;
	}

	static protected function renderThumbnail( CMF_Hydrogen_Environment $env, $mode, $path, $relativePath ){
		switch( $mode ){
			case 'image':
				$div	= UI_HTML_Tag::create( 'div', '&nbsp;', array(
					'class'	=> 'source-list-image',
					'style'	=> 'background-image: url('.$env->getBaseUrl().$path.$relativePath.');',
				) );
				try{
					if( class_exists( 'View_Helper_Thumbnailer' ) ){
						$helper	= new View_Helper_Thumbnailer( $env, 36, 36 );
						$image	= $helper->get( $env->uri.$path.$relativePath );
						$image	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $image ) );
						$div	= UI_HTML_Tag::create( 'div', $image, array(
							'class'	=> 'source-list-image',
						) );
					}
				}
				catch( Exception $e ){
				}
				return $div;
			case 'style':
			default:
				return UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-2x fa-file-code-o' ) )
				), array(
					'class'	=> 'source-list-image',
				) );
		}
	}
}
