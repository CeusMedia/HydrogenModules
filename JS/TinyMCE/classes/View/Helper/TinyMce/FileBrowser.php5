<?php
class View_Helper_TinyMce_FileBrowser{

	protected $thumbnailer;
	protected $displayMode			= 0;
	protected $sourceMode			= 0;
	protected $cssClassPrefix		= 'list';

	const DISPLAY_MODE_GRID			= 0;
	const DISPLAY_MODE_LIST			= 1;

	const SOURCE_MODE_IMAGE			= 0;
	const SOURCE_MODE_LINK			= 1;

	public function __construct( $env ){
		$this->env	= $env;
		$this->__onInit();
	}

	public function __onInit(){
		$this->thumbnailer	= new View_Helper_Thumbnailer( $this->env, 128, 128 );
		$this->baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->timePhraser	= new View_Helper_TimePhraser( $this->env );
	}

	protected function filterFoldersByPath( $items, $path ){
		$list	= array();
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

	protected function filterItemsByPath( $items, $path ){
		$list	= array();
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

	public function render(){
		$buffer		= new UI_OutputBuffer();
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
		$html			= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', $listTopics, array( 'id' => 'tinymce-file-browser-sidebar' ) ),
			UI_HTML_Tag::create( 'div', $topbar, array( 'id' => 'tinymce-file-browser-topbar' ) ),
			UI_HTML_Tag::create( 'div', $messages.$content, array( 'id' => 'tinymce-file-browser-content' ) ),
		), array( 'id' => 'container-tinymce-file-browser' ) );

		$page	= $this->env->getPage();
		$page->js->addScriptOnReady( 'tinymce.FileBrowser.initBrowser();' );
//		$page->js->addScriptOnReady( 'jQuery("#tinymce-file-browser-content").height("100%").css("overflow-y", "auto");' );
		$page->addBody( $html );
		print( $page->build( array( 'id' => 'tinymce-file-browser' ) ) );
		exit;
	}

	protected function renderFolderItem( $path, $label, $count = NULL, $icon = 'folder-open' ){
		$path	= base64_encode( $path );
		if( is_int( $count ) )
			$count	= UI_HTML_Tag::create( 'small', '('.$count.')', array( 'class' => 'muted' ) );
		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon ) );
		$label	= UI_HTML_Tag::create( 'div', $label.'&nbsp;'.$count, array(
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		) );
		$mode	= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		return UI_HTML_Tag::create( 'li', $image.$label, array(
			'class' 	=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-folder trigger-folder',
			'data-url'	=> './manage/tinyMce/setPath/'.$mode.'/'.$this->topicId.'/'.$path,
		) );
	}

	protected function renderImageItem( $path, $filePath, $size = NULL, $icon = 'image' ){
		$parts	= explode( "/", $filePath );
		$label	= array_pop( $parts );
//		if( is_string( $size ) )
//			$size	= UI_HTML_Tag::create( 'small', '('.$size.')', array( 'class' => 'muted' ) );
//		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon ) );

		$remoteFilePath = Logic_Frontend::getInstance( $this->env )->getPath().$path;
		if( preg_match( '/^file\//', $path ) ){
			$bucket = new Logic_FileBucket( $this->env );
			$file   = $bucket->getByPath( substr( $path, 5 ) );
			if( $file )
				$remoteFilePath = $bucket->getPath().$file->hash;
			else
				throw new Exception( 'No file found in bucket for: '.$path );
		}

		$image		= new UI_Image( $remoteFilePath );
		$facts		= join( '&nbsp;|&nbsp;', array(
			sprintf( 'Größe: %s', Alg_UnitFormater::formatBytes( filesize( $remoteFilePath ) ) ),
			sprintf( 'Auflösung: %s &times; %s Pixel', $image->getWidth(), $image->getHeight() ),
			sprintf( 'Qualität: %s %%', $image->getQuality() ),
			sprintf( 'Typ: %s', $image->getMimeType() ),
			sprintf( 'Alter: '.$this->timePhraser->convert( filectime( $remoteFilePath ), TRUE ) ),
		) );
		$facts		= UI_HTML_Tag::create( 'small', $facts, array( 'class' => 'muted' ) );

		$data		= $this->thumbnailer->get( $remoteFilePath, 128, 128 );
		$thumbnail	= UI_HTML_Tag::create( 'div', NULL, array(
			'class'		=> $this->cssClassPrefix.'-item-icon trigger-submit',
			'style'		=> 'background-image: url('.$data.')',
			'data-url'	=> Logic_Frontend::getInstance( $this->env )->getUri().$path,
			'data-type'	=> 'image',
		) );
		$label	= UI_HTML_Tag::create( 'div', $filePath.'<br/>'.$facts, array(
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		) );
		return UI_HTML_Tag::create( 'li', $thumbnail.$label, array(
			'class' 	=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-file',
			'data-url'	=> '',
		) );
	}

	protected function renderImageMode(){
		if( !isset( $this->topics[$this->topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

		$list		= array();
		$folders	= $this->filterFoldersByPath( $this->topics[$this->topicId]->menu, $this->path );
		$images		= $this->filterItemsByPath( $this->topics[$this->topicId]->menu, $this->path );

		if( $this->path ){
			$parts		= explode( "/", $this->path );
			$pathBack	= implode( "/", array_slice( $parts, 0, -1 ) );
			$list[]		= $this->renderFolderItem( $pathBack, 'zurück', NULL, 'arrow-left' );
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
				$this->env->getMessenger()->noteFailure( $e->getMessage() );
			}
		}
		$parts		= explode( '/', $this->path );
		$pathLabel	= array();
		$way		= array();
		foreach( $parts as $nr => $part ){
			if( $nr == count( $parts ) - 1 )
				break;
			$way[]	= $part;
			$pathLabel[]	= UI_HTML_Tag::create( 'a', $part, array(
				'class'		=> 'trigger-folder',
				'data-url'	=> './manage/tinyMce/setPath/image/'.$this->topicId.'/'.base64_encode( join( '/', $way ) ),
			) );
		}
		$pathLabel[]	= $part;
		$pathLabel		= join( ' / ', $pathLabel );

		$listItems		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => $this->cssClassPrefix.' unstyled' ) );
		$listItems		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', $pathLabel ),
			UI_HTML_Tag::create( 'div', $listItems, array( 'id' => 'container-list-items' ) )
		) );

		return $listItems;
	}

	protected function renderLinkItem( $path, $filePath, $size = NULL, $icon = 'link' ){
		$parts	= explode( "/", $filePath );
		$label	= array_pop( $parts );
		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => $this->cssClassPrefix.'-item-icon fa fa-fw fa-'.$icon ) );
		$label	= UI_HTML_Tag::create( 'div', $label, array(
			'class'	=> $this->cssClassPrefix.'-item-label autocut',
		) );
		return UI_HTML_Tag::create( 'li', $image.$label, array(
			'class' 	=> $this->cssClassPrefix.'-item '.$this->cssClassPrefix.'-item-link trigger-submit',
			'data-url'	=> Logic_Frontend::getInstance( $this->env )->getPath().$path,
			'data-type'	=> 'link',
		) );
	}

	protected function renderLinkMode(){
		if( !isset( $this->topics[$this->topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

		$list		= array();
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
				$list[]		= $this->renderLinkItem( $link->value, $link->title, NULL, $icon );
			}
		}

		$parts		= explode( '/', $this->path );
		$pathLabel	= array();
		$way		= array();
		foreach( $parts as $nr => $part ){
			if( $nr == count( $parts ) - 1 )
				break;
			$way[]	= $part;
			$pathLabel[]	= UI_HTML_Tag::create( 'a', $part, array(
				'class'		=> 'trigger-folder',
				'data-url'	=> './manage/tinyMce/setPath/link/'.$this->topicId.'/'.base64_encode( join( '/', $way ) ),
			) );
		}
		$pathLabel[]	= $part;
		$pathLabel		= join( ' / ', $pathLabel );

		$listItems		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => $this->cssClassPrefix.' unstyled' ) );
		$listItems		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', $pathLabel ),
			UI_HTML_Tag::create( 'div', $listItems, array( 'id' => 'container-list-items' ) )
		) );

		return $listItems;
	}

	protected function renderTopBar(){
		$mode		= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
		$iconGrid	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconGrid.'&nbsp;Kacheln', array(
				'href'		=> './manage/tinyMce/setDisplayMode/'.$mode.'/0',
				'class'		=> 'btn not-btn-small '.( $this->displayMode == self::DISPLAY_MODE_GRID ? 'disabled' : NULL ),
			) ),
			UI_HTML_Tag::create( 'a', $iconList.'&nbsp;Liste', array(
				'href'		=> './manage/tinyMce/setDisplayMode/'.$mode.'/1',
				'class'		=> 'btn not-btn-small '.( $this->displayMode == self::DISPLAY_MODE_LIST ? 'disabled' : NULL ),
			) ),
		), array( 'class' => 'btn-group' ) );
	}

	protected function renderTopicList(){
		$list	= array();
		$mode	= $this->sourceMode == self::SOURCE_MODE_IMAGE ? 'image' : 'link';
		foreach( $this->topics as $topicId => $topic ){
			$count	= UI_HTML_Tag::create( 'small', '('.count( $topic->menu ).')', array( 'class' => 'muted' ) );
			$title	= rtrim( trim( $topic->title ), ":" );
			$link	= UI_HTML_Tag::create( 'a', $title.'&nbsp;'.$count, array(
				'href'	=> './manage/tinyMce/setTopic/'.$mode.'/'.$topicId,
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array(
				'class'		=> $topicId == $this->topicId ? 'active' : NULL,
			) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array(
			'class'	=> 'nav nav-pills nav-stacked'
		) );
	}

	public function setDisplayMode( $displayMode ){
		$this->displayMode		= $displayMode;
		$this->cssClassPrefix	= $displayMode == self::DISPLAY_MODE_GRID ? 'grid' : 'list';
		return $this;
	}

	public function setSourceMode( $sourceMode ){
		$this->sourceMode	= $sourceMode;
		return $this;
	}

	public function setTopicId( $topicId ){
		$this->topicId	= $topicId;
		return $this;
	}

	public function setTopics( $topics ){
		$this->topics	= $topics;
		return $this;
	}

	public function setPath( $path ){
		$this->path		= $path;
		return $this;
	}
}
