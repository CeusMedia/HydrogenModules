<?php
class Controller_Manage_Form_Mail extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelMail;
	protected $filterPrefix		= 'filter_manage_form_mail_';
	protected $filters			= array(
		'mailId',
		'roleType',
		'identifier',
		'format',
		'title',
	);

	protected function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	protected function checkId( $mailId ){
		if( !$mailId )
			throw new RuntimeException( 'No mail ID given' );
		if( !( $mail = $this->modelMail->get( $mailId ) ) )
			throw new DomainException( 'Invalid mail ID given' );
		return $mail;
	}

	protected function checkIsPost(){
		if( !$this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function add(){
		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll();
			$mailId	= $this->modelMail->add( $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
	}

	public function edit( $mailId ){
		$mail	= $this->checkId( $mailId );
		if( $this->env->getRequest()->has( 'save' ) ){
			$data	= $this->env->getRequest()->getAll();
			$this->modelMail->edit( $mailId, $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
		$this->addData( 'mail', $mail );
	}

	public function filter( $reset = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $reset ){
			foreach( $this->filters as $filter )
				$session->remove( $this->filterPrefix.$filter );
		}
		foreach( $this->filters as $filter ){
			if( $request->has( $filter ) ){
				$value	= $request->get( $filter );
				$session->set( $this->filterPrefix.$filter, $value );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$session		= $this->env->getSession();
		$filters		= new ADT_List_Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), NULL ) ),
			$session->getAll( $this->filterPrefix )
		) );

		$filterMailId		= $filters->get( 'mailId' );
		$filterTitle		= $filters->get( 'title' );
		$filterIdentifier	= $filters->get( 'identifier' );
		$filterFormat		= $filters->get( 'format' );

		$limit		= 15;
		$conditions	= [];

		if( (int) $filters->get( 'mailId' ) )
		 	$conditions['mailId']		= (int) $filters->get( 'mailId' );
		if( strlen( trim( $filters->get( 'title' ) ) ) )
		 	$conditions['title']		= '%'.$filters->get( 'title' ).'%';
		if( strlen( trim( $filters->get( 'identifier' ) ) ) )
		 	$conditions['identifier']	= '%'.$filters->get( 'identifier' ).'%';
		if( $filters->get( 'format' ) )
		 	$conditions['format']		= $filters->get( 'format' );
		if( $filters->get( 'roleType' ) )
		 	$conditions['roleType']		= $filters->get( 'roleType' );

		$orders		= array( 'title' => 'ASC' );
		$limits		= array( $page * $limit, $limit );
		$total		= $this->modelMail->count();
		$count		= $this->modelMail->count( $conditions );
		$mails		= $this->modelMail->getAll( $conditions, $orders, $limits );
		$this->addData( 'mails', $mails );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );
		$this->addData( 'count', $count );
		$this->addData( 'total', $total );

		$this->addData( 'filters', $filters );

/*		$identifiers	= $this->modelMail->getAll(
			array(),
			array( 'identifier' => 'ASC' ),
			array(),
			array( 'identifier' )
		);
		$this->addData( 'identifiers', $identifiers );
*/
		$formats		= $this->modelMail->getAll(
			array(),
			array( 'format' => 'ASC' ),
			array(),
			array( 'format' )
		);
		array_unique( $formats );
		$this->addData( 'formats', $formats );
	}

	public function remove( $mailId ){
		$this->checkId( $mailId );
		$this->modelMail->remove( $mailId );
		$this->restart( NULL, TRUE );
	}

	public function view( $mailId ){
		$mail	= $this->checkId( $mailId );
		$this->addData( 'mail', $mail );
	}
}
