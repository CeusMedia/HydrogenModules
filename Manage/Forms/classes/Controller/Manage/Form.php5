<?php
class Controller_Manage_Form extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;
	protected $modelRule;
	protected $modelMail;
	protected $filters		= array( 'formId', 'type', 'status', 'customerMailId', 'managerMailId', 'title' );



	protected function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelRule	= new Model_Form_Rule( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	public function add(){
		if( $this->env->getRequest()->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->env->getRequest()->getAll();
			$data['timestamp']	= time();
			$formId	= $this->modelForm->add( $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}

		$orders		= array( 'identifier' => 'customer_result_%' );
		$mails		= $this->modelMail->getAll( $orders, array( 'title' => 'ASC' ) );
		$this->addData( 'mails', $mails );
	}

	public function addRule( $formId, $formType ){
		$request	= $this->env->getRequest();
		$data		= array();
		for( $i=0; $i<3; $i++ ){
			if( $request->get( 'ruleKey_'.$i ) ){
				$data[]	= array(
					'key'			=> $request->get( 'ruleKey_'.$i ),
					'keyLabel'		=> $request->get( 'ruleKeyLabel_'.$i ),
					'value'			=> $request->get( 'ruleValue_'.$i ),
					'valueLabel'	=> $request->get( 'ruleValueLabel_'.$i ),
				);
			}
		}
		$this->modelRule->add( array(
			'formId'		=> $formId,
			'type'			=> $formType,
			'rules'			=> json_encode( $data ),
			'mailAddresses'	=> $request->get( 'mailAddresses' ),
			'mailId'		=> $request->get( 'mailId' ),
		) );
		$this->restart( 'edit/'.$formId, TRUE );
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
		$session	= $this->env->getSession();
		$this->addData( 'activeTab', $session->get( 'manage_forms_tab' ) );
		$form		= $this->checkId( $formId );
		if( $this->env->getRequest()->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->env->getRequest()->getAll();
			$data['timestamp']	= time();
			$this->modelForm->edit( $formId, $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}
		$this->addData( 'form', $form );
		$this->addData( 'mailsCustomer', $this->getAvailableCustomerMails() );
		$this->addData( 'mailsManager', $this->getAvailableManagerMails() );

		$this->addData( 'rulesManager', $this->modelRule->getAllByIndices( array(
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_MANAGER,
		) ) );
		$this->addData( 'rulesCustomer', $this->modelRule->getAllByIndices( array(
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		) ) );

		$fills	= $this->modelFill->getAll( array( 'formId' => $formId ) );
		$this->addData( 'fills', $fills );
		$this->addData( 'hasFills', count( $fills ) > 0 );
	}

	public function filter( $reset = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $reset ){
			foreach( $this->filters as $filterKey )
				$session->remove( 'filter_manage_form_'.$filterKey );
		}
		foreach( $this->filters as $filterKey ){
			if( $request->has( $filterKey ) ){
				$session->set( 'filter_manage_form_'.$filterKey, $request->get( $filterKey ) );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$session	= $this->env->getSession();
		$limit		= 15;
		$conditions	= array();
		foreach( $this->filters as $filterKey ){
			$value	= $session->get( 'filter_manage_form_'.$filterKey );
			$this->addData( 'filter'.ucfirst( $filterKey ), $value );
			if( strlen( trim( $value ) ) ){
				if( in_array( $filterKey, array( 'orderColumn', 'orderDirection' ) ) )
					continue;
				if( $filterKey === 'title' )
					$value	= '%'.$value.'%';
				$conditions[$filterKey]	= $value;
			}
		}
		$orders		= array( 'status' => 'DESC', 'title' => 'ASC' );
		$limits		= array( $page * $limit, $limit );
		$total		= $this->modelForm->count( $conditions );
		$forms		= $this->modelForm->getAll( $conditions, $orders, $limits );
		$this->addData( 'forms', $forms );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
		$this->addData( 'mailsCustomer', $this->getAvailableCustomerMails() );
		$this->addData( 'mailsManager', $this->getAvailableManagerMails() );

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

	public function removeRule( $formId, $ruleId ){
		$this->modelRule->remove( $ruleId );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function setTab( $formId, $tabId ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$session->set( 'manage_forms_tab', $tabId );
		if( $request->isAjax() ){
			header( "Content-Type: application/json" );
			print( json_encode( array( 'status' => 'data', 'data' => 'ok' ) ) );
			exit;
		}
		$this->restart( 'edit/'.$formId, TRUE );
	}

	//  --  PROTECTED METHODS  --  //
	protected function getAvailableCustomerMails( $conditions = array(), $orders = array() ){
//		$conditions	= array( 'identifier' => 'customer_result_%' );
		$conditions	= array_merge( $conditions, array( 'roleType' => array(
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_RESULT,
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_REACT,
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_ALL,
			Model_Form_Mail::ROLE_TYPE_LEADER_RESULT,
			Model_Form_Mail::ROLE_TYPE_LEADER_REACT,
			Model_Form_Mail::ROLE_TYPE_LEADER_ALL,
		) ) );
		$orders		= $orders ? $orders : array(
			'roleType'	=> 'ASC',
			'title'		=> 'ASC',
		);
		return $this->modelMail->getAll( $conditions, $orders );
	}

	protected function getAvailableManagerMails( $conditions = array(), $orders = array() ){
//		$conditions	= array( 'identifier' => 'manager_%' );
		$conditions	= array_merge( $conditions, array( 'roleType' => array(
			Model_Form_Mail::ROLE_TYPE_LEADER_RESULT,
			Model_Form_Mail::ROLE_TYPE_LEADER_REACT,
			Model_Form_Mail::ROLE_TYPE_LEADER_ALL,
			Model_Form_Mail::ROLE_TYPE_MANAGER_RESULT,
			Model_Form_Mail::ROLE_TYPE_MANAGER_REACT,
			Model_Form_Mail::ROLE_TYPE_MANAGER_ALL,
		) ) );
		$orders		= $orders ? $orders : array(
			'roleType'	=> 'ASC',
			'title'		=> 'ASC',
		);
		return $this->modelMail->getAll( $conditions, $orders );
	}
}
