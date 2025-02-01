<?php

use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Company_Branch extends Controller
{
	protected Logic_Frontend $frontend;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Branch $modelBranch;
	protected Model_Company $modelCompany;
	protected Model_Branch_Image $modelImage;
	protected Model_Branch_Tag $modelTag;

	public static function ___onRemoveCompany( Environment $env, $module, $content, $data = [] )
	{
		$modelBranch	= new Model_Branch( $env );
		$modelImage		= new Model_Branch_Image( $env );
		$modelTag		= new Model_Branch_Tag( $env );
		$frontend		= Logic_Frontend::getInstance( $env );
		if( isset( $data['companyId'] ) ){
			if( ( $companyId = $data['companyId'] ) ){
				$branches	= $modelBranch->getAllByIndex( 'companyId', $companyId );
				foreach( $branches as $branch ){
					$images	= $modelImage->getAllByIndex( 'branchId', $branch->branchId );
					foreach( $images as $image ){
						unlink( $frontend->getPath().'images/branches/'.$image->filename );
						$modelImage->remove( $image->branchImageId );
					}
					$modelTag->removeByIndex( 'branchId', $branch->branchId );
				}
				$modelBranch->remove( $branch->branchId );
			}
		}
	}

	public function activate( int|string $branchId ): void
	{
		$this->modelBranch->edit( $branchId, [
			'status'		=> 2,
			'modifiedAt'	=> time(),
		] );
		$branch		= $this->modelBranch->get( $branchId );
		$this->messenger->noteSuccess( 'Filiale "%s" aktiviert.', $branch->title );
		$this->restart( './manage/company/branch/edit/'.$branchId );
	}

	public function deactivate( int|string $branchId ): void
	{
		$this->modelBranch->edit( $branchId, [
			'status'		=> -2,
			'modifiedAt'	=> time(),
		] );
		$branch		= $this->modelBranch->get( $branchId );
		$this->messenger->noteSuccess( 'Filiale "%s" deaktiviert.', $branch->title );
		$this->restart( './manage/company/branch/edit/'.$branchId );
	}

	public function reject( int|string $branchId ): void
	{
		$this->modelBranch->edit( $branchId, [
			'status'		=> -1,
			'modifiedAt'	=> time(),
		] );
		$branch		= $this->modelBranch->get( $branchId );
		$this->messenger->noteSuccess( 'Filiale "%s" deaktiviert.', $branch->title );
		$this->restart( './manage/company/branch' );
	}

	public function add( $companyId = NULL ): void
	{
		$words			= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' );
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorNoTitle );
			else if( $this->modelBranch->getAll( ['title' => $data['title']] ) )
				$this->messenger->noteError( $words->errorTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorNoCompany );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->errorNoCity );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->errorNoPostcode );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->errorNoStreet );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->errorNoNumber );

			if( !$this->messenger->gotError() ){
				$data['createdAt']	= time();
				$branchId	= $this->modelBranch->add( $data );
				$this->messenger->noteSuccess( 'Added: '.$data['title'] );
				$this->modelBranch->extendWithGeocodes( $branchId );
				$this->restart( './manage/company/branch/'.$branchId );
			}
		}
		$data	= new stdClass();
		if( $companyId ){
			//  @todo check ownage of company!
			foreach( $this->modelCompany->get( $companyId ) as $key => $value )
				$data->$key	= $value;
		}
		else{
			foreach( $this->modelBranch->getColumns() as $column )
				$data->$column	= htmlentities ( $this->request->get( $column ) );
		}
		$this->view->addData( 'branch', $data );
		$this->view->setData( array( 'companies' => $this->modelCompany->getAll() ) );
	}

	public function addImage( int|string $branchId ): void
	{
		try{
			$image		= $this->request->get( 'image' );
			$imagePath	= $this->frontend->getPath( 'images' ).'branches/';		//  @todo to configuration
			FolderEditor::createFolder( $imagePath, 0777 );
			$upload		= new Logic_Upload( $this->env );
			$upload->setUpload( $image );				//  @todo handle upload errors before
			if( !$upload->checkIsImage() )
				$this->messenger->noteError( 'Das ist kein Bild.' );
			else if( !$upload->checkSize( 1048576 ) )							//  @todo to configuration
				$this->messenger->noteError( 'Das Bild ist zu groß.' );
			else{
				$extension	= pathinfo( $image['name'], PATHINFO_EXTENSION );
				$imageName	= $branchId.'_'.md5( time() ).'.'.$extension;
				$upload->saveTo( $imagePath.$imageName );
				$data	= array(
					'branchId'		=> $branchId,
					'filename'		=> $imageName,
					'title'			=> $this->request->get( 'image_title' ),
					'uploadedAt'	=> time()
				);
				$this->modelImage->add( $data );
//				$this->modelBranch->edit( $branchId, ['modifiedAt' => time()] );
				$this->messenger->noteSuccess( 'Das Bild wurde hochgeladen und gespeichert.' );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
		}

/*		if( $image['error'] ){
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
			$handler		= new \CeusMedia\Common\Net\HTTP\UploadErrorHandler();
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
		$messenger->noteSuccess( 'Bild erfolgreich hochgeladen.' );*/
		$this->restart( './manage/company/branch/edit/'.$branchId );
	}

	public function addTag( int|string $branchId ): void
	{
		$this->checkBranch( $branchId );
		$tags		= explode( " ", trim( $this->request->get( 'tags' ) ) );
		$list		= [];
		if( $tags ){
			foreach( $tags as $tag ){
				$indices	= [
					'branchId'	=> $branchId,
					'label'		=> $tag
				];
				if( !$this->modelTag->getByIndices( $indices ) ){
					$indices['createdAt']	= time();
					$this->modelTag->add( $indices );
					$list[]	= $tag;
				}
			}
		}
		$this->restart( 'manage/company/branch/edit/'.$branchId );
	}

	public function edit( int|string $branchId ): void
	{
		$config			= $this->env->getConfig();
		$words			= (object) $this->getWords( 'msg' );
	//	$modelImage		= new Model_Branch_Image( $this->env );

		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' );
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorNoTitle );
			else if( $this->modelBranch->getAll( ['title' => $data['title'], 'branchId' => '!= '.$branchId] ) )
				$this->messenger->noteError( $words->errorTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorNoCompany );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->errorNoCity );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->errorNoPostcode );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->errorNoStreet );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->errorNoNumber );

			if( !$this->messenger->gotError() ){
				$data['modifiedAt']	= time();
				$this->modelBranch->edit( $branchId, $data );
				$this->messenger->noteSuccess( 'Updated: '.$data['title'] );
				$this->modelBranch->extendWithGeocodes( $branchId );
				$this->restart( './manage/company/branch/'.$branchId );
			}
		}
		$branch				= $this->modelBranch->get( $branchId );
		$branch->tags		= $this->modelTag->getAllByIndex( 'branchId', $branchId );
		$branch->images		= $this->modelImage->getAllByIndex( 'branchId', $branchId );
		$branch->company	= $this->modelCompany->getByIndex( 'companyId', $branch->companyId );
		$this->view->setData(
			array(
				'branch'	=> $branch,
				'companies' => $this->modelCompany->getAll( [], ['title' => 'ASC'] )
			)
		);
		$this->view->addData( 'images', [] );//$modelImage->getAllByIndex( 'branchId', $branchId ) );
		$this->view->addData( 'companies', $this->modelCompany->getAll() );				//  @todo deliver only user related companies!!!
		$this->addData( 'frontend', $this->frontend );
	}

	public function filter(): void
	{
/*		$this->messenger->noteSuccess( "Companies have been filtered." );
*/		$this->restart( './manage/company/branch' );
	}

	public function index( $branchId = NULL ): void
	{
		if( $branchId )
			$this->restart( 'edit/'.$branchId, TRUE );
		$branches	= $this->modelBranch->getAll();
		foreach( $branches as $nr => $branch )
			$branches[$nr]->company	= $this->modelCompany->get( $branch->companyId );
		$this->view->addData( 'branches', $branches );
	}

	public function remove( int|string $branchId ): void
	{
		$branch	= $this->modelBranch->get( $branchId );
		if( !$branch ){
			$this->messenger->noteError( 'Invalid ID: '.$branchId );
			$this->restart( NULL, TRUE );
		}
		$images	= $this->modelImage->getAllByIndex( 'branchId', $branchId );
		foreach( $images as $image ){
			unlink( $this->frontend->getPath().'images/branches/'.$image->filename );
			$this->modelImage->remove( $image->branchImageId );
		}
		$this->modelTag->removeByIndex( 'branchId', $branchId );
		$this->modelBranch->remove( $branchId );
		$this->messenger->noteSuccess( 'Removed: '.$branch->title );
		$this->restart( './manage/company/branch' );
	}

	public function removeImage( int|string $branchId, int|string $imageId ): void
	{
		$image			= $this->modelImage->get( $imageId );
		if( !$image )
			$this->messenger->noteFailure( 'Invalid imageId' );
		if( !$this->messenger->gotError() ){
			$imagePath	= $this->frontend->getPath().'images/branches/';		//  @todo to configuration
			@unlink( $imagePath.$image->filename );
			$this->modelImage->remove( $imageId );
			$this->messenger->noteSuccess( 'Das Bild wurde entfernt.' );
		}
		$this->restart( './manage/company/branch/edit/'.$branchId );
	}

	public function removeTag( int|string $branchTagId ): void
	{
		$modelTag	= new Model_Branch_Tag( $this->env );
		$tag		= $this->modelTag->get( $branchTagId );
		if( $tag )
			$this->modelTag->remove( $branchTagId );
		$this->restart( 'manage/company/branch/edit/'.$tag->branchId );
	}

	protected function __onInit(): void
	{
		$this->modelBranch		= new Model_Branch( $this->env );
		$this->modelCompany		= new Model_Company( $this->env );
		$this->modelImage		= new Model_Branch_Image( $this->env );
		$this->modelTag			= new Model_Branch_Tag( $this->env );
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
	}

	protected function checkBranch( int|string $branchId ): object
	{
		$branch	= $this->modelBranch->get( $branchId );
		if( NULL === $branch ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorBranchInvalid );
			$this->restart( NULL, TRUE );
		}
		return $branch;
	}
}
