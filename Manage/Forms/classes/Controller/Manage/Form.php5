<?php
class Controller_Manage_Form extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;
	protected $modelRule;
	protected $modelMail;
	protected $modelTranserTarget;
	protected $modelTransferRule;
	protected $filters		= array(
		'formId',
		'type',
		'status',
		'customerMailId',
		'managerMailId',
		'title'
	);

	public function add(){
		if( $this->request->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->request->getAll();
			$data['timestamp']	= time();
			$formId	= $this->modelForm->add( $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}

		$orders		= array( 'identifier' => 'customer_result_%' );
		$mails		= $this->modelMail->getAll( $orders, array( 'title' => 'ASC' ) );
		$this->addData( 'mails', $mails );
	}

	public function addRule( $formId, $formType ){
		$data		= array();
		for( $i=0; $i<3; $i++ ){
			if( $this->request->get( 'ruleKey_'.$i ) ){
				$data[]	= array(
					'key'			=> $this->request->get( 'ruleKey_'.$i ),
					'keyLabel'		=> $this->request->get( 'ruleKeyLabel_'.$i ),
					'value'			=> $this->request->get( 'ruleValue_'.$i ),
					'valueLabel'	=> $this->request->get( 'ruleValueLabel_'.$i ),
				);
			}
		}
		$this->modelRule->add( array(
			'formId'		=> $formId,
			'type'			=> $formType,
			'rules'			=> json_encode( $data ),
			'mailAddresses'	=> $this->request->get( 'mailAddresses' ),
			'mailId'		=> $this->request->get( 'mailId' ),
		) );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function addTransferRule( $formId )
	{
		$this->checkIsPost();
		$title		= $this->request->get( 'title' );
		$targetId	= trim( $this->request->get( 'formTransferTargetId' ) );
		$rules		= trim( $this->request->get( 'rules' ) );

		if( empty( $title ) )
			throw new InvalidArgumentException( 'No title given' );
		if( empty( $targetId ) )
			throw new InvalidArgumentException( 'No target ID given' );

		$this->modelTransferRule->add([
			'formTransferTargetId'	=> $targetId,
			'formId'				=> $formId,
			'title'					=> $title,
			'rules'					=> $rules,
			'createdAt'				=> time(),
			'modifiedAt'			=> time(),
		]);
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function confirm(){
		$fillId		= $this->request->get( 'fillId' );
		$fill		= $this->modelFill->get( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'		=> Model_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		) );
		return 'Okay.';
	}

	public function edit( $formId ){
		$this->addData( 'activeTab', $this->session->get( 'manage_forms_tab' ) );
		$form		= $this->checkId( $formId );
		if( $this->request->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->request->getAll();
			$data['timestamp']	= time();
			$this->modelForm->edit( $formId, $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}
		$this->addData( 'form', $form );
		$this->addData( 'mailsCustomer', $this->getAvailableCustomerMails() );
		$this->addData( 'mailsManager', $this->getAvailableManagerMails() );
		$this->addData( 'blocksWithin', $this->getBlocksFromFormContent( $form->content ) );
		$this->addData( 'rulesManager', $this->modelRule->getAllByIndices( array(
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_MANAGER,
		) ) );
		$this->addData( 'rulesCustomer', $this->modelRule->getAllByIndices( array(
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		) ) );
		$transferTargetMap	= array();
		foreach( $this->modelTransferTarget->getAll() as $target )
			$transferTargetMap[$target->formTransferTargetId]	= $target;
		$this->addData( 'transferTargets', $transferTargetMap );
		$this->addData( 'transferRules', $this->modelTransferRule->getAllByIndices( array(
			'formId'	=> $formId,
		) ) );

		$fills	= $this->modelFill->getAll( array( 'formId' => $formId ) );
		$this->addData( 'fills', $fills );
		$this->addData( 'hasFills', count( $fills ) > 0 );
	}

	public function editTransferRule( $formId, $transferRuleId )
	{
		$this->checkIsPost();
		$rule		= $this->checkTransferRuleId( $transferRuleId );
		$title		= $this->request->get( 'title' );
		$targetId	= trim( $this->request->get( 'formTransferTargetId' ) );
		$rules		= trim( $this->request->get( 'rules' ) );

		if( empty( $title ) )
			throw new InvalidArgumentException( 'No title given' );
		if( empty( $targetId ) )
			throw new InvalidArgumentException( 'No target ID given' );

		if( strlen( $rules ) > 0 ){
			$ruleSet = json_decode( $rules );
			if( $ruleSet )
				$rules	= json_encode( $ruleSet, JSON_PRETTY_PRINT );
		}
		if( $rule->formTransferTargetId !== $targetId )
			$data['formTransferTargetId']	= $targetId;
		if( $rule->title !== $title )
			$data['title']	= $title;
		if( $rule->rules !== $rules )
			$data['rules']	= $rules;
		if( $data ){
			$data['modifiedAt']	= time();
			$this->modelTransferRule->edit( $transferRuleId, $data );
		}
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			foreach( $this->filters as $filterKey )
				$this->session->remove( 'filter_manage_form_'.$filterKey );
		}
		foreach( $this->filters as $filterKey ){
			if( $this->request->has( $filterKey ) ){
				$this->session->set( 'filter_manage_form_'.$filterKey, $this->request->get( $filterKey ) );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$limit		= 15;
		$conditions	= array();
		foreach( $this->filters as $filterKey ){
			$value	= $this->session->get( 'filter_manage_form_'.$filterKey );
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

	public function ajaxTestTransferRules(){
		$this->checkIsPost();
		$ruleId	= $this->request->get( 'ruleId' );
		$this->checkTransferRuleId( $ruleId );
		$rules	= $this->request->get( 'rules' );

		$response	= [
			'userId'	=> $this->session->get( 'userId' ),
			'ruleId'	=> $ruleId,
			'rules'		=> $rules,
			'status'	=> 'empty',
			'message'	=> NULL,
		];

		if( strlen( trim( $rules ) ) ){
			$parser	= new ADT_JSON_Parser;
			try{
				$ruleSet	= $parser->parse( $rules, FALSE );
				$response['status']	= 'parsed';
			}
			catch( RuntimeException $e ){
				$response['status']		= 'exception';
				$response['message']	= $e->getMessage();
			}
		}

		print( json_encode( $response ) );
		exit;

		$this->respond( $rules );
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

	public function removeTransferRule( $formId, $transferRuleId )
	{
		$this->checkTransferRuleId( $transferRuleId );
		$this->modelTransferRule->remove( $transferRuleId );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function setTab( $formId, $tabId ){
		$this->session->set( 'manage_forms_tab', $tabId );
		if( $this->request->isAjax() ){
			header( "Content-Type: application/json" );
			print( json_encode( array( 'status' => 'data', 'data' => 'ok' ) ) );
			exit;
		}
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function view( $formId, $mode = NULL ){
		$form	= $this->checkId( (int) $formId );
		$this->addData( 'formId', $formId );
		$this->addData( 'mode', (string) $mode );
//		$helper	= new View_Helper_Form( $this->env );
//		return $helper->setId( $formId )->render();
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

	protected function getBlocksFromFormContent( $content ){
		$modelBlock	= new Model_Form_Block( $this->env );
		$list		= array();
		$matches	= array();
		$content	= preg_replace( '@<!--.*-->@', '', $content );
		preg_match_all( '/\[block_(\S+)\]/', $content, $matches );
		if( isset( $matches[0] ) && count( $matches[0] ) ){
			foreach( array_keys( $matches[0] ) as $nr ){
				$item	= $modelBlock->getByIndex( 'identifier', $matches[1][$nr] );
				if( !$item )
					continue;
				$list[$matches[1][$nr]]	= $item;
			}
		}
		return $list;
	}

	//  --  PROTECTED  --  //

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelRule	= new Model_Form_Rule( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
		$this->modelTransferTarget	= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransferRule	= new Model_Form_Transfer_Rule( $this->env );

		$module			= $this->env->getModules()->get( 'Manage_Forms' );
		$mailDomains	= trim( $module->config['mailDomains']->value );
		$mailDomains	= strlen( $mailDomains ) ? preg_split( '/\s*,\s*/', $mailDomains ) : array();
		$this->addData( 'mailDomains', $mailDomains );
	}

	protected function checkId( $formId, $strict = TRUE ){
		if( !$formId )
			throw new RuntimeException( 'No form ID given' );
		if( $form = $this->modelForm->get( $formId ) )
			return $form;
		if( $strict )
			throw new DomainException( 'Invalid form ID given' );
		return FALSE;
	}

	protected function checkIsPost( $strict = TRUE ){
		if( $this->request->getMethod()->is( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	protected function checkTransferRuleId( $transferRuleId ){
		if( !$transferRuleId )
			throw new RuntimeException( 'No transfer rule ID given' );
		if( !( $transferRule = $this->modelTransferRule->get( $transferRuleId ) ) )
			throw new DomainException( 'Invalid transfer rule ID given' );
		return $transferRule;
	}
}


/*
{
	"copy": ["personDate1"],
	"map": {"personDate3": "personDate2"},
	"db": {
		"course_id": {
			"table": "school_course_bases",
			"column": "schoolCourseBaseId",
			"index": {
				"schoolCourseId": "__courseId__",
				"schoolBaseId": "__base__"
			}
		}
	}
}

*/
