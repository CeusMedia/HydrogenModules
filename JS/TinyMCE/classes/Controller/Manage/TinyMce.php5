<?php
class Controller_Manage_TinyMce extends CMF_Hydrogen_Controller {

	protected $thumbnailer;
	protected $request;
	protected $session;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->thumbnailer	= new View_Helper_Thumbnailer( $this->env, 128, 128 );

		$this->baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->sessionPrefix	= 'manager_tinymce_';
	}

	public function index( $mode = 'image' ){
		$topicId	= (int) $this->session->get( $this->sessionPrefix.$mode );
		$path		= (string) $this->session->get( $this->sessionPrefix.$mode.'_'.$topicId );
		$helper		= new View_Helper_TinyMce( $this->env );

		try{
			if( $mode === 'image' ){
				$topics		= $helper->getImageList();
				$content	= $this->renderImageMode( $path, $topics, $topicId );
			}
			else{
				$topics		= $helper->getLinkList();
				$content	= $this->renderLinkMode( $path, $topics, $topicId );
			}
			$listTopics		= $this->renderTopicList( $mode, $topics, $topicId );
		}
		catch( Exception $e ){
remark( $e->getMessage() );
remark( 'mode: '.$mode );
remark( 'topic: '.$topicId );
remark( 'path: '.$path );
die;
			$topicId	= (int) $this->session->set( $this->sessionPrefix.$mode, 0 );
			$this->restart( $mode, TRUE );
		}
		$html			= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $listTopics, array( 'class' => 'span2' ) ),
				UI_HTML_Tag::create( 'div', $content, array( 'class' => 'span10' ) ),
			), array( 'class' =>'row-fluid' ) )
		), array( 'class' =>'container-fluid', 'id' => 'container-tinymce-file-browser' ) );

		$page	= $this->env->getPage();
		$page->js->addScriptOnReady( 'tinymce.FileBrowser.initBrowser();' );
		$page->addBody( $html );
		print( $page->build() );
		exit;
	}

	protected function renderLinkMode( $path, $topics, $topicId ){
		if( !isset( $topics[$topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

//print_m( $topics[$topicId] );
//die;

		$list		= array();
		$folders	= $this->filterFoldersByPath( $topics[$topicId]->menu, $path );
//		$folders	= $topics[$topicId]->menu;
		$links		= $this->filterItemsByPath( $topics[$topicId]->menu, $path );
//		$links		= $topics[$topicId]->menu;

		if( $path ){
			$parts		= explode( "/", $path );
			$pathBack	= implode( "/", array_slice( $parts, 0, -1 ) );
			$list[]		= $this->renderFolderItem( 'link', $topicId, $pathBack, 'zurück', NULL, 'arrow-left' );
		}

		foreach( $folders as $label => $count ){
			$pathNext	= $path ? $path.'/'.$label : $label;
			$list[]		= $this->renderFolderItem( 'link', $topicId, $pathNext, $label, $count );
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

		$parts		= explode( '/', $path );
		$pathLabel	= array();
		$way		= array();
		foreach( $parts as $nr => $part ){
			if( $nr == count( $parts ) - 1 )
				break;
			$way[]	= $part;
			$pathLabel[]	= UI_HTML_Tag::create( 'a', $part, array(
				'class'		=> 'trigger-folder',
				'data-url'	=> './manage/tinyMce/setPath/link/'.$topicId.'/'.base64_encode( join( '/', $way ) ),
			) );
		}
		$pathLabel[]	= $part;
		$pathLabel		= join( ' / ', $pathLabel );

		$listItems		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'grid unstyled' ) );
		$listItems		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', $pathLabel ),
			UI_HTML_Tag::create( 'div', $listItems, array( 'id' => 'container-list-items' ) )
		) );

		return $listItems;
	}

	protected function renderImageMode( $path, $topics, $topicId ){
		if( !isset( $topics[$topicId] ) )
			throw new DomainException( 'Invalid topic ID' );

		$list		= array();
		$folders	= $this->filterFoldersByPath( $topics[$topicId]->menu, $path );
		$images		= $this->filterItemsByPath( $topics[$topicId]->menu, $path );

		if( $path ){
			$parts		= explode( "/", $path );
			$pathBack	= implode( "/", array_slice( $parts, 0, -1 ) );
			$list[]		= $this->renderFolderItem( 'image', $topicId, $pathBack, 'zurück', NULL, 'arrow-left' );
		}

		foreach( $folders as $label => $count ){
			$pathNext	= $path ? $path.'/'.$label : $label;
			$list[]		= $this->renderFolderItem( 'image', $topicId, $pathNext, $label, $count );
		}

		foreach( $images as $image )
			$list[]		= $this->renderImageItem( $image->value, $image->title );


		$parts		= explode( '/', $path );
		$pathLabel	= array();
		$way		= array();
		foreach( $parts as $nr => $part ){
			if( $nr == count( $parts ) - 1 )
				break;
			$way[]	= $part;
			$pathLabel[]	= UI_HTML_Tag::create( 'a', $part, array(
				'class'		=> 'trigger-folder',
				'data-url'	=> './manage/tinyMce/setPath/image/'.$topicId.'/'.base64_encode( join( '/', $way ) ),
			) );
		}
		$pathLabel[]	= $part;
		$pathLabel		= join( ' / ', $pathLabel );

		$listItems		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'grid unstyled' ) );
		$listItems		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', $pathLabel ),
			UI_HTML_Tag::create( 'div', $listItems, array( 'id' => 'container-list-items' ) )
		) );

		return $listItems;
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

	protected function renderFolderItem( $mode, $topicId, $path, $label, $count = NULL, $icon = 'folder-open' ){
		$path	= base64_encode( $path );
		if( is_int( $count ) )
			$count	= UI_HTML_Tag::create( 'small', '('.$count.')', array( 'class' => 'muted' ) );
		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'grid-item-icon fa fa-fw fa-'.$icon ) );
		$label	= UI_HTML_Tag::create( 'div', $label.'&nbsp;'.$count, array(
			'class'	=> 'grid-item-label autocut',
		) );
		return UI_HTML_Tag::create( 'li', $image.$label, array(
			'class' 	=> 'grid-item grid-item-folder trigger-folder',
			'data-url'	=> './manage/tinyMce/setPath/'.$mode.'/'.$topicId.'/'.$path,
		) );
	}

	protected function renderLinkItem( $path, $filePath, $size = NULL, $icon = 'link' ){
		$parts	= explode( "/", $filePath );
		$label	= array_pop( $parts );
		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'grid-item-icon fa fa-fw fa-'.$icon ) );
		$label	= UI_HTML_Tag::create( 'div', $label, array(
			'class'	=> 'grid-item-label autocut',
		) );
		return UI_HTML_Tag::create( 'li', $image.$label, array(
			'class' 	=> 'grid-item grid-item-link trigger-submit',
			'data-url'	=> Logic_Frontend::getInstance( $this->env )->getPath().$path,
			'data-type'	=> 'link',
		) );
	}

	protected function renderImageItem( $path, $filePath, $size = NULL, $icon = 'image' ){
		$parts	= explode( "/", $filePath );
		$label	= array_pop( $parts );
//		if( is_string( $size ) )
//			$size	= UI_HTML_Tag::create( 'small', '('.$size.')', array( 'class' => 'muted' ) );
//		$image	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'grid-item-icon fa fa-fw fa-'.$icon ) );

		$remoteFilePath	= Logic_Frontend::getInstance( $this->env )->getPath().$path;

		$remoteFilePath = Logic_Frontend::getInstance( $this->env )->getPath().$path;
		if( preg_match( '/^file\//', $path ) ){
			$bucket = new Logic_FileBucket( $this->env );
			$file   = $bucket->getByPath( substr( $path, 5 ) );
			if( $file )
				$remoteFilePath = $bucket->getPath().$file->hash;
			else
				throw new Exception( 'No file found in bucket for: '.$path );
		}
		$data			= $this->thumbnailer->get( $remoteFilePath, 128, 128 );
		$image			= UI_HTML_Tag::create( 'div', NULL, array(
			'class'		=> 'grid-item-icon trigger-submit',
			'style'		=> 'background-image: url('.$data.')',
			'data-url'	=> Logic_Frontend::getInstance( $this->env )->getUri().$path,
			'data-type'	=> 'image',
		) );
		$label	= UI_HTML_Tag::create( 'div', $filePath.'&nbsp;'.$size, array(
			'class'	=> 'grid-item-label autocut',
		) );
		return UI_HTML_Tag::create( 'li', $image.$label, array(
			'class' 	=> 'grid-item grid-item-file',
			'data-url'	=> '',
		) );
	}

	protected function renderTopicList( $mode, $topics, $currentTopicId ){
		$list	= array();
		foreach( $topics as $topicId => $topic ){
			$count	= UI_HTML_Tag::create( 'small', '('.count( $topic->menu ).')', array( 'class' => 'muted' ) );
			$title	= rtrim( trim( $topic->title ), ":" );
			$link	= UI_HTML_Tag::create( 'a', $title.'&nbsp;'.$count, array(
				'href'	=> './manage/tinyMce/setTopic/'.$mode.'/'.$topicId,
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array(
				'class'		=> $topicId == $currentTopicId ? 'active' : NULL,
			) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array(
			'class'	=> 'nav nav-pills nav-stacked'
		) );
	}

	public function setTopic( $mode, $topicId ){
		$this->session->set( $this->sessionPrefix.$mode, $topicId );
		$this->restart( $mode, TRUE );
//		$this->restart( NULL, TRUE );
	}

	public function setPath( $mode, $topicId, $pathBase64 = '' ){
		$this->session->set( $this->sessionPrefix.$mode.'_'.$topicId, base64_decode( $pathBase64 ) );
		$this->restart( $mode, TRUE );
//		$this->restart( NULL, TRUE );
	}
}
