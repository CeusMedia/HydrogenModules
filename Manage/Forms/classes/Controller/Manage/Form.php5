<?php
class Controller_Manage_Form extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;

	protected function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
	}

	protected function checkId( $formId ){
		if( !$formId )
			throw new RuntimeException( 'No form ID given' );
		if( !( $form = $this->modelForm->get( $formId ) ) )
			throw new DomainException( 'Invalid form ID given' );
		return $form;
	}

	protected function checkIsPost( $strict = TRUE ){
		if( $this->env->getRequest()->isMethod( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	public function add(){
		if( $this->env->getRequest()->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->env->getRequest()->getAll();
			$data['timestamp']	= time();
			$formId	= $this->modelForm->add( $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}
	}

	public function confirm(){
		$fillId		= $this->env->getRequest()->get( 'fillId' );
		$fill		= $this->modelFill->get( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'		=> Model_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		) );
		return 'Okay.';
	}

	public function edit( $formId ){
		$form	= $this->checkId( $formId );
		if( $this->env->getRequest()->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->env->getRequest()->getAll();
			$data['timestamp']	= time();
			$this->modelForm->edit( $formId, $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}
		$this->addData( 'form', $form );
	}

	public function index(){
		$forms	= $this->modelForm->getAll( array(), array( 'title' => 'ASC' ) );
		$this->addData( 'forms', $forms );
	}

	public function view( $formId ){
		$form	= $this->checkId( (int) $formId );
		$this->addData( 'formId', $formId );
//		$helper	= new View_Helper_Form( $this->env );
//		return $helper->setId( $formId )->render();
	}

	public function remove( $formId ){
		$this->checkId( $formId );
		$this->modelForm->remove( $formId );
		$this->restart( NULL, TRUE );
	}
}
