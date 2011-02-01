<?php
class Controller_Admin_Project extends CMF_Hydrogen_Controller
{
	public function add()
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->env->getLanguage()->getWords( 'admin/project' );

		$title			= $request->get( 'title' );
		$description		= $request->get( 'description' );
		$status			= $request->get( 'status' );
		$model			= new Model_Project( $this->env );
		if( $request->get( 'doAdd' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( $words['add']['msgErrorTitleEmpty'] );
			else
			{
				if( $model->getAll( array( 'title' => $title ) ) )
					$messenger->noteError( $words['add']['msgErrorTitleNotUnique'], $title );
				else
				{
					$data	= array(
						'title'		=> $title,
						'description'	=> $description,
						'status'	=> $status,
						'createdAt'	=> time(),
					);
					$model->add( $data );
					$messenger->noteSuccess( $words['add']['msgSuccess'], $title );
					$this->restart( 'admin/project' );
				}
			}
		}
		$this->view->addData( 'title', $title );
		$this->view->addData( 'description', $description );
		$this->view->addData( 'status', $status );
	}

	public function remove( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->env->getLanguage()->getWords( 'admin/project' );

		$model			= new Model_Project( $this->env );
		$project		= $model->get( $projectId );
		if( !$project ){
			$messenger->noteError( $words['remove']['msgErrorInvalidId'] );
			$this->restart( './admin/project' );
		}
		$model->remove( $projectId );
		$messenger->noteSuccess( $words['remove']['msgSuccess'], $project->title );
		$this->restart( './admin/project' );
	}

	public function edit( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->env->getLanguage()->getWords( 'admin/project' );

		$title			= $request->get( 'title' );
		$description		= $request->get( 'description' );
		$status			= $request->get( 'status' );
		$model			= new Model_Project( $this->env );
		$project		= $model->get( $projectId );
		if( !$project ){
			$messenger->noteError( $words['edit']['msgErrorInvalidId'] );
			$this->restart( './admin/project' );
		}

		if( $request->get( 'doEdit' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( $words['edit']['msgErrorTitleEmpty'] );
			else
			{
				if( $model->getAll( array( 'title' => $title, 'projectId' => '!='.$projectId ) ) )
					$messenger->noteError( $words['edit']['msgErrorTitleNotUnique'], $title );
				else
				{
					$data	= array(
						'title'		=> $title,
						'description'	=> $description,
						'status'	=> $status,
						'modifiedAt'	=> time(),
					);
					$messenger->noteSuccess( $words['edit']['msgSuccess'], $title );
					$this->restart( './admin/project' );
				}
			}
		}
		$this->view->setData(
			array(
				'projectId'	=> $project->projectId,
				'project'	=> $project,
			)
		);
	}

	public function filter()
	{
		$this->env->getMessenger()->noteSuccess( "Tests have been filtered." );
		$this->restart( 'test/table' );
	}

	public function index()
	{
		$model	= new Model_Project( $this->env );
		$this->view->setData( array( 'projects' => $model->getAll() ) );
#		$this->setData( $model->getAll(), 'list' );
	}
}
?>
