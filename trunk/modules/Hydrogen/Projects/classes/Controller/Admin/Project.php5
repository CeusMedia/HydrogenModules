<?php
class Controller_Admin_Project extends CMF_Hydrogen_Controller
{
	public function add()
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$title			= $request->get( 'title' );
		$model			= new Model_Project( $this->env );
		if( $request->get( 'doAdd' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( 'Title is missing.' );
			else
			{
				if( $model->getAll( array( 'title' => $title ) ) )
					$messenger->noteError( 'Already exists: '.$label );
				else
				{
					$data	= array(
						'title'		=> $title,
						'timestamp'	=> time(),
					);
					$model->add( $data );
					$messenger->noteSuccess( 'Added: '.$title );
					$this->restart( 'test/table' );
				}
			}
		}
		$this->view->setData( array( 'title' => $title ) );
	}

	public function delete( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Project( $this->env );
		$data			= $model->get( $projectId );
		if( !$data )
		{
			$messenger->noteError( 'Invalid ID: '.$projectId );
			return $this->redirect( 'test' );
		}
		$model->remove( $projectId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( 'test/table' );
	}

	public function edit( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$title			= $request->get( 'title' );
		$model			= new Model_Project( $this->env );

		if( $request->get( 'doEdit' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( 'Title is missing.' );
			else
			{
				if( $model->getAll( array( 'title' => $title, 'projectId' => '!='.$projectId ) ) )
					$messenger->noteError( 'Already exists: '.$title );
				else
				{
					$data	= array(
						'title'		=> $title,
						'timestamp'	=> time(),
					);
					$model->edit( $projectId, $data );
					$messenger->noteSuccess( 'Updated: '.$title );
					$this->restart( 'test/table' );
				}
			}
		}
		$this->view->setData(
			array(
				'projectId'	=> $projectId,
				'project'	=> $model->get( $projectId ),
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
