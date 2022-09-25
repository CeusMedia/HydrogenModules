<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Image_Slider extends Controller
{
	protected $modelSlider;
	protected $modelSlide;
	protected $frontend;
	protected $messenger;
	protected $request;
	protected $basePath;

	public function add()
	{
		$words	= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			if( file_exists( $this->basePath.$data['path'] ) ){
				$this->messenger->noteError( $words->errorPathExists );
			}
			else{
				$data['path']		= preg_replace( "/\/*$/", "", $data['path'] )."/";
				$data['createdAt']	= time();
				$sliderId	= $this->modelSlider->add( $data );
				$path		= $this->basePath.$data['path'];
				if( !file_exists( $path ) )
					mkdir( $path, 0770, TRUE );
				$this->messenger->noteSuccess( $words->successSliderAdded, $data['title'] );
				$this->restart( 'edit/'.$sliderId, TRUE );
			}
		}
		$this->addData( 'data', $this->request->getAll( NULL, TRUE ) );
	}

	public function addSlide( $sliderId )
	{
		$slider	= $this->checkSliderId( $sliderId );
		$words	= (object) $this->getWords( 'msg' );
		try{
			$image		= $this->request->get( 'image' );
			$indices	= array(
				'sliderId'  => $sliderId,
				'source'    => $image['name']
			);
			if( $this->modelSlide->getByIndices( $indices ) ){
				$this->messenger->noteError( $words->errorSlideExists, $image['name'] );
				$this->restart( './edit/'.$sliderId, TRUE );
			}

			$handler	= new Net_HTTP_UploadErrorHandler();
			$handler->handleErrorFromUpload( $image );
			$target		= $this->basePath.$slider->path.$image['name'];

			$imageObject	= new UI_Image( $image['tmp_name'] );
			$minWidth		= $slider->width / 2;
			$minHeight		= $slider->height / 2;
			$imageWidth		= $imageObject->getWidth();
			$imageHeight	= $imageObject->getHeight();
			if( $imageWidth < $minWidth || $imageHeight < $minHeight ){
				$message	= $words->errorImageTooSmall;
				$message	= sprintf( $message, $minWidth, $minHeight, $imageWidth, $imageHeight );
				$this->messenger->noteError( $message );
			}
			else{
				if( !file_exists( dirname( $target ) ) )
					mkdir( dirname( $target ), 0770, TRUE );
				if( !move_uploaded_file( $image['tmp_name'], $target ) ){
					$this->messenger->noteError( $words->errorUploadFailed );
				}
				else{
					$slideId	= $this->modelSlide->add( array(
						'sliderId'	=> $sliderId,
						'status'	=> 1,
						'source'	=> $image['name'],
						'title'		=> $this->request->get( 'title' ),
						'link'		=> $this->request->get( 'link' ),
						'rank'		=> $this->request->get( 'rank' ),
						'timestamp'	=> time(),
					) );
					try{
						$positionX	= $this->request->get( 'positionX' );
						$positionY	= $this->request->get( 'positionY' );
						$this->scaleImage( $sliderId, $slideId, $positionX, $positionY );
						$this->reorderSlides( $sliderId );
						$this->messenger->noteSuccess( $words->successSlideAdded, $image['name'] );
					}
					catch( Exception $e ){
						$this->modelSlide->remove( $slideId );
						$this->messenger->noteFailure( $words->errorScalingFailed, $image['name'], $e->getMessage() );
					}
				}
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( $e->getMessage() );
		}
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'edit/'.$sliderId, TRUE );
	}

	public function demo( $sliderId )
	{
		$slider	= $this->checkSliderId( $sliderId );
		if( !$this->modelSlide->getAllByIndex( 'sliderId', $sliderId ) ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorNoSlides, $slider->title );
			$this->restart( 'edit/'.$sliderId, TRUE );
		}
		$this->addData( 'slider', $this->modelSlider->get( $sliderId ) );
		$this->addData( 'sliderId', $sliderId );
	}

	public function edit( $sliderId )
	{
		$words	= (object) $this->getWords( 'msg' );
		$slider	= $this->checkSliderId( $sliderId );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['path']		= preg_replace( "/\/*$/", "", $data['path'] )."/";
			$data['modifiedAt']	= time();
			if( $slider->path !== $data['path'] ){
				$pathOld	= $this->basePath.$slider->path;
				$pathNew	= $this->basePath.$data['path'];
				try{
					Folder_Editor::renameFolder( $pathOld, $pathNew );
				}
				catch( Exception $e ){
					$this->messenger->noteError( $words->errorMovingSliderFailed );
					$data['path']	= $slider->path;
				}
			}
			$this->modelSlider->edit( $sliderId, $data );
			$this->messenger->noteSuccess( $words->successSliderEdited, $data['title'] );
			$this->restart( NULL, TRUE );
		}
		$slider->slides	= $this->modelSlide->getAll( array( 'sliderId' => $sliderId ), array( 'rank' => 'ASC' ) );
		if( !$slider->slides )
			$this->messenger->noteNotice( $words->noticeNoSlidesYet );
		$this->addData( 'slider', $slider );
	}

	public function editSlide( $slideId )
	{
		$slide	= $this->checkSlideId( $slideId );
		$slider	= $this->checkSliderId( $slide->sliderId );
		$words	= (object) $this->getWords( 'msg' );

		$orders	= array( 'rank' => 'ASC', 'sliderSlideId' => 'ASC' );
		$slides	= $this->modelSlide->getAllByIndex( 'sliderId', $slide->sliderId, $orders );

		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data	= array(
				'status'	=> $this->request->get( 'status' ),
				'title'		=> $this->request->get( 'title' ),
				'content'	=> $this->request->get( 'content' ),
				'link'		=> $this->request->get( 'link' ),
				'rank'		=> $this->request->get( 'rank' ),
				'timestamp'	=> time(),
			);
			$this->modelSlide->edit( $slideId, $data, FALSE );
			$this->reorderSlides( $slide->sliderId );
			$this->messenger->noteSuccess( $words->successSlideEdited );
			$this->restart( 'edit/'.$slide->sliderId, TRUE );
		}

		$this->addData( 'slideId', $slideId );
		$this->addData( 'slider', $slider );
		$this->addData( 'slide', $slide );
		$this->addData( 'slides', $slides );
	}

	public function importSlides( $sliderId )
	{
		$slider	= $this->checkSliderId( $sliderId );
		$slides	= $this->modelSlide->getAll( array( 'sliderId' => $sliderId ) );
		$list	= [];
		$index	= new DirectoryIterator( $this->basePath );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			$indices	= array( 'sliderId' => $sliderId, 'source' => $entry->getFilename() );
			if( $this->modelSlide->getByIndices( $indices ) )
				continue;
			$slideId	= $this->modelSlide->add( array(
				'sliderId'		=> $sliderId,
				'source'		=> $entry->getFilename(),
				'status'		=> '1',
				'rank'			=> count( $slides ) + count( $list ) + 1,
				'timestamp'		=> time(),
			) );
			try{
				$this->scaleImage( $sliderId, $slideId );
				$list[]	= HtmlTag::create( 'li', $entry->getFilename() );
			}
			catch( Exception $e ){
				$this->modelSlide->remove( $slideId );
				$this->messenger->noteFailure( 'Import of "'.$entry->getFilename().'" failed: '.$e->getMessage() );
			}
		}
		if( $list ){
			$this->messenger->noteSuccess( count( $list).' image imported: '.HtmlTag::create( 'ul', $list ) );
		}
		$this->restart( 'edit/'.$sliderId, TRUE );
	}

	public function index()
	{
		$conditions	= [];
		$orders		= array( 'status' => 'DESC', 'title' => 'ASC' );
		$sliders	= $this->modelSlider->getAll( $conditions, $orders );
		foreach( $sliders as $nr => $slider )
			$slider->slides	= $this->modelSlide->getAll( array( 'sliderId' => $slider->sliderId ), array( 'rank' => 'ASC' ) );

		$this->addData( 'sliders', $sliders );
	}

	public function rankSlide( $slideId, $moveBy )
	{
		$slide	= $this->checkSlideId( $slideId );
		$slider	= $this->checkSliderId( $slide->sliderId );
		$this->modelSlide->edit( $slideId, array(
			'rank'		=> $slide->rank + (int) $moveBy,
			'timestamp'	=> time()
		) );
		$this->reorderSlides( $slide->sliderId, $moveBy < 0 );
		$this->restart( 'edit/'.$slide->sliderId, TRUE );
	}

	public function remove( $sliderId )
	{
		$words	= (object) $this->getWords( 'msg' );
		$slider	= $this->checkSliderId( $sliderId );

		$slides	= $this->modelSlide->getAllByIndex( 'sliderId', $sliderId );
		foreach( $slides as $slide ){
			$this->modelSlide->remove( $slide->sliderSlideId );
			@unlink( $this->basePath.$slide->source );
			@unlink( $this->basePath.'source/'.$slide->source );
			@rmdir( $this->basePath.$slide->source );
		}
		$this->modelSlider->remove( $sliderId );
		$this->messenger->noteSuccess( $words->successSliderRemoved, $slider->title );
		$this->restart( NULL, TRUE );
	}

	public function removeSlide( $slideId, $removeSourceBackup = FALSE )
	{
		$words	= (object) $this->getWords( 'msg' );
		$slide	= $this->checkSlideId( $slideId );
		$slider	= $this->checkSliderId( $slide->sliderId );

		$this->modelSlide->remove( $slideId );
		$message	= $words->successSlideRemoved1;
		if( @unlink( $this->basePath.$slide->source ) ){
			$message	= $words->successSlideRemoved2;
			if( $removeSourceBackup && @unlink( $this->basePath.'source/'.$slide->source ) )
				$message	= $words->successSlideRemoved3;
		}
		$this->messenger->noteSuccess( $message );
		$this->reorderSlides( $slide->sliderId );
		$this->restart( 'edit/'.$slide->sliderId, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->modelSlider	= new Model_Image_Slider( $this->env );
		$this->modelSlide	= new Model_Image_Slide( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();

		$pathImages		= $this->frontend->getPath( 'images' );
		if( !$this->frontend->hasModule( 'UI_Image_Slider' ) ){
			$this->messenger->noteFailure( 'Module "UI_Image_Slider" is not installed in frontend instance. Your request has been reset to start.' );
			$this->restart( NULL );
		}
		$pathSliders	= $this->frontend->getModuleConfigValue( 'UI_Image_Slider', 'path' );
		$this->addData( 'basePath', $this->basePath = $pathImages.$pathSliders );
	}

	protected function checkSlideId( $slideId )
	{
		$slide		= $this->modelSlide->get( $slideId );
		if( !$slide ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorInvalidSlideId );
			$this->restart( NULL, TRUE );
		}
		return $slide;
	}

	protected function checkSliderId( $sliderId )
	{
		$slider		= $this->modelSlider->get( $sliderId );
		if( !$slider ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorInvalidSliderId );
			$this->restart( NULL, TRUE );
		}
		return $slider;
	}

	protected function reorderSlides( $sliderId, $takeNewerFirst = TRUE )
	{
		$this->checkSliderId( $sliderId );
		$orders		= array( 'rank' => 'ASC', 'timestamp' => $takeNewerFirst ? 'DESC' : 'ASC' );
		$slides		= $this->modelSlide->getAllByIndex( 'sliderId', $sliderId, $orders );
		$changes	= 0;
		foreach( $slides as $nr => $slide ){
			if( (int) $slide->rank !== $nr + 1 ){
				$this->modelSlide->edit( $slide->sliderSlideId, array( 'rank' => $nr + 1 ) );
				$changes++;
			}
		}
		return (bool) $changes;
	}

	protected function scaleImage( $sliderId, $slideId, $posX = "center", $posY = "center" )
	{
		$slider	= $this->checkSliderId( $sliderId );
		$slide	= $this->checkSlideId( $slideId );
		$path	= $this->basePath;

		if( !file_exists( $path ) )
			throw new RuntimeException( 'Slider image file "'.$path.$slide->source.'" is not existing' );

		$slideWidth		= (int) $slider->width;
		$slideHeight	= (int) $slider->height;
		$image			= new UI_Image( $path.$slider->path.$slide->source );
		if( $image->getWidth() === $slideWidth && $image->getHeight() === $slideHeight )			//  no need to scale or crop
			return FALSE;																			//  indicate to have done nothing

		if( !is_dir( $path.'source/' ) )															//  no backup folder found
			mkdir( $path.'source/' );																//  create backup folder
		if( !@copy( $path.$slider->path.$slide->source, $path.'source/'.$slide->source ) )			//  try to backup original image
			throw new RuntimeException( 'Slider image backup to path "'.$path.'source/" failed' );

		$processor	= new UI_Image_Processing( $image );											//  start image processor
		if( $slideWidth / $slideHeight > $image->getWidth() / $image->getHeight() )					//  slide is broader than image
			$processor->scaleToRange( $slideWidth, $slideHeight, $slideWidth, $slideHeight * 5 );	//  scale image to match in width
		else																						//  otherwise
			$processor->scaleToRange( $slideWidth, $slideHeight, $slideWidth * 5, $slideHeight );	//  scale image to match in height

		$image->save();																				//  save image to source file

		$startX	= 0;
		$startY	= 0;
		if( $posX == "center" )
			$startX = (int) floor( ( $image->getWidth() - $slideWidth ) / 2 );						//  calculate top offset
		else if( $posX == "bottom" )
			$startX	= $image->getWidth() - $slideWidth;												//  calculate top offset
		if( $posY == "center" )
			$startY = (int) floor( ( $image->getHeight() - $slideHeight ) / 2 );					//  calculate left offset
		else if( $posY == "bottom" )
			$startY	= $image->getHeight() - $slideHeight;											//  calculate left offset

		$processor->crop( $startX, $startY, $slideWidth, $slideHeight );							//  crop image
		$image->save();																				//  save image to source file
		return TRUE;																				//  indicate success
	}
}
