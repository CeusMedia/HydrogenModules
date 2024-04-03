<?php

use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Image_Slider extends Controller
{
	protected Model_Image_Slider $modelSlider;
	protected Model_Image_Slide $modelSlide;
	protected Logic_Frontend $frontend;
	protected MessengerResource $messenger;
	protected HttpRequest $request;
	protected string $basePath;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
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

	/**
	 *	@param		string		$sliderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addSlide( string $sliderId ): void
	{
		$slider	= $this->checkSliderId( $sliderId );
		$words	= (object) $this->getWords( 'msg' );
		try{
			$image		= $this->request->get( 'image' );
			$indices	= [
				'sliderId'  => $sliderId,
				'source'    => $image['name']
			];
			if( $this->modelSlide->getByIndices( $indices ) ){
				$this->messenger->noteError( $words->errorSlideExists, $image['name'] );
				$this->restart( './edit/'.$sliderId, TRUE );
			}

			$handler	= new UploadErrorHandler();
			$handler->handleErrorFromUpload( $image );
			$target		= $this->basePath.$slider->path.$image['name'];

			$imageObject	= new Image( $image['tmp_name'] );
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

	/**
	 *	@param		string		$sliderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function demo( string $sliderId ): void
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

	/**
	 *	@param		string		$sliderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $sliderId ): void
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
					FolderEditor::renameFolder( $pathOld, $pathNew );
				}
				catch( Exception $e ){
					$this->messenger->noteError( $words->errorMovingSliderFailed, $e->getMessage() );
					$data['path']	= $slider->path;
				}
			}
			$this->modelSlider->edit( $sliderId, $data );
			$this->messenger->noteSuccess( $words->successSliderEdited, $data['title'] );
			$this->restart( NULL, TRUE );
		}
		$slider->slides	= $this->modelSlide->getAll( ['sliderId' => $sliderId], ['rank' => 'ASC'] );
		if( !$slider->slides )
			$this->messenger->noteNotice( $words->noticeNoSlidesYet );
		$this->addData( 'slider', $slider );
	}

	/**
	 *	@param		string		$slideId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editSlide( string $slideId ): void
	{
		$slide	= $this->checkSlideId( $slideId );
		$slider	= $this->checkSliderId( $slide->sliderId );
		$words	= (object) $this->getWords( 'msg' );

		$orders	= ['rank' => 'ASC', 'sliderSlideId' => 'ASC'];
		$slides	= $this->modelSlide->getAllByIndex( 'sliderId', $slide->sliderId, $orders );

		if( $this->request->has( 'save' ) ){
			$post	= $this->request->getAllFromSource( 'post', TRUE );
			$data	= [
				'status'	=> $post->get( 'status' ),
				'title'		=> $post->get( 'title' ),
				'content'	=> $post->get( 'content' ),
				'link'		=> $post->get( 'link' ),
				'rank'		=> $post->get( 'rank' ),
				'timestamp'	=> time(),
			];
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

	/**
	 *	@param		string		$sliderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function importSlides( string $sliderId ): void
	{
		$this->checkSliderId( $sliderId );
		$slides	= $this->modelSlide->getAll( ['sliderId' => $sliderId] );
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

	public function index(): void
	{
		$conditions	= [];
		$orders		= ['status' => 'DESC', 'title' => 'ASC'];
		$sliders	= $this->modelSlider->getAll( $conditions, $orders );
		foreach( $sliders as $slider )
			$slider->slides	= $this->modelSlide->getAll( ['sliderId' => $slider->sliderId], ['rank' => 'ASC'] );

		$this->addData( 'sliders', $sliders );
	}

	/**
	 *	@param		string			$slideId
	 *	@param		string|int		$moveBy
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function rankSlide( string $slideId, string|int $moveBy ): void
	{
		$slide	= $this->checkSlideId( $slideId );
		$this->checkSliderId( $slide->sliderId );
		$this->modelSlide->edit( $slideId, array(
			'rank'		=> $slide->rank + (int) $moveBy,
			'timestamp'	=> time()
		) );
		$this->reorderSlides( $slide->sliderId, $moveBy < 0 );
		$this->restart( 'edit/'.$slide->sliderId, TRUE );
	}

	/**
	 *	@param		string		$sliderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $sliderId ): void
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

	/**
	 *	@param		string		$slideId
	 *	@param		bool		$removeSourceBackup
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeSlide( string $slideId, bool $removeSourceBackup = FALSE ): void
	{
		$words	= (object) $this->getWords( 'msg' );
		$slide	= $this->checkSlideId( $slideId );
		$this->checkSliderId( $slide->sliderId );

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

	protected function __onInit(): void
	{
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->modelSlider	= new Model_Image_Slider( $this->env );
		$this->modelSlide	= new Model_Image_Slide( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();

		$pathImages		= $this->frontend->getPath( 'images' );
		if( !$this->frontend->hasModule( 'UI_Image_Slider' ) ){
			$this->messenger->noteFailure( 'Module "UI_Image_Slider" is not installed in frontend instance. Your request has been reset to start.' );
			$this->restart();
		}
		$pathSliders	= $this->frontend->getModuleConfigValue( 'UI_Image_Slider', 'path' );
		$this->addData( 'basePath', $this->basePath = $pathImages.$pathSliders );
	}

	/**
	 *	@param		string		$slideId
	 *	@return		object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkSlideId( string $slideId ): object
	{
		$slide		= $this->modelSlide->get( $slideId );
		if( !$slide ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorInvalidSlideId );
			$this->restart( NULL, TRUE );
		}
		return $slide;
	}

	/**
	 *	@param		string		$sliderId
	 *	@return		object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkSliderId( string $sliderId ): object
	{
		$slider		= $this->modelSlider->get( $sliderId );
		if( !$slider ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorInvalidSliderId );
			$this->restart( NULL, TRUE );
		}
		return $slider;
	}

	/**
	 *	@param		string		$sliderId
	 *	@param		bool		$takeNewerFirst
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function reorderSlides( string $sliderId, bool $takeNewerFirst = TRUE ): bool
	{
		$this->checkSliderId( $sliderId );
		$orders		= ['rank' => 'ASC', 'timestamp' => $takeNewerFirst ? 'DESC' : 'ASC'];
		$slides		= $this->modelSlide->getAllByIndex( 'sliderId', $sliderId, $orders );
		$changes	= 0;
		foreach( $slides as $nr => $slide ){
			if( (int) $slide->rank !== $nr + 1 ){
				$this->modelSlide->edit( $slide->sliderSlideId, ['rank' => $nr + 1] );
				$changes++;
			}
		}
		return (bool) $changes;
	}

	/**
	 *	@param		string		$sliderId
	 *	@param		string		$slideId
	 *	@param		string		$posX
	 *	@param		string		$posY
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function scaleImage( string $sliderId, string $slideId, string $posX = "center", string $posY = "center" ): bool
	{
		$slider	= $this->checkSliderId( $sliderId );
		$slide	= $this->checkSlideId( $slideId );
		$path	= $this->basePath;

		if( !file_exists( $path ) )
			throw new RuntimeException( 'Slider image file "'.$path.$slide->source.'" is not existing' );

		$slideWidth		= (int) $slider->width;
		$slideHeight	= (int) $slider->height;
		$image			= new Image( $path.$slider->path.$slide->source );
		if( $image->getWidth() === $slideWidth && $image->getHeight() === $slideHeight )			//  no need to scale or crop
			return FALSE;																			//  indicate to have done nothing

		if( !is_dir( $path.'source/' ) )															//  no backup folder found
			mkdir( $path.'source/' );																//  create backup folder
		if( !@copy( $path.$slider->path.$slide->source, $path.'source/'.$slide->source ) )			//  try to back up original image
			throw new RuntimeException( 'Slider image backup to path "'.$path.'source/" failed' );

		$processor	= new ImageProcessing( $image );											//  start image processor
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
