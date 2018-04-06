<?php
class Controller_Manage_My_User_Avatar extends CMF_Hydrogen_Controller{

	protected $userId;

	protected function __onInit(){
		$this->userId		= $this->env->getSession()->get( 'userId' );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		$this->pathImages	= $this->moduleConfig->get( 'path.images' );

		if( !file_exists( $this->pathImages ) )
			FS_Folder_Editor::createFolder( $this->pathImages );
	}

	public function index(){
		$avatar		= $this->modelAvatar->getByIndex( 'userId', $this->userId );
		$model		= new Model_User( $this->env );
		$user		= $model->get( $this->userId );

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'user', $user );
		$this->addData( 'avatar', $avatar );					//
	}

	public function remove(){
		$avatar		= $this->modelAvatar->getByIndex( 'userId', $this->userId );
		@unlink( $this->pathImages.$this->userId.'_'.$avatar->filename );
		@unlink( $this->pathImages.$this->userId.'__'.$avatar->filename );
		@unlink( $this->pathImages.$this->userId.'___'.$avatar->filename );
		$this->modelAvatar->removeByIndex( 'userId', $this->userId );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}

	public function upload(){
		$words		= (object) $this->getWords( 'msg' );
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
//		$words		= (object) $this->getWords( 'update' );

		$logic		= new Logic_Upload( $this->env );
		$maxSize	= Alg_UnitParser::parse( $this->moduleConfig->get( 'image.upload.maxFileSize' ), 'M' );
		$maxSize	= Logic_Upload::getMaxUploadSize( array( 'config' => $maxSize ) );
		$logic->setUpload( $request->get( 'upload' ) );
		if( !$logic->checkSize( $maxSize ) ){
			$messenger->noteError( $words->errorFileTooLarge );
		}
		else{
			$fileName	= Alg_ID::uuid().'.'.$logic->getExtension( TRUE );
			$logic->saveTo( $this->pathImages.$this->userId.'_'.$fileName );									//  save originally uploaded image
			try{
				/*  --  PROCESS AND SAVE NEW AVATAR IMAGES  -- */
				$image		= new UI_Image( $this->pathImages.$this->userId.'_'.$fileName );
				$processor	= new UI_Image_Processing( $image );
				$width		= (int) $image->getWidth();
				$height		= (int) $image->getHeight();
				$size		= min( $width, $height );
				$offsetX	= (int) floor( ( $width - $size ) / 2 );
				$offsetY	= (int) floor( ( $height - $size ) / 2 );
				$processor->crop( $offsetX, $offsetY, $size, $size );
				$sizeLarge	= $this->moduleConfig->get( 'image.size.large' );
				$sizeMedium	= $this->moduleConfig->get( 'image.size.medium' );
				$sizeSmall	= $this->moduleConfig->get( 'image.size.small' );
				if( $size > $sizeLarge )
					$processor->resize( $sizeLarge, $sizeLarge );
				$image->save( $this->pathImages.$this->userId.'_'.$fileName );
				if( $size > $sizeMedium )
					$processor->resize( $sizeMedium, $sizeMedium );
				$image->save( $this->pathImages.$this->userId.'__'.$fileName );
				if( $size > $sizeSmall )
					$processor->resize( $sizeSmall, $sizeSmall );
				$image->save( $this->pathImages.$this->userId.'___'.$fileName );

				/*  --  REMOVE OLD AVATAR IMAGES AND DATABASE ENTRY  -- */
				if( ( $avatar	= $this->modelAvatar->getByIndex( 'userId', $this->userId ) ) ){
					@unlink( $this->pathImages.$this->userId.'_'.$avatar->filename );
					@unlink( $this->pathImages.$this->userId.'__'.$avatar->filename );
					@unlink( $this->pathImages.$this->userId.'___'.$avatar->filename );
					$this->modelAvatar->remove( $avatar->userAvatarId );
				}

				/*  --  SAVE DATABASE ENTRY OF NEW AVATAR  -- */
				$this->modelAvatar->add( array(
					'userId'		=> $this->userId,
					'status'		=> 1,
					'filename'		=> $fileName,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				) );
				$messenger->noteSuccess( $words->successImageSaved );
			}
			catch( Exception $e ){
				@unlink( $path.$this->userId.'_'.$filename );
				$this->callHook( 'Env', 'logException', $this, $e );
				$message	=  'Bei der Bildverarbeitung ist ein <abbr title="%s">Fehler</abbr> aufgetreten.';
				$messenger->noteFailure( $message, $e->getMessage() );
			}
		}
		$this->restart( NULL, TRUE );
	}
}
?>
