<?php
/**
 *	Survey Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Survey Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		Controller_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Survey extends Controller_Abstract {

	/**	@var		Environment		$env		Environment instance */
	protected $env;

	/**
	 *	Returns data of a run or survey.
	 *	@access		public
	 *	@param		integer		$surveyId		ID of survey to return if neither user ID nor run ID is set
	 *	@param		integer		$userId			ID of user to return run of
	 *	@param		integer		$runId			ID of run to return
	 *	@return		object|integer				survey or run data object, -1: no user run found, -2: no run found
	 */
	public function get( $surveyId, $userId = NULL, $runId = NULL ){
		if( $runId ){
			$model		= new Model_Survey_Run( $this->env );
			$run		= $model->get( $runId );
			if( $run ){
				$run->user	= $this->env->getLogic()->getUserData( $run->userId );
				return $run;
			}
			return -2;
		}
		else if( $userId ){
			$model		= new Model_Survey_Run( $this->env );
			$run		= $model->getByIndex( 'userId', $userId );
			if( $run ){
				$run->user	= $this->env->getLogic()->getUserData( $run->userId );
				return $run;
			}
			return -1;
		}
		else{
			$model	= new Model_Survey( $this->env );
			return $model->get( $surveyId );
		}
	}

	/**
	 *	Returns list of surveys or runs.
	 *	@access		public
	 *	@param		integer		$surveyId		ID of survey to list runs of, otherwise list surveys
	 *	@return		array		List of survey or run data objects
	 */
	public function index( $surveyId = NULL ){
		if( $surveyId ){
			$model		= new Model_Survey_Run( $this->env );
			$userMap	= $this->env->getLogic()->getUserMap();										//  resolve users
			$runs		= $model->getAllByIndex( 'surveyId', $surveyId );							//  
			foreach( $runs as $nr => $run )
				$runs[$nr]->user	= $userMap[$run->userId];
			return $runs;
		}
		else{
			$model		= new Model_Survey( $this->env );
			$post		= $this->env->getRequest()->getAllFromSource( 'POST' );
			$conditions	= $post->has( 'conditions' ) ? $post->get( 'conditions' ) : array();
			$surveys	= $model->getAll( $conditions );
			$model		= new Model_Survey_Run( $this->env );
			foreach( $surveys as $nr => $survey )
				$surveys[$nr]->runs	= $model->countByIndex( 'surveyId', $survey->surveyId );
			return $surveys;
		}
	}

	/**
	 *	Remove a run or a survey and its runs.
	 *	@access		public
	 *	@param		integer		$surveyId		ID of survey to remove if no run ID set
	 *	@param		integer		$runId			ID of run to remove
	 *	@return		integer		1 for success, 0 for no effect
	 */
	public function remove( $surveyId, $runId = NULL ){
		if( $runId ){
			$model	= new Model_Survey_Run( $this->env );
			return $model->remove( $runId );
		}
		else{
			$model	= new Model_Survey_Run( $this->env );
			$model->removeByIndex( 'surveyId', $surveyId );
			$model	= new Model_Survey( $this->env );
			return $model->remove( $surveyId );
		}
	}

	/**
	 *	Stores data of a survey or run.
	 *	@access		public
	 *	@param		integer		$surveyId		ID of survey to set data for if no user ID set
	 *	@param		integer		$userId			ID of user to set run data for
	 *	@return		integer		1 for success
	 */
	public function set( $surveyId = NULL, $userId = NULL ){
		$post	= $this->env->getRequest()->getAllFromSource( 'POST' );
		try{
			if( $surveyId && $userId ){
				$model		= new Model_Survey_Run( $this->env );
				$indices	= array( 'surveyId' => $surveyId, 'userId' => $userId );
				$run		= $model->getByIndices( $indices );
				if( $run ){
					$data	= array(
						'page'			=> max( $run->page, $post->get( 'page' ) ),
						'data'			=> $post->get( 'data' ),
						'modifiedAt'	=> time()
					);
					$model->edit( $run->surveyRunId, $data );
					return 1;
				}
				else{
					$data	= array(
						'surveyId'	=> $surveyId,
						'userId'	=> $userId,
						'page'		=> $post->get( 'page' ),
						'data'		=> $post->get( 'data' ),
						'createdAt'	=> time(),
					);
					$model->add( $data );
					return 1;
				}

			}
			else if( $surveyId ){
				$model	= new Model_Survey( $this->env );
				$data	= array(
					'title'			=> $post->get( 'title' ),
					'pages'			=> $post->get( 'pages' ),
					'questions'		=> $post->get( 'questions' ),
					'status'		=> $post->get( 'status' ),
					'modifiedAt'	=> time()
				);
				return (int) (bool) $model->edit( $surveyId, $data );
			}
			else{
				$model	= new Model_Survey( $this->env );
				$data	= array(
					'title'		=> $post->get( 'title' ),
					'pages'		=> $post->get( 'pages' ),
					'questions'	=> $post->get( 'questions' ),
					'status'	=> 0,
					'createdAt'	=> time()
				);
				return (int) (bool) $model->add( $data );
			}
		}
		catch( Exception $e ) {
			return -105;
		}
	}
}
?>