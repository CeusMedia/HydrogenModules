<?php
class Controller_Manage_Gallery extends CMF_Hydrogen_Controller{
	
	protected $path;
	
	public function __onInit(){
		$config		= $this->env->getConfig();
#		$this->path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
	}
	
	public function add( $folder = NULL ){
		$this->addData( 'folder', $folder );
		$request	= $this->env->getRequest();
		if( $request->has( 'add' ) ){
			$model	= new Model_Gallery( $this->env );
			$title	= $request->get( 'title' );
			$data	= array(
				'folder'	=> $request->get( 'folder' ),
				'title'		=> $title,
				'content'	=> $request->get( 'content' ),
				'createdAt'	=> time(),
				'status'	=> 0,
			);
			$galleryId	= $model->add( $data, FALSE );
			$this->env->getMessenger()->noteSuccess( 'Gallery "'.$title.'" imported.' );
		}
	}

	public function index( $galleryId = NULL ){
		
	}

	public function edit( $galleryId ){
		
	}
}
?>