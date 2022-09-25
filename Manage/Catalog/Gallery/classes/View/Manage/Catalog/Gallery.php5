<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Catalog_Gallery extends View
{
	public static function ___onTinyMCE_getImageList( Environment $env, $context, $module, $arguments = [] )
	{
		$frontend	= Logic_Frontend::getInstance( $env );
		$basePath	= $frontend->getConfigValue( 'path.images' );
		$options	= $env->getConfig()->getAll( 'module.manage_catalog_gallery.', TRUE );
		$pathImages	= $basePath.$options->get( 'path.images' );

		$modelImage		= new Model_Catalog_Gallery_Image( $env );
		$modelCategory	= new Model_Catalog_Gallery_Category( $env );

		/*  --  CATEGORIES  --  */
		$list			= [];
		$categories		= $modelCategory->getAllByIndex( 'status', '1' );
		foreach( $categories as $category ){
			$list[]	= (object) array(
				'title'	=> $category->title,
				'value'	=> $pathImages.$category->image,
			);
		}
		$list	= array( (object) array(
			'title'	=> 'Galerien:',
			'menu'	=> $list,
		) );
		$context->list	= array_merge( $context->list, $list );

		/*  --  CATEGORY IMAGES  --  */
		$list			= [];
		$categories		= $modelCategory->getAllByIndex( 'status', '1' );
		foreach( $categories as $category ){
			$images		= $modelImage->getAllByIndices( array(
				'status'			=> '1',
				'galleryCategoryId'	=> $category->galleryCategoryId
			), array( 'rank' => 'ASC', 'galleryImageId' => 'ASC' ) );
			foreach( $images as $nr => $image ){
				$label	= !empty( $image->title ) ? $image->title : $image->filename;
				$images[$nr]	= (object) array(
					'title'	=> $label,
					'value'	=> $pathImages.$category->path.'/'.$image->filename,
				);
			}
			$list[] = (object) array(
				'title'	=> $category->title,
				'menu'	=> array_values( $images ),
			);
		}
		$list	= array( (object) array(
			'title'	=> 'Bilder in Galerien:',
			'menu'	=> $list,
		) );
		$context->list	= array_merge( $context->list, $list );
	}

	public static function ___onTinyMCE_getLinkList( Environment $env, $context, $module, $arguments = [] )
	{
		$modelImage		= new Model_Catalog_Gallery_Image( $env );
		$modelCategory	= new Model_Catalog_Gallery_Category( $env );

		/*  --  CATEGORIES  --  */
		$list			= [];
		$categories		= $modelCategory->getAllByIndex( 'status', '1' );
		foreach( $categories as $category ){
			$list[]	= (object) array(
				'title'	=> $category->title,
				'value'	=> 'catalog/gallery/category/'.$category->galleryCategoryId,
			);
		}
		$list	= array( (object) array(
			'title'	=> 'Galerien:',
			'menu'	=> $list,
		) );
		$context->list	= array_merge( $context->list, $list );

		/*  --  CATEGORY IMAGES  --  */
		$list			= [];
		$categories		= $modelCategory->getAllByIndex( 'status', '1' );
		foreach( $categories as $category ){
			$images		= $modelImage->getAllByIndices( array(
				'status'			=> '1',
				'galleryCategoryId'	=> $category->galleryCategoryId
			), array( 'rank' => 'ASC', 'galleryImageId' => 'ASC' ) );
			foreach( $images as $nr => $image ){
				$label	= !empty( $image->title ) ? $image->title : $image->filename;
				$images[$nr]	= (object) array(
					'title'	=> $label,
					'value'	=> 'catalog/gallery/image/'.$image->galleryImageId,
				);
			}
			$list[] = (object) array(
				'title'	=> $category->title,
				'menu'	=> array_values( $images ),
			);
		}
		$list	= array( (object) array(
			'title'	=> 'Bilder in Galerien:',
			'menu'	=> $list,
		) );
		$context->list	= array_merge( $context->list, $list );
	}

	public function addCategory()
	{
	}

	public function addImage()
	{
	}

	public function editCategory()
	{
	}

	public function editImage()
	{
	}

	public function index()
	{
	}

	public function renderCategoryMatrix( $categories, $urlAdd = NULL )
	{
		$list  		= [];
		$pathImages	= $this->getData( 'pathImages' );
		$list		= [];
		foreach( $categories as $category ){
			$urlLink	= './manage/catalog/gallery/editCategory/'.$category->galleryCategoryId;
			$urlImage	= $pathImages.$category->image;
			if( !$category->image ){
				$model		= new Model_Catalog_Gallery_Image( $this->env );
				$orders		= array( 'status' => 'DESC', 'rank' => 'ASC' );
				$image		= $model->getByIndex( 'galleryCategoryId', $category->galleryCategoryId, $orders );
				if( $image )
					$urlImage	= $this->getData( 'pathPreview' ).rawurlencode( $category->path ).'/'.$image->filename;
			}
			$list[]	= $this->renderMatrixItem( $urlLink, $category->status, $category->title, $urlImage );
		}
		if( $urlAdd )
			$list[]	= $this->renderMatrixItem( $urlAdd, 1, '', NULL, 'add' );
		return HtmlTag::create( 'div', $list, array( 'class' => 'gallery-matrix' ) );
	}

	public function renderImageMatrix( $category, $urlAdd = NULL )
	{
		$list  		= [];
		$pathImages	= $this->getData( 'pathPreview' );
		$list		= [];
		foreach( $category->images as $image ){
			$urlLink	= './manage/catalog/gallery/editImage/'.$image->galleryImageId;
			$urlImage	= $pathImages.rawurlencode( $category->path ).'/'.$image->filename;
			$label	= strlen( trim( $image->title ) ) ? $image->title : $image->filename;
			$list[]	= $this->renderMatrixItem( $urlLink, $image->status, $label, $urlImage );
		}
		if( $urlAdd )
			$list[]	= $this->renderMatrixItem( $urlAdd, 1, '', NULL, 'add' );
		return HtmlTag::create( 'div', $list, array( 'class' => 'gallery-matrix' ) );
	}

	protected function renderMatrixItem( $url, $status, $label, $imageUrl = NULL, $class = NULL )
	{
		$image	= HtmlTag::create( 'div', '', array(
			'class'		=> "gallery-matrix-image",
			'style'		=> $imageUrl ? 'background-image: url('.$imageUrl.');' : NULL,
		) );
		$label	= strlen( trim( $label ) ) ? trim( $label ) : '&nbsp;';
		$label	= HtmlTag::create( 'span', $label, array(
/*			'href'	=> $url,*/
			'class'	=> "gallery-matrix-item-label autocut"
		) );
		return HtmlTag::create( 'div', $image.$label, array(
			'class'		=> "gallery-matrix-item img-polaroid status".$status.' '.$class,
			'onclick'	=> 'document.location.href="'.$url.'"',
		) );
	}
}
