<?php

use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Branch extends Controller
{
	public function activate( $branchId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, ['status' => 1] );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" aktiviert.' );
		$this->restart( NULL, TRUE );
	}

	public function deactivate( $branchId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, ['status' => -1] );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" deaktiviert.' );
		$this->restart( NULL, TRUE );
	}

	public function addImage( $branchId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$image			= $request->get( 'image' );

		if( $image['error'] ){
			$words			= $this->env->getLanguage()->getWords( 'main' );
			$messages		= [
				UPLOAD_ERR_INI_SIZE		=> $words['upload-errors']['UPLOAD_ERR_INI_SIZE'],
				UPLOAD_ERR_FORM_SIZE	=> $words['upload-errors']['UPLOAD_ERR_FORM_SIZE'],
				UPLOAD_ERR_PARTIAL		=> $words['upload-errors']['UPLOAD_ERR_PARTIAL'],
				UPLOAD_ERR_NO_FILE		=> $words['upload-errors']['UPLOAD_ERR_NO_FILE'],
				UPLOAD_ERR_NO_TMP_DIR	=> $words['upload-errors']['UPLOAD_ERR_NO_TMP_DIR'],
				UPLOAD_ERR_CANT_WRITE	=> $words['upload-errors']['UPLOAD_ERR_CANT_WRITE'],
				UPLOAD_ERR_EXTENSION	=> $words['upload-errors']['UPLOAD_ERR_EXTENSION'],
			];
			$handler		= new UploadErrorHandler();
			$handler->setMessages( $messages );
			$handler->handleErrorFromUpload( $image );
		}
		$model	= new Model_Branch_Image( $this->env );

		$imageName	= $branchId.'_'.md5( time() ).'.'.pathinfo( $image['name'], PATHINFO_EXTENSION );
		$imagePath	= './images/branches/';
		FolderEditor::createFolder( $imagePath, 0777 );
		if( !@move_uploaded_file( $image['tmp_name'], $imagePath.$imageName ) )
			throw new RuntimeException( 'Bilddatei konnte nicht im Pfad "'.$imagePath.'" gespeichert werden.' );
		$data	= array(
			'branchId'		=> $branchId,
			'filename'		=> $imageName,
			'title'			=> $request->get( 'image_title' ),
			'uploadedAt'	=> time()
		);
		$model->add( $data );
		$messenger->noteSuccess( 'Bild erfolgreich hochgeladen.' );
		$this->restart( 'edit/'.$branchId, TRUE );
	}

	public function removeImage( $branchId, $imageId ){
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch_Image( $this->env );
		$image			= $model->get( $imageId );
		if( !$image )
			$messenger->noteFailure( 'Invalid imageId' );
		if( !$messenger->gotError() ){
			@unlink( './images/branches/'.$image->filename);
			$model->remove( $imageId );
			$messenger->noteSuccess( 'Das Bild "'.$image->title.'" wurde entfernt.' );
		}
		$this->restart( 'edit/'.$branchId, TRUE );
	}

	public function add(){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'add' );
		$model			= new Model_Branch( $this->env );
		$data			= $request->getAllFromSource( 'POST' );

		if( $request->get( 'doAdd' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( ['title' => $data['title']] ) )
				$messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$messenger->noteError( $words->msgNoCompany );
			if( empty( $data['city'] ) )
				$messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$messenger->noteError( $words->msgNoNumber );

			if( !$messenger->gotError() ){
				$data['createdAt']	= time();
				$model->add( $data );
				$messenger->noteSuccess( 'Added: '.$data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
		$data	= new stdClass();
		foreach( $model->getColumns() as $column )
			$data->$column	= htmlentities ( $request->get( $column ) );
		$this->view->addData( 'branch', $data );

		$model		= new Model_Company( $this->env );
		$this->view->setData( array( 'companies' => $model->getAll() ) );
		}

	public function delete( $branchId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$data			= $model->get( $branchId );
		if( !$data ){
			$messenger->noteError( 'Invalid ID: '.$branchId );
			$this->restart( NULL, TRUE );
			return;
		}
		$model->remove( $branchId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( NULL, TRUE );
	}

	public function edit( $branchId ){
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$data			= $request->getAllFromSource( 'POST' );
		$modelBranch	= new Model_Branch( $this->env );
		$modelCompany	= new Model_Company( $this->env );
		$modelImage		= new Model_Branch_Image( $this->env );

		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $modelBranch->getAll( ['title' => $data['title'], 'branchId' => '!= '.$branchId] ) )
				$messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$messenger->noteError( $words->msgNoCompany );
			if( empty( $data['city'] ) )
				$messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$messenger->noteError( $words->msgNoNumber );

			if( !$messenger->gotError() ){
				$data['modifiedAt']	= time();
				$modelBranch->edit( $branchId, $data );
				$messenger->noteSuccess( 'Updated: '.$data['title'] );
				$modelBranch->extendWithGeocodes( $branchId );
				$this->restart( NULL, TRUE );
			}
		}
		$branch			= $modelBranch->get( $branchId );
		$branch->images	= $modelImage->getAllByIndex( 'branchId', $branchId );
		if( $config->get( 'module.companies' ) ){
			$modelCompany		= new Model_Company( $this->env );
			$branch->company	= $modelCompany->getAllByIndex( 'companyId', $branch->companyId );
		}
		$this->view->setData(
			array(
				'branch'	=> $branch,
				'companies' => $modelCompany->getAll( NULL, ['title' => 'ASC'] )
			)
		);
		$this->view->addData( 'images', $modelImage->getAllByIndex( 'branchId', $branchId ) );
		$this->view->addData( 'companies', $modelCompany->getAll() );
	}

	public function filter(){
		$this->env->getMessenger()->noteSuccess( "Companies have been filtered." );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$model		= new Model_Branch( $this->env );
		$branches	= $model->getAll();
		$model		= new Model_Company( $this->env );
		foreach( $branches as $nr => $branch )
			$branches[$nr]->company	= $model->get( $branch->companyId );
		$this->view->addData( 'branches', $branches );
		$this->setData( $model->getAll(), 'list' );
	}
}
