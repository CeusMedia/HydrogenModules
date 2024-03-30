<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_TinyMce_FileBrowser
{
	protected WebEnvironment $env;
	protected View_Helper_Thumbnailer $thumbnailer;
	protected View_Helper_TimePhraser $timePhraser;
	protected int $displayMode			= 0;
	protected int $sourceMode			= 0;
	protected string $cssClassPrefix	= 'list';
	protected string $baseUrl;
	protected int|string|NULL $topicId	= NULL;
	protected array $topics;
	protected string $path;

	public const DISPLAY_MODE_LIST		= 0;
	public const DISPLAY_MODE_GRID		= 1;

	public const SOURCE_MODE_IMAGE		= 0;
	public const SOURCE_MODE_LINK		= 1;

	public function __construct( WebEnvironment $env )
	{
		$this->env	= $env;
		$this->__onInit();
	}

	public function render(): string
	{
		$buffer		= new OutputBuffer();
		$helper		= new View_Helper_TinyMce( $this->env );
		try{
			if( $this->sourceMode === self::SOURCE_MODE_IMAGE )
				$content	= $this->renderImageMode();
			else
				$content	= $this->renderLinkMode();
			$listTopics		= $this->renderTopicList();
			$topbar			= $this->renderTopBar();
		}
		catch( Exception $e ){
			remark( $e->getMessage() );
			remark( 'sourceMode: '.$this->sourceMode );
			remark( 'displayMode: '.$this->displayMode );
			remark( 'topic: '.$this->topicId );
			remark( 'path: '.$this->path );
			die;
		}
		$messages		= $buffer->get( TRUE );
		$html			= HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $listTopics, ['id' => 'tinymce-file-browser-sidebar'] ),
			HtmlTag::create( 'div', $topbar, ['id' => 'tinymce-file-browser-topbar'] ),
			HtmlTag::create( 'div', $messages.$content, ['id' => 'tinymce-file-browser-content'] ),
		], ['id' => 'container-tinymce-file-browser'] );

		$page	= $this->env->getPage();
		$page->js->addScriptOnReady( 'tinymce.FileBrowser.initBrowser();' );
//		$page->js->addScriptOnReady( 'jQuery("#tinymce-file-browser-content").height("100%").css("overflow-y", "auto");' );
		$page->addBody( $html );
		print( $page->build( ['id' => 'tinymce-file-browser'] ) );
		exit;
	}

	public function setDisplayMode( int $displayMode ): self
	{
		$this->displayMode		= $displayMode;
		$this->cssClassPrefix	= $displayMode == self::DISPLAY_MODE_GRID ? 'grid' : 'list';
		return $this;
	}

	public function setSourceMode( int $sourceMode ): self
	{
		$this->sourceMode	= $sourceMode;
		return $this;
	}

	public function setTopicId( int|string $topicId ): self
	{
		$this->topicId	= $topicId;
		return $this;
	}

	public function setTopics( array $topics ): self
	{
		$this->topics	= $topics;
		return $this;
	}

	public function setPath( string $path ): self
	{
		$this->path		= $path;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->thumbnailer	= new View_Helper_Thumbnailer( $this->env, 128, 128 );
		$this->baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->timePhraser	= new View_Helper_TimePhraser( $this->env );
	}

	protected function filterFoldersByPath( array $items, string $path ): array
	{
		$list	= [];
		foreach( $items as $item ){
			$itemPath	= $item->title;
			if( strlen( $path ) ){
				if( substr( $item->title, 0, strlen( $path ) + 1 ) !== $path.'/' )
					continue;
				$itemPath	= substr( $item->title, strlen( $path ) + 1 );
			}
			$parts			= explode( "/", $itemPath );
			if( count( $parts ) < 2 )
				continue;
			if( !isset( $list[$parts[0]] ) )
				$list[$parts[0]]	= 0;
//			if( count( $parts ) === 2 )
				$list[$parts[0]]++;
		}
		return $list;
	}

	protected function filterItemsByPath( array $items, string $path ): array
	{
		$list	= [];
		foreach( $items as $item ){
			if( strlen( $path ) ){
				if( substr( $item->title, 0, strlen( $path ) + 1 ) !== $path.'/' )
					continue;
				if( substr_count( $item->title, '/' ) !== count( explode( "/", $path ) ) )
					continue;
			}
			else{
				if( preg_match( "@/@", $item->title ) )
					continue;
			}
			$list[]	= $item;
		}
		return $list;
	}

	protected function renderFolderItem( string $path, string $label, $count = NULL, string $icon = 'folder-open' ): string
	{
		$facts		= [];
		if( is_int( $count ) && $count > 0 ){
			$facts[]	= sprintf( $count > 1 ? '%d Einträge' : '%d Eintrag', $count );
		}
		$facts	= HtmlTag::create( 'small', join( '&nbsp;|&nbsp;', $facts ), ['class' => 'muted'] );

		$path		= base64_encode( $path );
		$image	= HtmlTag::create( 'i', '', ['class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon] );
		$label	= HtmlTag::create( 'div', $label.'<br/>'.$facts, [
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		] );
		$mode	= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		return HtmlTag::create( 'li', $image.$label, [
			'class' 		=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-folder trigger-folder',
			'data-url'		=> './manage/tinyMce/setPath/'.$mode.'/'.$this->topicId.'/'.$path,
			'data-label'	=> $label,
		] );
	}

	protected function renderItemException( $itemUri, $itemLabel, $exception ): string
	{
		return HtmlTag::create( 'li', $exception->getMessage(), [
			'class' 	=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-file',
			'data-url'	=> '',
		] );
	}

	protected function renderImageItem( string $path, string $filePath, $size = NULL, string $icon = 'image' ): string
	{
		$labelParts	= explode( "/", $filePath );
		$label	= $labelParts[count( $labelParts ) - 1];
//		if( is_string( $size ) )
//			$size	= HtmlTag::create( 'small', '('.$size.')', ['class' => 'muted'] );
//		$image	= HtmlTag::create( 'i', '', ['class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon] );

		$remoteFilePath = Logic_Frontend::getInstance( $this->env )->getPath().$path;
		if( preg_match( '/^file\//', $path ) ){
			$bucket = new Logic_FileBucket( $this->env );
			$file   = $bucket->getByPath( substr( $path, 5 ) );
			if( $file )
				$remoteFilePath = $bucket->getPath().$file->hash;
			else
				throw new Exception( 'No file found in bucket for: '.$path );
		}

		try{
			$image		= new Image( $remoteFilePath );
			$facts		= HtmlTag::create( 'dl', [
				sprintf( '<dt>Größe</dt><dd>%s</dd>', UnitFormater::formatBytes( filesize( $remoteFilePath ) ) ),
				sprintf( '<dt>Auflösung</dt><dd>%s&times;%s px</dd>', $image->getWidth(), $image->getHeight() ),
				sprintf( '<dt>Qualität</dt><dd class="fact-quality">%s %%</dd>', $image->getQuality() ),
				sprintf( '<dt>Typ</dt><dd class="fact-mime">%s</dd>', $image->getMimeType() ),
				sprintf( '<dt>Alter</dt><dd>%s</dd>', $this->timePhraser->convert( filectime( $remoteFilePath ), TRUE ) ),
			], ['class' => 'dl-inline'] );
		}
		catch( Exception $e ){
			$facts		= HtmlTag::create( 'dl', array(
				sprintf( '<dt>Größe</dt><dd>%s</dd>', UnitFormater::formatBytes( filesize( $remoteFilePath ) ) ),
				sprintf( '<dt>Alter</dt><dd>%s</dd>', $this->timePhraser->convert( filectime( $remoteFilePath ), TRUE ) ),
			), ['class' => 'dl-inline'] );
		}
		$facts		= HtmlTag::create( 'small', $facts, ['class' => 'muted'] );

		try{
			$data		= $this->thumbnailer->get( $remoteFilePath, 128, 128 );
			$thumbnail	= HtmlTag::create( 'div', NULL, array(
				'class'			=> $this->cssClassPrefix.'-item-icon trigger-submit',
				'style'			=> 'background-image: url('.$data.')',
				'data-url'		=> Logic_Frontend::getInstance( $this->env )->getUrl().$path,
				'data-type'		=> 'image',
				'data-label'	=> $filePath,
			) );
		}
		catch( Exception $e ){
			$thumbnail			= HtmlTag::create( 'div', NULL, array(
				'class'			=> $this->cssClassPrefix.'-item-icon trigger-submit',
				'data-url'		=> Logic_Frontend::getInstance( $this->env )->getUrl().$path,
				'data-type'		=> 'image',
				'data-label'	=> $filePath,
			) );
		}

		$label	= HtmlTag::create( 'div', $labelParts[count( $labelParts ) - 1].'<br/>'.$facts, [
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		] );
		return HtmlTag::create( 'li', $thumbnail.$label, array(
			'class' 	=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-file',
			'data-url'	=> Logic_Frontend::getInstance( $this->env )->getUri().$path,
		) );
	}

	protected function renderImageMode(): string
	{
		if( !isset( $this->topics[$this->topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

		$list		= [];
		$folders	= $this->filterFoldersByPath( $this->topics[$this->topicId]->menu, $this->path );
		$images		= $this->filterItemsByPath( $this->topics[$this->topicId]->menu, $this->path );

		if( $this->path ){
			$parts		= explode( '/', $this->path );
			$pathBack	= implode( '/', array_slice( $parts, 0, -1 ) );
			$title		= 'zurück';
			$desc		= HtmlTag::create( 'small', 'zum Überordner', ['class' => 'muted'] );
			$list[]		= $this->renderFolderItem( $pathBack, $title.'<br/>'.$desc, NULL, 'arrow-left' );
		}

		foreach( $folders as $label => $count ){
			$pathNext	= $this->path ? $this->path.'/'.$label : $label;
			$list[]		= $this->renderFolderItem( $pathNext, $label, $count );
		}

		foreach( $images as $image ){
			try{
				$list[]		= $this->renderImageItem( $image->value, $image->title );
			}
			catch( Exception $e ){
				$list[]		= $this->renderItemException( $image->value, $image->title, $e );
//				$this->env->getMessenger()->noteFailure( $e->getMessage() );
			}
		}
		$listItems		= HtmlTag::create( 'ul', $list, ['class' => $this->cssClassPrefix.' unstyled'] );
		$listItems		= HtmlTag::create( 'div', array(
//			HtmlTag::create( 'h4', '-' ),//$pathLabel ),
			HtmlTag::create( 'div', $listItems, ['id' => 'container-list-items'] )
		) );
		return $listItems;
	}

	protected function renderLinkItem( string $path, string $filePath, $size = NULL, string $icon = 'link' ): string
	{
		$fullpath	= preg_replace( '/^\.\//', '', $path );
		if( !preg_match( '/^https?:\/\//', $fullpath ) )
			$fullpath	= $this->baseUrl.$fullpath;
		$labelParts	= explode( "/", $filePath );
		$label		= $labelParts[count( $labelParts ) - 1];
		$image	= HtmlTag::create( 'i', '', ['class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon] );
		$url	= preg_replace( '/^\.\//', '', $path );
		$icon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-external-link'] );
//		$icon	= HtmlTag::create( 'a', $icon, ['href' => $fullpath, 'target' => '_blank'] );
		$url	= HtmlTag::create( 'small', $url.'&nbsp;'.$icon, ['class' => 'muted'] );
		$label	= HtmlTag::create( 'div', $label.'<br/>'.$url, [
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		] );
		return HtmlTag::create( 'li', $image.$label, [
			'class' 		=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-link trigger-submit',
			'data-url'		=> $fullpath,
			'data-path'		=> $path,
			'data-label'	=> $filePath,
			'data-type'		=> 'link',
		] );
	}

	protected function renderLinkMode(): string
	{
		if( !isset( $this->topics[$this->topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

		$list		= [];
		$folders	= $this->filterFoldersByPath( $this->topics[$this->topicId]->menu, $this->path );
		$links		= $this->filterItemsByPath( $this->topics[$this->topicId]->menu, $this->path );

		if( $this->path ){
			$parts		= explode( "/", $this->path );
			$pathBack	= implode( "/", array_slice( $parts, 0, -1 ) );
			$list[]		= $this->renderFolderItem( $pathBack, 'zurück', NULL, 'arrow-left' );
		}

		foreach( $folders as $label => $count ){
			$pathNext	= $this->path ? $this->path.'/'.$label : $label;
			$list[]		= $this->renderFolderItem( $pathNext, $label, $count );
		}

		foreach( $links as $link ){
			if( !isset( $link->type ) )
				$link->type	= 'unknown';
			if( isset( $link->type ) && $link->type === 'image' )
				$list[]		= $this->renderImageItem( $link->value, $link->title );
			else{
				$icon		= 'link';
				if( $link->type === 'link:gallery' )
					$icon	= 'camera';
				if( $link->type === 'link:bookmark' )
					$icon	= 'bookmark';
				if( $link->type === 'unknown' )
					$icon	= 'link';
				if( isset( $link->value ) )
					$list[]		= $this->renderLinkItem( $link->value, $link->title, NULL, $icon );
			}
		}

		$listItems		= HtmlTag::create( 'ul', $list, ['class' => $this->cssClassPrefix.' unstyled'] );
		$listItems		= HtmlTag::create( 'div', array(
//			HtmlTag::create( 'h4', '-' ),//$pathLabel ),
			HtmlTag::create( 'div', $listItems, ['id' => 'container-list-items'] )
		) );

		return $listItems;
	}

	protected function renderTopBar(): string
	{
		$mode		= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
		$iconGrid	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );

		$parts		= explode( '/', $this->path );
		$pathLabel	= [];
		$way		= [];
		foreach( $parts as $nr => $part ){
			if( $nr == count( $parts ) - 1 )
				break;
			$way[]	= $part;
			$pathLabel[]	= HtmlTag::create( 'a', $part, array(
				'class'		=> 'trigger-folder',
				'data-url'	=> './manage/tinyMce/setPath/image/'.$this->topicId.'/'.base64_encode( join( '/', $way ) ),
			) );
		}
		$pathLabel[]	= $part;
		$pathLabel		= join( ' / ', $pathLabel );
		$position		= '<b><small>Position:</small></b> '.$pathLabel;

		$modeLabel		= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'Bild-Quelle' : 'Link-Quelle';
		$mode			= '<b><small>Modus:</small></b> '.$modeLabel;

		return HtmlTag::create( 'div', $mode.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$position, ['class' => 'position autocut'] ).
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'a', $iconList.'&nbsp;Liste', array(
						'href'		=> './manage/tinyMce/setDisplayMode/'.$mode.'/'.self::DISPLAY_MODE_LIST,
						'class'		=> 'btn not-btn-small '.( $this->displayMode == self::DISPLAY_MODE_LIST ? 'disabled' : NULL ),
					) ),
					HtmlTag::create( 'a', $iconGrid.'&nbsp;Kacheln', array(
						'href'		=> './manage/tinyMce/setDisplayMode/'.$mode.'/'.self::DISPLAY_MODE_GRID,
						'class'		=> 'btn not-btn-small '.( $this->displayMode == self::DISPLAY_MODE_GRID ? 'disabled' : NULL ),
					) ),
				), ['class' => 'btn-group'] )
			), ['class' => 'buttons'] );
	}

	protected function renderTopicList(): string
	{
		$list	= [];
		$mode	= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		foreach( $this->topics as $topicId => $topic ){
			$count	= HtmlTag::create( 'small', '('.count( $topic->menu ).')', ['class' => 'muted'] );
			$title	= rtrim( trim( $topic->title ), ":" );
			$link	= HtmlTag::create( 'a', $title.'&nbsp;'.$count, [
				'href'	=> './manage/tinyMce/setTopic/'.$mode.'/'.$topicId,
			] );
			$list[]	= HtmlTag::create( 'li', $link, [
				'class'		=> $topicId == $this->topicId ? 'active' : NULL,
			] );
		}
		return HtmlTag::create( 'ul', $list, [
			'class'	=> 'nav nav-pills nav-stacked'
		] );
	}
}
