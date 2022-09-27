<?php

use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Exif as ImageExif;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Catalog_Gallery extends Controller
{
	protected $request;
	protected $options;

	protected $pathImages;
	protected $pathImagesOriginal;
	protected $pathImagesPreview;
	protected $pathImagesThumbnail;

	public function addCategory( $parentCategoryId = 0 )
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'msg' );
			$data	= array(
				'parentId'		=> $parentCategoryId,
				'status'		=> $this->request->get( 'status' ),
				'path'			=> str_replace( "/", "", $this->request->get( 'path' ) ),
				'title'			=> $this->request->get( 'title' ),
				'price'			=> $this->request->get( 'price' ),
				'rank'			=> $this->request->get( 'rank' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			);
			$categoryId	= $this->modelCategory->add( $data );
			mkdir( $this->pathImagesPreview.$data['path'] );
			mkdir( $this->pathImagesThumbnail.$data['path'] );
			mkdir( $this->pathImagesOriginal.$data['path'] );
			$this->messenger->noteSuccess( $words->successCategoryAdded );
			$this->restart( $categoryId, TRUE );
		}

		$data	= [];
		foreach( $this->modelCategory->getColumns() as $column )
			$data[$column]	= $this->request->get( $column );
		$lastRank	= $this->modelCategory->getAll( array(), array( 'rank' => 'DESC' ), array( 0, 1 ) );
		$lastRank	= $lastRank ? $lastRank[0]->rank : 0;
		$data['rank']	= max( 1, $lastRank, (int) $this->request->get( 'rank' ) );
		$this->addData( 'category', (object) $data );
		$this->addData( 'parentCategoryId', $parentCategoryId );
		$this->addData( 'categoryId', $parentCategoryId );
	}

	public function addImage( $categoryId )
	{
		$category	= $this->checkCategoryId( $categoryId );
		if( $this->request->has( 'save') ){
			$words	= (object) $this->getWords( 'msg' );
			$data	= array(
				'title'				=> $this->request->get( 'title' ),
				'status'			=> $this->request->get( 'status' ),
				'price'				=> $this->request->get( 'price' ),
				'rank'				=> $this->request->get( 'rank' ),
				'type'				=> $this->request->get( 'type' ),
				'rank'				=> $this->request->get( 'rank' ),
				'galleryCategoryId'	=> $categoryId,
				'single'			=> TRUE,
				'modifiedAt'		=> time(),
			);
			try{
				$imageId	= $this->modelImage->add( $data );
				$this->rerankImages( $categoryId );
				if( $this->uploadImage( $imageId, $this->request->get( 'upload' ) ) ){
					$this->messenger->noteSuccess( $words->successImageAdded );
/*					if( $this->request->has( 'addToSlider' ) ){
						$this->request->set( 'image', $this->request->get( 'upload' ) );
						$sliderId	= $this->request->get( 'sliderId' );
						$from		= 'manage/catalog/gallery/editCategory/'.$categoryId;
						$url		= './manage/image/slider/addSlide/'.$sliderId.'?from='.$from;
						$this->restart( $url );
					}*/
					$this->restart( 'editImage/'.$imageId, TRUE );
				}
				else{
					$this->modelImage->remove( $imageId );
				}
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
				$this->modelImage->remove( $imageId );
			}
			$this->restart( 'editCategory/'.$categoryId, TRUE );
		}
		$lastRank	= (int) $this->modelImage->getByIndex( 'galleryCategoryId', $categoryId, 'rank', array( 'rank' => 'DESC' ) );
		$data	= [];
		$number		= $this->modelImage->countByIndex( 'galleryCategoryId', $categoryId );
		foreach( $this->modelImage->getColumns() as $column )
			$data[$column]	= $this->request->get( $column );
		$data['rank']	= max( $number, $lastRank + 1, (int) $this->request->get( 'rank' ) );
		$this->addData( 'image', (object) $data );
		$this->addData( 'categoryId', $categoryId );
		$this->addData( 'category', $category );
	}

	public function addImageToSlider( $imageId )
	{
		$this->restart( './manage/catalog/gallery/editImage/'.$imageId );
	}

	public function editCategory( $categoryId )
	{
		$category	= $this->checkCategoryId( $categoryId );
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'msg' );
			$data	= array(
				'path'			=> $this->request->get( 'path' ),
				'title'			=> $this->request->get( 'title' ),
				'status'		=> $this->request->get( 'status' ),
				'price'			=> $this->request->get( 'price' ),
				'rank'			=> $this->request->get( 'rank' ),
				'modifiedAt'	=> time(),
			);
			if( $category->path !== $data['path'] ){
				rename( $this->pathImagesPreview.$category->path, $this->pathImagesPreview.$data['path'] );
				rename( $this->pathImagesOriginal.$category->path, $this->pathImagesOriginal.$data['path'] );
				rename( $this->pathImagesThumbnail.$category->path, $this->pathImagesThumbnail.$data['path'] );
			}
			$image	= $this->request->get( 'image' );
			if( $image && $image['error'] == 0 ){
				$upload	= new Logic_Upload( $this->env );
				$upload->setUpload( $image );
				if( !$upload->checkExtension( array( 'jpg', 'jpe', 'jpeg', 'gif' ) ) )
					$this->messenger->noteError( $words->errorUnsupportedFileType );
				else if( !$upload->checkMimeType( array( 'image/jpeg' ) ) )
					$this->messenger->noteError( $words->errorUnsupportedFileType );
				else if( $upload->checkIsImage() ){
					$imagePath		= $this->pathImages.$categoryId.'.'.$upload->getExtension( true );
					$upload->saveTo( $imagePath );
					$image		= new Image( $imagePath );
					$processor	= new ImageProcessing( $image );
					$size		= (int) min( $image->getWidth(), $image->getHeight() );
					$marginLeft	= (int) floor( ( $image->getWidth() - $size ) / 2 );
					$marginTop	= (int) floor( ( $image->getHeight() - $size ) / 2 );
					$processor->crop( $marginLeft, $marginTop, $size, $size );
					$processor->scaleDownToLimit( 400, 400 );
					$image->save();
					$data['image']	= $categoryId.'.'.$upload->getExtension( true );
				}
			}
			$this->modelCategory->edit( $categoryId, $data );
			$this->messenger->noteSuccess( $words->successCategoryEdited );
			$this->rerankCategories();
			$this->restart( 'editCategory/'.$categoryId, TRUE );
		}
		$category	= $this->modelCategory->get( $categoryId );
		$category->images	= $this->modelImage->getAll( array(
			'galleryCategoryId'	=> $categoryId,
		), array( 'rank' => 'ASC', 'galleryImageId' => 'ASC' ) );
		$this->addData( 'categoryId', (int) $categoryId );
		$this->addData( 'category', $category );
	}

	public function edit( $imageId )
	{
		$this->restart( 'editImage/'.$imageId, TRUE );
	}

	public function editImage( $imageId )
	{
		$image		= $this->checkImageId( $imageId );
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'msg' );
			$data	= array(
				'galleryCategoryId'	=> $this->request->get( 'categoryId' ),
				'filename'		=> $this->request->get( 'filename' ),
				'title'			=> $this->request->get( 'title' ),
				'status'		=> $this->request->get( 'status' ),
				'price'			=> $this->request->get( 'price' ),
				'rank'			=> $this->request->get( 'rank' ),
				'type'			=> $this->request->get( 'type' ),
				'modifiedAt'	=> time(),
			);
			$filenameHasChanged	= $image->filename !== $data['filename'];
			$categoryHasChanged	= $image->galleryCategoryId != $data['galleryCategoryId'];
			if( $filenameHasChanged || $categoryHasChanged ){
				$categoryOld	= $this->modelCategory->get( $image->galleryCategoryId );
				$categoryNew	= $this->modelCategory->get( $data['galleryCategoryId'] );
				$imagePathOld	= $categoryOld->path.'/'.$image->filename;
				$imagePathNew	= $categoryNew->path.'/'.$data['filename'];
				rename( $this->pathImagesPreview.$imagePathOld, $this->pathImagesPreview.$imagePathNew );
				rename( $this->pathImagesOriginal.$imagePathOld, $this->pathImagesOriginal.$imagePathNew );
				rename( $this->pathImagesThumbnail.$imagePathOld, $this->pathImagesThumbnail.$imagePathNew );
			}
			$this->modelImage->edit( $imageId, $data );
			$this->messenger->noteSuccess( $words->successImageEdited );
			$this->rerankImages( $image->galleryCategoryId );
			if( $categoryHasChanged )
				$this->rerankImages( $data['galleryCategoryId'] );
			if( $this->request->get( 'upload' ) ){
				if( $this->request->get( 'upload' )['error'] != 4 ){
					try{
						if( $this->uploadImage( $imageId, $this->request->get( 'upload' ) ) ){
//							$this->messenger->noteSuccess( 'Das Bild wurde neu hoch geladen und skaliert.' );
						}
					}
					catch( Exception $e ){
						$this->messenger->noteFailure( $e->getMessage() );
					}
				}
			}
			$this->restart( 'editImage/'.$imageId, TRUE );
		}

		$category		= $this->modelCategory->get( $image->galleryCategoryId );
		$pathOriginal	= $this->pathImagesOriginal.$category->path.'/'.$image->filename;
		$this->addData( 'category', $category );
		$this->addData( 'categoryId', $category->galleryCategoryId );
		$this->addData( 'image', $this->modelImage->get( $imageId ) );
		$this->addData( 'imageId', $imageId );
		$this->addData( 'imageObject', new Image( $pathOriginal ) );
	}

	public function index( $categoryId = 0 )
	{
		if( $categoryId )
			$this->restart( 'editCategory/'.$categoryId, TRUE );
	}

	public function viewOriginal( $imageId )
	{
		$image			= $this->checkImageId( $imageId );
		$category		= $this->checkCategoryId( $image->galleryCategoryId );
		$uri			= $this->pathImagesOriginal.$category->path.'/'.$image->filename;
		$imageObject	= new Image( $uri );
		$mimeType		= $imageObject->getMimeType();
		header( 'Content-Type: '.$imageObject->getMimeType() );
		print File_Reader::load( $uri );
		exit;
	}

	public function removeCategory( $categoryId )
	{
		$category	= $this->checkCategoryId( $categoryId );
		$words		= (object) $this->getWords( 'msg' );
		$images	= $this->modelImage->getAllByIndex( 'galleryCategoryId', $categoryId );
		if( $images ){
			foreach( $images as $image ){
				unlink( $this->pathImagesPreview.$category->path.'/'.$image->filename );
				unlink( $this->pathImagesThumbnail.$category->path.'/'.$image->filename );
				unlink( $this->pathImagesOriginal.$category->path.'/'.$image->filename );
			}
		}
		unlink( $this->pathImages.$category->image );
		rmdir( $this->pathImagesPreview.$category->path );
		rmdir( $this->pathImagesThumbnail.$category->path );
		rmdir( $this->pathImagesOriginal.$category->path );
		$this->modelCategory->remove( $categoryId );
		$this->messenger->noteSuccess( $words->successCategoryRemoved );
		$this->restart( NULL, TRUE );
	}

	public function removeCategoryCover( $categoryId )
	{
		$category	= $this->checkCategoryId( $categoryId );
		$words		= (object) $this->getWords( 'msg' );
		unlink( $this->pathImages.$category->image );
		$this->modelCategory->edit( $categoryId, array(
			'image'			=> NULL,
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'editCategory/'.$categoryId, TRUE );
	}

	public function removeImage( $imageId )
	{
		$image		= $this->checkImageId( $imageId );
		$category	= $this->checkCategoryId( $image->galleryCategoryId );
		$words		= (object) $this->getWords( 'msg' );
		unlink( $this->pathImagesPreview.$category->path.'/'.$image->filename );
		unlink( $this->pathImagesThumbnail.$category->path.'/'.$image->filename );
		unlink( $this->pathImagesOriginal.$category->path.'/'.$image->filename );
		$this->modelImage->remove( $imageId );
		$this->messenger->noteSuccess( $words->successImageRemoved );
		$this->restart( $image->galleryCategoryId, TRUE );
	}

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_catalog_gallery.', TRUE );

		$this->pathImages			= $this->frontend->getPath( 'images' ).$this->moduleConfig->get( 'path.images' );
		$this->pathImagesOriginal	= $this->pathImages.'original/';
		$this->pathImagesPreview	= $this->pathImages.'preview/';
		$this->pathImagesThumbnail	= $this->pathImages.'thumbnail/';

		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );

		if( !is_dir( $this->pathImagesOriginal ) )
			mkdir( $this->pathImagesOriginal );
		if( !is_dir( $this->pathImagesPreview ) )
			mkdir( $this->pathImagesPreview );
		if( !is_dir( $this->pathImagesThumbnail ) )
			mkdir( $this->pathImagesThumbnail );

		if( !file_exists( $this->pathImagesOriginal.'.htaccess' ) )
			File_Writer::save( $this->pathImagesOriginal.'.htaccess', 'Deny from all' );

		$categories	= $this->modelCategory->getAll( array(), array( 'rank' => 'ASC', 'galleryCategoryId' => 'ASC' ));
		foreach( $categories as $nr => $category ){
			$category->images	= $this->modelImage->getAll( array(
				'galleryCategoryId'	=> (int) $category->galleryCategoryId
			), array( 'rank' => 'ASC', 'galleryImageId' => 'ASC' ) );
		}
		$this->addData( 'categories', $categories );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'pathImages', $this->pathImages );
		$this->addData( 'pathOriginal', $this->pathImagesOriginal );
		$this->addData( 'pathThumbnail', $this->pathImagesThumbnail );
		$this->addData( 'pathPreview', $this->pathImagesPreview );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	protected function checkCategoryId( $categoryId )
	{
		$category	= $this->modelCategory->get( $categoryId );
		if( $category )
			return $category;
		$words		= (object) $this->getWords( 'msg' );
		$this->messenger->noteError( $words->errorCategoryIdInvalid );
		$this->restart( NULL, TRUE );
	}

	protected function checkImageId( $imageId )
	{
		$image	= $this->modelImage->get( $imageId );
		if( $image )
			return $image;
		$words		= (object) $this->getWords( 'msg' );
		$this->messenger->noteError( $words->errorImageIdInvalid );
		$this->restart( NULL, TRUE );
	}

	protected function rerankCategories( $start = 1 )
	{
		$rank		= max( 1, (int) $start );
		$conditions	= $start ? array( 'rank' => '>= '.(int) $rank ) : array();
		$categories	= $this->modelCategory->getAll( $conditions, array( 'rank' => 'ASC', 'modifiedAt' => 'DESC' ) );
		foreach( $categories as $category ){
			$this->modelCategory->edit( $category->galleryCategoryId, array( 'rank'	=> $rank ) );
			$rank	+= 1;
		}
	}

	protected function rerankImages( $categoryId, $start = 1 )
	{
		$conditions	= array( 'galleryCategoryId' => $categoryId );
		if( ( $rank = max( 1, (int) $start ) ) > 1 )
			$conditions['rank']	= '>= '.(int) $rank;
		$images		= $this->modelImage->getAll( $conditions, array( 'rank' => 'ASC', 'modifiedAt' => 'DESC' ) );
		foreach( $images as $image ){
			$this->modelImage->edit( $image->galleryImageId, array( 'rank'	=> $rank ) );
			$rank	+= 1;
		}
	}

	protected function uploadImage( $imageId, $uploadData )
	{
		$image		= $this->checkImageId( $imageId );
		$category	= $this->checkCategoryId( $image->galleryCategoryId );
		$words		= (object) $this->getWords( 'msg' );
		$upload		= new Logic_Upload( $this->env );
		$upload->setUpload( $uploadData );
		if( !$upload->checkExtension( array( 'jpg', 'jpe', 'jpeg', 'gif', 'png' ) ) )
			$this->messenger->noteError( $words->errorUnsupportedFileType );
		else if( !$upload->checkMimeType( array( 'image/jpeg', 'image/png' ) ) )
			$this->messenger->noteError( $words->errorUnsupportedFileType );
		else{
			$imagePath	= $category->path.'/'.$upload->getFileName();
			$upload->saveTo( $this->pathImagesOriginal.$imagePath );
			$sizes		= (object) $this->moduleConfig->getAll( 'size.preview.' );
			$upload->scaleImage( $this->pathImagesPreview.$imagePath, $sizes->x, $sizes->y );
			$sizes		= (object) $this->moduleConfig->getAll( 'size.thumbnail.' );
			$upload->scaleImage( $this->pathImagesThumbnail.$imagePath, $sizes->x, $sizes->y );
			if( $image->filename !== $upload->getFileName() ){
				@unlink( $this->pathImagesPreview.$category->path.'/'.$image->filename );
				@unlink( $this->pathImagesThumbnail.$category->path.'/'.$image->filename );
				@unlink( $this->pathImagesOriginal.$category->path.'/'.$image->filename );
			}
			$data	= array(
				'filename'		=> $upload->getFileName(),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			);
			$exif		= new ImageExif( $this->pathImagesOriginal.$imagePath );
			if( $exif->has( 'DateTime' ) )
				$data['takenAt']	= strtotime( $exif->get( 'DateTime' ) );
			else
				$data['takenAt']	= $exif->get( 'FileDateTime' );
			if( !strlen( trim( $image->title ) ) ){
				if( strlen( trim( $exif->get( 'ImageDescription' ) ) ) )
					$data['title']	= $exif->get( 'ImageDescription' );
				else
					$data['title']	= $upload->getFileName();
			}
			$this->modelImage->edit( $imageId, $data );
			return TRUE;
		}
		return FALSE;
	}
}
