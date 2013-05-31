<?php
class View_Helper_TinyMceResourceLister extends CMF_Hydrogen_View_Helper_Abstract{

	public function __construct( CMF_Hydrogen_Environment_Web $env ){
		$this->setEnv( $env );
	}

	public function getImageList(){
		$pathFront	= "../";
		$pathImages	= "images/";
		$index	= new File_RecursiveRegexFilter( $pathFront.$pathImages, "/\.jpg$/i" );
		foreach( $index as $item ){
			$parts	= explode( "/", substr( $item->getPathname(), strlen( $pathImages ) ) );
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

	public function getLinkList(){
		$words		= (object) $this->getWords( 'link-prefixes', 'js/tinymce' );
		$resources	= explode( ",", $this->env->getConfig()->get( 'module.manage_pages.link.resources' ) );
		$links		= array();

		foreach( $resources as $resource ){
			switch( strtolower( trim( $resource ) ) ){
				case 'pages':
					$list  = array();
					if( class_exists( 'Model_Page' ) ){
						$model	= new Model_Page( $this->env );
						foreach( $model->getAllByIndex( 'status', 1, array( 'rank' => 'ASC' ) ) as $page ){
							$page->level		= 0;
							if( $page->parentId ){
								$parent = $model->get( $page->parentId );
								$page->level		= 1;
								if( $parent->parentId ){
									$grand  = $model->get( $parent->parentId );
									$parent->identifier = $grand->identifier.'/'.$parent->identifier;
									$parent->title		= $grand->title.' / '.$parent->title;
									$page->level		= 2;
								}
								$page->identifier   = $parent->identifier.'/'.$page->identifier;
								$page->title		= $parent->title.' / '.$page->title;
							}
							$list[$page->title]	= (object) array(
								'url'	=> './'.$page->identifier,
								'title'	=> $words->page.$page->title,
							);
						}
					}
					ksort( $list );
					$links	+= array_values( $list );
					break;
				case 'images':
					foreach( $this->getImageList() as $image ){
						$image->title	= $words->image.$image->title;
						$links[]	= $image;
					}
					break;
				case 'bookmarks':
					if( class_exists( 'Model_Bookmark' ) ){
						$model	= new Model_Bookmark( $this->env );
						foreach( $model->getAll() as $link ){
							$links[]	= (object) array(
								'url'	=> $link->url,
								'title'	=> $words->bookmark.$link->title,
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
								'title'	=> $words->document.$entry,
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
