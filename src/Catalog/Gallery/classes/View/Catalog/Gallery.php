<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Catalog_Gallery extends View
{
	/**	@var	Logic_Catalog_Gallery	$logic */
	protected $logic;

	public function category()
	{
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category->galleryCategoryId, FALSE ) );
	}

	public function image()
	{
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category->galleryCategoryId, FALSE ) );
	}

	public function index()
	{
		$categories	= $this->getData( 'categories' );
	}

	public function preview()
	{
		$category	= $this->getData( 'category' );
		$categories	= $this->getData( 'categories' );
		$this->addData( 'categoryList', $this->renderCategoryList( $categories, $category, FALSE ) );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_Gallery( $this->env );
	}

	/**
	 *	@todo	implement auto-path (see controller)
	 */
	protected function renderCategoryList( $categories, $currentId = NULL, $badges = TRUE )
	{
		$list   	= [];
		$pathModule	= $this->logic->pathModule;
		$pathImages	= $this->logic->pathImages;
		foreach( $categories as $item ){
			if( $item->status == 1 ){
				$files	= count( $item->images );
				$count	= '<span class="badge">'.$files.'</span>';
				$href	= $pathModule.'category/'.$item->galleryCategoryId;									//   @todo	implement auto-path
				$attr	= ['href' => $href, 'class' => 'autocut', 'rel' => 'gallery'];
				$label	= $badges ? $count.' '.$item->title : $item->title;
				$link	= HtmlTag::create( 'a', $label, $attr );
				$attr	= ['class' => $item->galleryCategoryId == $currentId ? 'active' : NULL];
				$list[]	= HtmlTag::create( 'li', $link, $attr );
			}
		}
		$attr	= ['class' => 'nav nav-pills nav-stacked'];
		return HtmlTag::create( 'ul', $list, $attr );
	}

	protected function renderCategoryMatrix( $categories )
	{
		$list  		= [];
		$pathImages	= $this->logic->pathImages;
		$list		= [];
		foreach( $categories as $category ){
			if( $category->status == 1 ){
//				$count	= '<span class="badge">'.count( $item->images ).'</span>';
				$label	= HtmlTag::create( 'div', $category->title, ['class' => "gallery-category-matrix-item-label autocut"] );
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
				$list[]	= HtmlTag::create( 'div', $label, $attr );
			}
		}
		$attr	= [];
		return HtmlTag::create( 'div', $list, $attr );
	}

	protected function renderImageMatrix( $category, $images )
	{
		$list  		= [];
		$pathModule	= $this->logic->pathModule;
		$pathImages	= $this->logic->pathImages;
		$list		= [];
		foreach( $images as $image ){
			if( $image->status == 1 ){
//				$count	= '<span class="badge">'.count( $item->images ).'</span>';
				if( strlen( trim( $image->title ) ) )
					$label	= HtmlTag::create( 'div', $image->title.'&nbsp;', ['class' => "gallery-image-matrix-item-label autocut"] );
				$url	= $this->logic->pathModule.'image/'.$image->galleryImageId;				//   @todo	implement auto-path
				$src	= $pathImages."preview/".rawurlencode( $category->path )."/".rawurlencode( $image->filename );
				$attr	= array(
					'class'		=> "gallery-image-matrix-item",
					'href'		=> $url,
					'style'		=> 'background-image: url('.$src.');'
				);
				$list[]	= HtmlTag::create( 'a', ''/*$label*/, $attr );
			}
		}
		$attr	= [];
		return HtmlTag::create( 'div', $list, $attr );
	}
}