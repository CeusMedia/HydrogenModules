<?php

use CeusMedia\Common\FS\File\RecursiveIterator as RecursiveFileIterator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;

class Controller_Ajax_Helper_Input_Resource extends AjaxController
{
	protected array $extensions	= [
		'image'	=> ['png', 'gif', 'jpg', 'jpeg', 'jpe', 'svg'],
		'style'	=> ['css', 'scss', 'less'],
	];

	/**
	 *	@return		void
	 *	@throws		EnvironmentException
	 *	@throws		JsonException
	 */
	public function render(): void
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
			$index		= new RecursiveFileIterator( $realpath.$path );
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
				$sublist[$key]	= HtmlTag::create( 'li', [
					self::renderThumbnail( $env, $mode, $path, $relativePath ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'span', $fileName ),
						'<br/>',
						HtmlTag::create( 'small', $filePath, ['class' => 'muted'] ),
					], [
						'class' => 'source-list-label',
					] )
				], [
					'class'		=> 'source-list-item',
					'onclick'	=> 'HelperInputResource.setSourceItem(this)',
				], [
					'source-path'	=> $path.$relativePath,
 					'modal-id'		=> $modalId,
					'input-id'		=> $inputId
				] );
			}
			if( !$sublist )
				continue;
			ksort( $sublist );
			$sublist	= HtmlTag::create( 'ul', $sublist, ['class' => 'unstyled'] );
			$labelPath	= HtmlTag::create( 'div', 'Pfad: <strong>'.$path.'</strong>' );
			$list[]		= HtmlTag::create( 'li', $labelPath.$sublist, ['class' => 'source-list-path'] );
		}
		$html	= HtmlTag::create( 'div', 'Nichts gefunden.', ['class' => 'alert alert-info'] );
		if( count( $list ) )
			$html	= HtmlTag::create( 'ul', $list, ['id' => '', 'class' => 'unstyled modal-source-list'] );
		$this->respondData( ['html' => $html] );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		string			$mode
	 *	@param		string			$path
	 *	@param		string			$relativePath
	 *	@return		string
	 */
	protected static function renderThumbnail( Environment $env, string $mode, string $path, string $relativePath ): string
	{
		switch( $mode ){
			case 'image':
				$div	= HtmlTag::create( 'div', '&nbsp;', [
					'class'	=> 'source-list-image',
					'style'	=> 'background-image: url('.$env->getBaseUrl().$path.$relativePath.');',
				] );
				try{
					if( class_exists( 'View_Helper_Thumbnailer' ) ){
						$helper	= new View_Helper_Thumbnailer( $env, 36, 36 );
						$image	= $helper->get( $env->uri.$path.$relativePath );
						$image	= HtmlTag::create( 'img', NULL, ['src' => $image] );
						$div	= HtmlTag::create( 'div', $image, [
							'class'	=> 'source-list-image',
						] );
					}
				}
				catch( Exception $e ){
				}
				return $div;
			case 'style':
			default:
				return HtmlTag::create( 'div', [
					HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-2x fa-file-code-o'] )
				], ['class' => 'source-list-image'] );
		}
	}
}
