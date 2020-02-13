<?php
class View_Manage_Gallery extends CMF_Hydrogen_View{

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function renderList( $galleryId = NULL ){
		$words		= (object) $this->getWords( 'index' );
		$model		= new Model_Gallery( $this->env );
		$order		= $this->env->getConfig()->get( 'module.manage_galleries.sort.by' );
		$direction	= $this->env->getConfig()->get( 'module.manage_galleries.sort.direction' );
		$galleries	= $model->getAll( array(), array( $order => $direction ) );
//		$galleries	= $model->getAll( array(), array( 'rank' => 'DESC' ) );

		$statusIcons	= array(
			-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-close' ) ),
			0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
			1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) ),
		);

		$list	= '<div><em><small class="muted">'.$words->noEntries.'</small></em></div>';
		if( $galleries ){
			$list	= array();
			foreach( $galleries as $gallery ){
				$label		= htmlentities( $gallery->path, ENT_QUOTES, 'UTF-8' );
//				$label		= UI_HTML_Tag::create( 'abbr', $gallery->title, array( 'title' => $label ) );
				$url		= './manage/gallery/edit/'.$gallery->galleryId;
				$class		= NULL;
				$icon		= UI_HTML_Tag::create( 'span', "", array( 'class' => 'gallery-status-indicator status'.$gallery->status ) );
				$icon		= $statusIcons[$gallery->status].'&nbsp;';
				if( $galleryId == $gallery->galleryId )
					$class		= 'active';

				$images		= count( $this->getGalleryImages( $gallery->galleryId ) );
				$label		.= ' <small class="muted">('.$images.')</small>';
				$link		= UI_HTML_Tag::create( 'a', $icon.$label, array( 'href' => $url, 'class' => 'autocut' ) );
				$list[]		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class.' not-autocut' ) );
			}
			$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
		}
		return $list;
	}

	protected function renderThumbnail( $image, $linked = FALSE, $galleryPath = NULL ){
		$frontend	= Logic_Frontend::getInstance( $this->env );
		$baseUri	= $frontend->getPath( 'images' ).$this->env->getConfig()->get( 'module.manage_galleries.image.path' );
		if( !$galleryPath ){
			$model			= new Model_Gallery( $this->env );
			$galleryPath	= $model->get( $image->galleryId )->path;
		}
		$url		= $baseUri.$galleryPath.'/thumbs/'.$image->filename;
		$thumb		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $url, 'width' => '160' ) );
		if( $linked ){
			$url	= $baseUri.$galleryPath.'/'.$image->filename;
			$thumb	= UI_HTML_Tag::create( 'a', $thumb, array(
				'href'		=> $url,
				'target'	=> '_blank',
				'class'		=> "fancybox-auto",
				'rel'		=> 'gallery',
				'title'		=> htmlentities( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
		}
		return $thumb;
	}

	protected function getGalleryImages( $galleryId ){
		if( !isset( $this->images[$galleryId] ) ){
			$indices	= array( 'galleryId' => $galleryId );
			$orders		= array( 'rank' => 'ASC', 'timestamp' => 'DESC' );
			$model		= new Model_Gallery_Image( $this->env );
			$this->images[$galleryId]	= $model->getAllByIndices( $indices, $orders );
		}
		return $this->images[$galleryId];
	}

	static public function urlencodeTitle( $label, $delimiter = "_" ){
		$label	= str_replace( array( 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß' ), array( 'ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss' ), $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
    }
}
?>
