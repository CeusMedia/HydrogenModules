<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Bug extends Controller{

	const CHANGE_DETAILS		= 1;
	const CHANGE_MANAGER		= 2;
	const CHANGE_DEVELOPER		= 3;
	const CHANGE_TYPE			= 4;
	const CHANGE_SEVERITY		= 5;
	const CHANGE_PRIORITY		= 6;
	const CHANGE_STATUS			= 7;
	const CHANGE_PROGRESS		= 8;
	const CHANGE_NOTE			= 9;
	const CHANGE_ATTACHMENT		= 10;
	const CHANGE_PATCH			= 11;

	protected $filters	= array(
		'bugId',
		'type',
		'severity',
		'priority',
		'status',
		'title',
		'order',
		'direction',
		'limit'
	);

	public function add(){
		$request	= $this->env->request;
		if( $request->has( 'save' ) ){
			$model		= new Model_Bug( $this->env );
			$data		= array(
				'reporterId'	=> $this->env->getSession()->get( 'auth_user_id' ),
				'type'			=> (int) $request->get( 'type' ),
				'severity'		=> (int) $request->get( 'severity' ),
				'status'		=> 0,
				'title'			=> $request->get( 'title' ),
				'content'		=> $request->get( 'content' ),
				'createdAt'		=> time(),
			);
			if( empty( $data['title'] ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !$this->env->getMessenger()->gotError() ){
				$bugId	= $model->add( $data );
				if( $bugId )
					$this->restart( './bug/edit/'.$bugId );
			}
		}
		$model	= new Model_Project( $this->env );
		$this->addData( 'projects', $model->getAll( array( 'status' => '> 0' ), array( 'title' => 'ASC' ) ) );
	}

	protected function compactFilterInput( $input ){
		if( is_string( $input ) )
			if( strlen( $input ) )
				return $input;
		if( !is_array( $input ) )
			return NULL;
		foreach( $input as $nr => $chunk ){
			$chunk	= $this->compactFilterInput( $chunk );
			if( is_string( $chunk ) && strlen( $chunk ) || is_array( $chunk ) && count( $chunk ) )
				continue;
			unset( $input[$nr] );
		}
		return $input;
	}

	public function emerge( $bugId ){
		$request	= $this->env->request;
		$modelBug		= new Model_Bug( $this->env );
		$modelNote		= new Model_Bug_Note( $this->env );
		$bug			= $modelBug->get( $bugId );
		if( $request->has( 'save' ) ){

			$changeTypes	= array(
				'type'		=> self::CHANGE_TYPE,
				'severity'	=> self::CHANGE_SEVERITY,
				'priority'	=> self::CHANGE_PRIORITY,
				'status'	=> self::CHANGE_STATUS,
				'progress'	=> self::CHANGE_PROGRESS,
				'managerId'	=> self::CHANGE_MANAGER,
			);
			$changes		= array(
				'modifiedAt'	=> time()
			);
			foreach( $changeTypes as $changeKey => $changeType ){
				$value	= $request->get( $changeKey );
				if( strlen( $value ) && $value != $bug->$changeKey )
					$changes[$changeKey]	= $value;
			}

			if( count( $changes ) > 1 || $request->get( 'note') ){

				$data	= array(
					'bugId'		=> $bugId,
					'userId'	=> $this->env->getSession()->get( 'auth_user_id' ),
					'note'		=> $request->get( 'note'),
					'timestamp'	=> time(),
				);
				$noteId	= $modelNote->add( $data );
				foreach( $changeTypes as $changeKey => $changeType ){
					$value	= $request->get( $changeKey );
					if( strlen( $value ) && $value != $bug->$changeKey ){
						$this->noteChange( $bugId, $noteId, $changeType, $bug->$changeKey, $value );
					}
				}
				$modelBug->edit( $bugId, $changes );
				$this->env->getMessenger()->noteSuccess( 'Die Veränderungen wurden gespeichert.' );
			}
			else
				$this->env->getMessenger()->noteError( 'Keine Veränderungen vorgenommen.' );
		}
		$this->restart( './bug/edit/'.$bugId );
	}

	public function edit( $bugId ){

		$logic	= new Logic_Bug( $this->env );
		$request	= $this->env->request;

		$modelBug		= new Model_Bug( $this->env );
		$modelBugNote	= new Model_Bug_Note( $this->env );
		$modelBugChange	= new Model_Bug_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$users	= [];
		foreach( $modelUser->getAll() as $user )
			$users[$user->userId]	= $user;

		$this->addData( 'users', $users );

		if( $request->has( 'save' ) ){
			$data		= array(
				'type'		=> $request->get( 'type' ),
				'severity'	=> $request->get( 'severity' ),
				'status'	=> $request->get( 'status' ),
//				'progress'	=> $request->get( 'progress' ),
				'title'		=> $request->get( 'title' ),
				'content'	=> $request->get( 'content' ),
			);
			$modelBug->edit( $bugId, $data );
//			$this->restart( './statement' );
		}
		$bug			= $modelBug->get( $bugId );

		$notes		= $modelBugNote->getAllByIndex( 'bugId', $bugId, array( 'timestamp' => 'ASC' ) );
		foreach( $notes as $nr => $note ){
			$changes	= $modelBugChange->getAllByIndex( 'noteId', $note->bugNoteId, array( 'type' => 'ASC' ) );
			if( $note->userId )
				$notes[$nr]->user	= $users[$note->userId];
			$notes[$nr]->changes	= $changes;
			foreach( $changes as $nr => $change )
				if( $change->userId )
					$changes[$nr]->user	= $users[$change->userId];
		}
		$bug->notes		= $notes;
		$bug->changes	= $modelBugChange->getAll( array( 'bugId' => $bugId, 'noteId' => 0 ), array( 'timestamp' => 'ASC' ) );

		if( $bug->reporterId )
			$bug->reporter	= $users[$bug->reporterId];
		if( $bug->managerId )
			$bug->manager	= $users[$bug->managerId];
		$this->addData( 'bug', $bug );
	}

	public function filter( $mode = NULL, $modeValue = 0 )
	{
		$session	= $this->env->getSession();
		switch( $mode )
		{
			case 'mode':
				$session->set( 'bug-filter-panel-mode', $modeValue );
				break;
			case 'reset':
				foreach( $this->filters as $filter )
					$session->remove( 'filter-bug-'.$filter );
				break;
			default:
				$request	= $this->env->getRequest();
				foreach( $this->filters as $filter )
				{
					$session->remove( 'filter-bug-'.$filter );
					if( ( $value = $this->compactFilterInput( $request->get( $filter ) ) ) )
						$session->set( 'filter-bug-'.$filter, $value );
				}
		}
		$this->restart( './bug' );
	}

	public function index(){
		$session	= $this->env->getSession();
		$filters	= [];
		foreach( $session->getAll() as $key => $value )
			if( preg_match( '/^filter-bug-/', $key ) ){
				$column	= preg_replace( '/^filter-bug-/', '', $key );
				if( !in_array( $column, array( 'order', 'direction', 'limit' ) ) )
					$filters[$column] = $value;
			}

		$orders	= [];
		$order	= $session->get( 'filter-bug-order' );
		$dir	= $session->get( 'filter-bug-direction' );
		$limit	= $session->get( 'filter-bug-limit' );
		$limit	= $limit > 0 ? $limit : 10;
		if( $order && $dir )
			$orders	= array( $order => $dir );

		$modelBug		= new Model_Bug( $this->env );
		$modelNote		= new Model_Bug_Note( $this->env );
		$modelChange	= new Model_Bug_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$bugs		= $modelBug->getAll( $filters, $orders, array( 0, $limit ) );
		foreach( $bugs as $nr => $bug ){
			$bugs[$nr]->notes = $modelNote->getAllByIndex( 'bugId', $bug->bugId, array( 'timestamp' => 'ASC' ) );
			$bugs[$nr]->changes	= $modelChange->getAllByIndex( 'bugId', $bug->bugId, array( 'timestamp' => 'ASC' ) );
		}
		$this->addData( 'bugs', $bugs );


		$users	= [];
		foreach( $modelUser->getAll() as $user )
			$users[$user->userId]	= $user;

		$this->addData( 'users', $users );
	}

	protected function noteChange( $bugId, $noteId, $type, $from, $to ){
		$model		= new Model_Bug_Change( $this->env );
		$data	= array(
			'bugId'		=> $bugId,
			'userId'	=> $this->env->getSession()->get( 'auth_user_id' ),
			'noteId'	=> $noteId,
			'type'		=> $type,
			'from'		=> $from,
			'to'		=> $to,
			'timestamp'	=> time(),
		);
		return $model->add( $data );
	}
}
?>
