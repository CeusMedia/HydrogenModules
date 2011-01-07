<?php
class Controller_Test_Table extends CMF_Hydrogen_Controller
{
	public function add()
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$title			= $request->get( 'title' );
		$model			= new Model_Test_Table( $this->env );
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

	public function delete( $testId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Test_Table( $this->env );
		$data			= $model->get( $testId );
		if( !$data )
		{
			$messenger->noteError( 'Invalid ID: '.$testId );
			return $this->redirect( 'test' );
		}
		$model->remove( $testId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( 'test/table' );
	}

	public function edit( $testId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$title			= $request->get( 'title' );
		$model			= new Model_Test_Table( $this->env );

		if( $request->get( 'doEdit' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( 'Title is missing.' );
			else
			{
				if( $model->getAll( array( 'title' => $title, 'testId' => '!='.$testId ) ) )
					$messenger->noteError( 'Already exists: '.$title );
				else
				{
					$data	= array(
						'title'		=> $title,
						'timestamp'	=> time(),
					);
					$model->edit( $testId, $data );
					$messenger->noteSuccess( 'Updated: '.$title );
					$this->restart( 'test/table' );
				}
			}
		}
		$this->view->setData(
			array(
				'testId'	=> $testId,
				'test'		=> $model->get( $testId ),
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
		$model	= new Model_Test_Table( $this->env );
		$this->view->setData( array( 'tests' => $model->getAll() ) );
#		$this->setData( $model->getAll(), 'list' );
	}
}
?>
