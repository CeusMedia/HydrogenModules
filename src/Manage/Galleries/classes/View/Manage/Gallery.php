<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Gallery extends View
{
	protected array $images		= [];

	public static function urlencodeTitle( string $label, string $delimiter = "_" ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	protected function renderList( $galleryId = NULL ): string
	{
		$words		= (object) $this->getWords( 'index' );
		$model		= new Model_Gallery( $this->env );
		$order		= $this->env->getConfig()->get( 'module.manage_galleries.sort.by' );
		$direction	= $this->env->getConfig()->get( 'module.manage_galleries.sort.direction' );
		$galleries	= $model->getAll( [], [$order => $direction] );
//		$galleries	= $model->getAll( [], ['rank' => 'DESC'] );

		$statusIcons	= array(
			-1	=> HtmlTag::create( 'i', '', ['class' => 'icon-eye-close'] ),
			0	=> HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] ),
			1	=> HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] ),
		);

		$list	= '<div><em><small class="muted">'.$words->noEntries.'</small></em></div>';
		if( $galleries ){
			$list	= [];
			foreach( $galleries as $gallery ){
				$label		= htmlentities( $gallery->path, ENT_QUOTES, 'UTF-8' );
//				$label		= HtmlTag::create( 'abbr', $gallery->title, ['title' => $label] );
				$url		= './manage/gallery/edit/'.$gallery->galleryId;
				$class		= NULL;
				$icon		= HtmlTag::create( 'span', "", ['class' => 'gallery-status-indicator status'.$gallery->status] );
				$icon		= $statusIcons[$gallery->status].'&nbsp;';
				if( $galleryId == $gallery->galleryId )
					$class		= 'active';

				$images		= count( $this->getGalleryImages( $gallery->galleryId ) );
				$label		.= ' <small class="muted">('.$images.')</small>';
				$link		= HtmlTag::create( 'a', $icon.$label, ['href' => $url, 'class' => 'autocut'] );
				$list[]		= HtmlTag::create( 'li', $link, ['class' => $class.' not-autocut'] );
			}
			$list	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );
		}
		return $list;
	}

	protected function renderThumbnail( $image, bool $linked = FALSE, $galleryPath = NULL ): string
	{
		$frontend	= Logic_Frontend::getInstance( $this->env );
		$baseUri	= $frontend->getPath( 'images' ).$this->env->getConfig()->get( 'module.manage_galleries.image.path' );
		if( !$galleryPath ){
			$model			= new Model_Gallery( $this->env );
			$galleryPath	= $model->get( $image->galleryId )->path;
		}
		$url		= $baseUri.$galleryPath.'/thumbs/'.$image->filename;
		$thumb		= HtmlTag::create( 'img', NULL, ['src' => $url, 'width' => '160'] );
		if( $linked ){
			$url	= $baseUri.$galleryPath.'/'.$image->filename;
			$thumb	= HtmlTag::create( 'a', $thumb, array(
				'href'		=> $url,
				'target'	=> '_blank',
				'class'		=> "fancybox-auto",
				'rel'		=> 'gallery',
				'title'		=> htmlentities( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
		}
		return $thumb;
	}

	protected function getGalleryImages( string $galleryId )
	{
		if( !isset( $this->images[$galleryId] ) ){
			$indices	= ['galleryId' => $galleryId];
			$orders		= ['rank' => 'ASC', 'timestamp' => 'DESC'];
			$model		= new Model_Gallery_Image( $this->env );
			$this->images[$galleryId]	= $model->getAllByIndices( $indices, $orders );
		}
		return $this->images[$galleryId];
	}
}
