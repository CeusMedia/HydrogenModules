<?php
class View_Manage_Page extends CMF_Hydrogen_View{

	public function __onInit(){
//		$page	= $this->env->getPage();
	}

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function getPageIcon( $page ){
		switch( $page->type ){
			case 0:
				return '<i class="icon-leaf"></i>';
			case 1:
				return '<i class="icon-chevron-down"></i>';
			case 2:
				return '<i class="icon-fire"></i>';
		}
	}

	public function renderTree( $tree, $currentPage = NULL ){
		$list	= array();
		foreach( $tree as $item ){
			$sublist	= array();
			foreach( $item->subpages as $subitem ){
				$classes	= array();
				if( $currentPage && $currentPage->pageId == $subitem->pageId )
					$classes[]	= 'active';
				if( $subitem->status == 0 )
					$classes[]	= 'disabled';
				$url	= './manage/page/edit/'.$subitem->pageId;
				$label	= $this->getPageIcon( $subitem ).' <small>'.$subitem->title.'</small>';
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => join( ' ', $classes ) ) );
			}
			if( $sublist )
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'nav nav-pills nav-stacked' ) );
			else
				$sublist	= '';
			$classes	= array();
			if( $currentPage && $currentPage->pageId == $item->pageId )
				$classes[]	= 'active';
			if( $item->status == 0 )
				$classes[]	= 'disabled';
			$url	= './manage/page/edit/'.$item->pageId;
			$label	= $this->getPageIcon( $item ).' '.$item->title;
			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => join( ' ', $classes ) ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	}

	protected function getImageList(){
		$pathFront	= "../";
		$pathImages	= "images/";
		$index	= new File_RecursiveRegexFilter( $pathFront.$pathImages, "/\.jpg$/i" );
		foreach( $index as $item ){
			$parts	= explode( "/", $item->getPathname() );
			$file	= array_pop( $parts );
			$path	= implode( '/', array_slice( $parts, 1 ) );
			$label	= $path ? $path.'/'.$file : $file;
			$uri	= substr( $item->getPathname(), strlen( $pathFront ) );
			$list[$item->getPathname()]	= (object) array(
				'title'	=> $label,
				'url'	=> $uri,
			);
		}
		ksort( $list );
		return array_values( $list );
	}

	protected function getLinkList( $pages ){
		$words		= (object) $this->getWords( 'list-links' );
		$resources	= explode( ",", $this->env->getConfig()->get( 'module.manage_pages.link.resources' ) );
		$links		= array();

		foreach( $resources as $resource ){
			switch( strtolower( trim( $resource ) ) ){
				case 'pages':
					foreach( $pages as $page ){
						$links[]	= (object) array(
							'url'	=> './'.$page->identifier,
							'title'	=> $words->prefixPage.$page->title,
						);
					}
					break;
				case 'images':
					foreach( $this->getImageList() as $image ){
						$image->title	= $words->prefixImage.$image->title;
						$links[]	= $image;
					}
					break;
				case 'links':
					if( class_exists( 'Model_Link' ) ){
						$model	= new Model_Link( $this->env );
						foreach( $model->getAll() as $link ){
							$links[]	= (object) array(
								'url'	=> $link->url,
								'title'	=> $words->prefixLink.$link->title,
							);
						}
					}
					break;
				case 'documents':
					if( class_exists( 'Model_Document' ) ){
						$model	= new Model_Document( $this->env, '../documents/' );
						foreach( $model->index() as $entry ){
							$links[]	= (object) array(
								'url'	=> 'documents/'.$entry,
								'title'	=> $words->prefixDocument.$entry,
							);
						}
					}
					break;
			}
		}
		return $links;
	}
}
?>
