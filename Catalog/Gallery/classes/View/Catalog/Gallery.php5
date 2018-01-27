<?php
class View_Catalog_Gallery extends CMF_Hydrogen_View{

	/**	@var	Logic_Catalog_Gallery	$logic */
	protected $logic;

	public function __onInit(){
		$this->logic		= new Logic_Catalog_Gallery( $this->env );
	}

	public function category(){
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category->galleryCategoryId, FALSE ) );
	}

	public function image(){
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category->galleryCategoryId, FALSE ) );
	}

	public function index(){
		$categories	= $this->getData( 'categories' );
	}

	public function preview(){
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category, FALSE ) );
	}

	/**
	 *	@todo	implement auto-path (see controller)
	 */
	protected function renderCategoryList( $categories, $currentId = NULL, $badges = TRUE ){
		$list   	= array();
		$pathModule	= $this->logic->pathModule;
		$pathImages	= $this->logic->pathImages;
		foreach( $categories as $item ){
			if( $item->status == 1 ){
				$files	= count( $item->images );
				$count	= '<span class="badge">'.$files.'</span>';
				$href	= $pathModule.'category/'.$item->galleryCategoryId;									//   @todo	implement auto-path
				$attr	= array( 'href' => $href, 'class' => 'autocut', 'rel' => 'gallery' );
				$label	= $badges ? $count.' '.$item->title : $item->title;
				$link	= UI_HTML_Tag::create( 'a', $label, $attr );
				$attr	= array( 'class' => $item->galleryCategoryId == $currentId ? 'active' : NULL );
				$list[]	= UI_HTML_Tag::create( 'li', $link, $attr );
			}
		}
		$attr	= array( 'class' => 'nav nav-pills nav-stacked' );
		return UI_HTML_Tag::create( 'ul', $list, $attr );
	}

	protected function renderCategoryMatrix( $categories ){
		$list  		= array();
		$pathImages	= $this->logic->pathImages;
		$list		= array();
		foreach( $categories as $category ){
			if( $category->status == 1 ){
//				$count	= '<span class="badge">'.count( $item->images ).'</span>';
				$label	= UI_HTML_Tag::create( 'div', $category->title, array( 'class' => "gallery-category-matrix-item-label autocut" ) );
				$url	= $this->logic->pathModule.'category/'.$category->galleryCategoryId;				//   @todo	implement auto-path
				$attr	= array(
					'class'		=> "gallery-category-matrix-item",
					'onclick'	=> 'document.location.href="'.$url.'"',
				);
				$src	= $pathImages.$category->image;
				if( !$category->image && $category->images ){
					$image	= $category->images[0];
					$src	= $pathImages.'preview/'.rawurlencode( $category->path ).'/'.$image->filename;
				}
				$attr['style']	= 'background-image: url('.$src.');';
				$list[]	= UI_HTML_Tag::create( 'div', $label, $attr );
			}
		}
		$attr	= array();
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}

	protected function renderImageMatrix( $category, $images ){
		$list  		= array();
		$pathModule	= $this->logic->pathModule;
		$pathImages	= $this->logic->pathImages;
		$list		= array();
		foreach( $images as $image ){
			if( $image->status == 1 ){
//				$count	= '<span class="badge">'.count( $item->images ).'</span>';
				if( strlen( trim( $image->title ) ) )
					$label	= UI_HTML_Tag::create( 'div', $image->title.'&nbsp;', array( 'class' => "gallery-image-matrix-item-label autocut" ) );
				$url	= $this->logic->pathModule.'image/'.$image->galleryImageId;				//   @todo	implement auto-path
				$src	= $pathImages."preview/".rawurlencode( $category->path )."/".rawurlencode( $image->filename );
				$attr	= array(
					'class'		=> "gallery-image-matrix-item",
					'href'		=> $url,
					'style'		=> 'background-image: url('.$src.');'
				);
				$list[]	= UI_HTML_Tag::create( 'a', ''/*$label*/, $attr );
			}
		}
		$attr	= array();
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}
}
