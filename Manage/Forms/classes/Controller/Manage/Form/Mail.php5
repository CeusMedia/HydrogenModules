<?php
class Controller_Manage_Form_Mail extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelMail;
	protected $filterPrefix		= 'filter_manage_form_mail_';
	protected $filters			= array(
		'mailId',
		'title',
		'identifier',
		'format',
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
		$filters		= $session->getAll( $this->filterPrefix, TRUE );

		$filterMailId		= $filters->get( 'mailId' );
		$filterTitle		= $filters->get( 'title' );
		$filterIdentifier	= $filters->get( 'identifier' );
		$filterFormat		= $filters->get( 'format' );

		$limit		= 15;
		$conditions	= array();

		if( (int) $filterMailId )
		 	$conditions['mailId']		= (int) $filterMailId;
		if( strlen( trim( $filterTitle ) ) )
		 	$conditions['title']		= '%'.$filterTitle.'%';
		if( strlen( trim( $filterIdentifier ) ) )
		 	$conditions['identifier']	= '%'.$filterIdentifier.'%';
		if( $filterFormat )
		 	$conditions['format']		= $filterFormat;

		$orders		= array( 'title' => 'ASC' );
		$limits		= array( $page * $limit, $limit );
		$total		= $this->modelMail->count();
		$count		= $this->modelMail->count( $conditions );
		$mails		= $this->modelMail->getAll( $conditions, $orders, $limits );
		$this->addData( 'mails', $mails );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );

		$identifiers	= $this->modelMail->getAll(
			array(),
			array( 'identifier' => 'ASC' ),
			array(),
			array( 'identifier' )
		);
		$this->addData( 'identifiers', $identifiers );

		$formats		= $this->modelMail->getAll(
			array(),
			array( 'format' => 'ASC' ),
			array(),
			array( 'format' )
		);
		array_unique( $formats );
		$this->addData( 'formats', $formats );

		$this->addData( 'filterMailId', $filterMailId );
		$this->addData( 'filterTitle', $filterTitle );
		$this->addData( 'filterIdentifier', $filterIdentifier );
		$this->addData( 'filterFormat', $filterFormat );
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
