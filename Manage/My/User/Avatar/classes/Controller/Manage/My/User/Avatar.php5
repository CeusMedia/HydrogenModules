<?php
class Controller_Manage_My_User_Avatar extends CMF_Hydrogen_Controller{

	protected $userId;

	protected function __onInit(){
		$this->userId		= $this->env->getSession()->get( 'userId' );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		if( !file_exists( $this->moduleConfig->get( 'path.images' ) ) )
			FS_Folder_Editor::createFolder( $this->moduleConfig->get( 'path.images' ) );
	}

	public function index(){
		$avatar		= $this->modelAvatar->getByIndex( 'userId', $this->userId );
		$model		= new Model_User( $this->env );
		$user		= $model->get( $this->userId );

		$this->addData( 'config', $this->moduleConfig );
		$this->addData( 'user', $user );
		$this->addData( 'avatar', $avatar );					//
	}

	public function remove(){
		$this->modelAvatar->removeByIndex( 'userId', $this->userId );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}

	public function upload(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
//		$words		= (object) $this->getWords( 'update' );

		$logic		= new Logic_Upload( $this->env );
		$maxSize	= Alg_UnitParser::parse( $this->moduleConfig->get( 'maxSize' ), 'M' );
		$maxSize	= Logic_Upload::getMaxUploadSize( array( 'config' => $maxSize ) );
		$logic->setUpload( $request->get( 'upload' ) );
		if( !$logic->checkSize( $maxSize ) ){
			$messenger->noteError( "Die Datei ist zu groß." );
		}
		else{
			$maxSize	= 256;
			$path		= $this->moduleConfig->get( 'path.images' );
			$fileName	= $this->userId.'_'.md5( time() ).'.'.$logic->getExtension( TRUE );
			$logic->saveTo( $path.$fileName );
			$image		= new UI_Image( $path.$fileName );
			$processor	= new UI_Image_Processing( $image );
			$width		= (int) $image->getWidth();
			$height		= (int) $image->getHeight();
			$size		= min( $width, $height );
			$offsetX	= (int) floor( ( $width - $size ) / 2 );
			$offsetY	= (int) floor( ( $height - $size ) / 2 );
			$processor->crop( $offsetX, $offsetY, $size, $size );
			if( $size > $maxSize )
				$processor->resize( $maxSize, $maxSize );
			$image->save();
			$avatar	= $this->modelAvatar->getByIndex( 'userId', $this->userId );
			if( $avatar ){
				unlink( $path.$avatar->filename );
				$this->modelAvatar->remove( $avatar->userAvatarId );
			}
			$data		= array(
				'userId'	=> $this->userId,
				'filename'	=> $fileName,
				'createdAt'	=> time(),
			);
			$this->modelAvatar->add( $data );
			$messenger->noteSuccess( 'Das Bild wurde gespeichert.' );
		}
		$this->restart( NULL, TRUE );
	}
}
?>
