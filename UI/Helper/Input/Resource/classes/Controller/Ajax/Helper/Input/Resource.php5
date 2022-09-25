<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Ajax_Helper_Input_Resource extends AjaxController
{
	protected $extensions	= array(
		'image'	=> array( 'png', 'gif', 'jpg', 'jpeg', 'jpe', 'svg' ),
		'style'	=> array( 'css', 'scss', 'less' ),
	);

	public function render()
	{
		$paths		= (array) $this->env->getRequest()->get( 'paths' );
		$mode		= $this->env->getRequest()->get( 'mode' );
		$modalId	= $this->env->getRequest()->get( 'modalId' );
		$inputId	= $this->env->getRequest()->get( 'inputId' );

		if( !$paths )
			throw new RuntimeException( 'No paths given' );
		if( !$mode )
			throw new RuntimeException( 'No mode given' );

		$extensions	= $this->extensions[$mode];
		$mimeTypes	= (array) $this->env->getRequest()->get( 'mimeTypes' );

		$env		= $this->env;
		$path		= $this->env->uri;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$env	= Logic_Frontend::getRemoteEnv( $this->env );
			$path	= $env->path;
		}
		$realpath	= realpath( $path ).'/';

		$list		= [];
		foreach( $paths as $path ){
			if( !file_exists( $realpath.$path ) )
				continue;
			$sublist	= [];
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
				$sublist[$key]	= HtmlTag::create( 'li', array(
					self::renderThumbnail( $env, $mode, $path, $relativePath ),
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'span', $fileName ),
						'<br/>',
						HtmlTag::create( 'small', $filePath, array( 'class' => 'muted' ) ),
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
			$sublist	= HtmlTag::create( 'ul', $sublist, array( 'class' => 'unstyled' ) );
			$labelPath	= HtmlTag::create( 'div', 'Pfad: <strong>'.$path.'</strong>' );
			$list[]		= HtmlTag::create( 'li', $labelPath.$sublist, array( 'class' => 'source-list-path' ) );
		}
		$html	= HtmlTag::create( 'div', 'Nichts gefunden.', array( 'class' => 'alert alert-info' ) );
		if( count( $list ) )
			$html	= HtmlTag::create( 'ul', $list, array( 'id' => '', 'class' => 'unstyled modal-source-list' ) );
		$this->respondData( array( 'html' => $html ) );
	}

	protected static function renderThumbnail( Environment $env, string $mode, string $path, string $relativePath ): string
	{
		switch( $mode ){
			case 'image':
				$div	= HtmlTag::create( 'div', '&nbsp;', array(
					'class'	=> 'source-list-image',
					'style'	=> 'background-image: url('.$env->getBaseUrl().$path.$relativePath.');',
				) );
				try{
					if( class_exists( 'View_Helper_Thumbnailer' ) ){
						$helper	= new View_Helper_Thumbnailer( $env, 36, 36 );
						$image	= $helper->get( $env->uri.$path.$relativePath );
						$image	= HtmlTag::create( 'img', NULL, array( 'src' => $image ) );
						$div	= HtmlTag::create( 'div', $image, array(
							'class'	=> 'source-list-image',
						) );
					}
				}
				catch( Exception $e ){
				}
				return $div;
			case 'style':
			default:
				return HtmlTag::create( 'div', array(
					HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-2x fa-file-code-o' ) )
				), array(
					'class'	=> 'source-list-image',
				) );
		}
	}
}
