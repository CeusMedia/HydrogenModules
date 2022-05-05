<?php
class Controller_Manage_Form extends CMF_Hydrogen_Controller
{
	protected $modelForm;
	protected $modelFill;
	protected $modelRule;
	protected $modelMail;
	protected $modelTranserTarget;
	protected $modelTransferRule;
	protected $modelImportRule;
	protected $modelImportConnector;
	protected $modelImportConnection;

	protected $filters		= [
		'formId',
		'type',
		'status',
		'customerMailId',
		'managerMailId',
		'title'
	];

	protected $transferTargetMap		= [];

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$this->checkIsPost();
			$data	= $this->request->getAll();
			$data['timestamp']	= time();
			$formId	= $this->modelForm->add( $data, FALSE );
			$this->restart( 'edit/'.$formId, TRUE );
		}

		$orders		= ['identifier' => 'customer_result_%'];
		$mails		= $this->modelMail->getAll( $orders, ['title' => 'ASC'] );
		$this->addData( 'mails', $mails );
	}

	public function addRule( $formId, $formType )
	{
		$data		= [];
		for( $i=0; $i<3; $i++ ){
			if( $this->request->get( 'ruleKey_'.$i ) ){
				$data[]	= [
					'key'			=> $this->request->get( 'ruleKey_'.$i ),
					'keyLabel'		=> $this->request->get( 'ruleKeyLabel_'.$i ),
					'value'			=> $this->request->get( 'ruleValue_'.$i ),
					'valueLabel'	=> $this->request->get( 'ruleValueLabel_'.$i ),
				];
			}
		}
		$this->modelRule->add( [
			'formId'		=> $formId,
			'type'			=> $formType,
			'rules'			=> json_encode( $data ),
			'mailAddresses'	=> $this->request->get( 'mailAddresses' ),
			'mailId'		=> $this->request->get( 'mailId' ),
			'filePath'		=> $this->request->get( 'filePath' ),
		] );
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

	public function confirm()
	{
		$fillId		= $this->request->get( 'fillId' );
		$fill		= $this->modelFill->get( $fillId );
		$this->modelFill->edit( $fillId, [
			'status'		=> Model_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		] );
		return 'Okay.';
	}

	public function edit( $formId )
	{
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
		$this->addData( 'rulesAttachment', $this->modelRule->getAllByIndices( [
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_ATTACHMENT,
		] ) );
		$this->addData( 'rulesManager', $this->modelRule->getAllByIndices( [
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_MANAGER,
		] ) );
		$this->addData( 'rulesCustomer', $this->modelRule->getAllByIndices( [
			'formId'	=> $formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		] ) );

		$transferTargetMap	= [];
		foreach( $this->modelTransferTarget->getAll() as $target )
			$transferTargetMap[$target->formTransferTargetId]	= $target;
		$this->addData( 'transferTargets', $transferTargetMap );
		$this->addData( 'transferRules', $this->modelTransferRule->getAllByIndices( [
			'formId'	=> $formId,
		] ) );

		$fills	= $this->modelFill->getAll( ['formId' => $formId] );
		$this->addData( 'fills', $fills );
		$this->addData( 'hasFills', count( $fills ) > 0 );

		$parameterBlacklist	= ['gclid', 'fclid'];
		$references	= $this->modelFill->getDistinct( 'referer', ['formId' => $formId], ['referer' => 'ASC'] );
		$list		= [];
		foreach( array_filter( $references ) as $nr => $reference ){
			if( preg_match( '@&preview=true@', $reference ) )
				continue;

			$url = new ADT_URL( $reference );
			parse_str( $url->getQuery(), $parameters );
			foreach( $parameters as $key => $value ){
				if( in_array( $key, $parameterBlacklist ) )
					unset( $parameters[$key] );
			}
			$url->setQuery( http_build_query( $parameters, NULL, '&' ) );
			$list[]	= $url->getAbsolute();
		}
		$this->addData( 'references', array_unique( $list ) );
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

	public function filter( $reset = NULL )
	{
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

	public function index( $page = 0 )
	{
		$limit		= 15;
		$conditions	= [];
		foreach( $this->filters as $filterKey ){
			$value	= $this->session->get( 'filter_manage_form_'.$filterKey );
			$this->addData( 'filter'.ucfirst( $filterKey ), $value );
			if( strlen( trim( $value ) ) ){
				if( in_array( $filterKey, ['orderColumn', 'orderDirection'] ) )
					continue;
				if( $filterKey === 'title' )
					$value	= '%'.$value.'%';
				$conditions[$filterKey]	= $value;
			}
		}
		$orders		= ['status' => 'DESC', 'title' => 'ASC'];
		$limits		= [$page * $limit, $limit];
		$total		= $this->modelForm->count( $conditions );
		$forms		= $this->modelForm->getAll( $conditions, $orders, $limits );
		foreach( $forms as $form ){
			$form->transfers	= $this->modelTransferRule->getAllByIndex( 'formId', $form->formId );
			$form->imports		= $this->modelImportRule->getAllByIndex( 'formId', $form->formId );
		}

		$transferTargetMap	= [];
		foreach( $this->modelTransferTarget->getAll() as $transferTarget )
			$transferTargetMap[$transferTarget->formTransferTargetId]	= $transferTarget;
		$this->addData( 'transferTargets', $transferTargetMap );

		$this->addData( 'forms', $forms );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
		$this->addData( 'mailsCustomer', $this->getAvailableCustomerMails() );
		$this->addData( 'mailsManager', $this->getAvailableManagerMails() );
	}

	public function ajaxTestTransferRules()
	{
		$this->checkIsPost();
		$ruleId	= $this->request->get( 'ruleId' );
		$this->checkTransferRuleId( $ruleId );
		$rules	= $this->request->get( 'rules' );

		$response	= [
			'userId'	=> $this->session->get( 'auth_user_id' ),
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

	public function remove( $formId )
	{
		$this->checkId( $formId );
		$this->modelForm->remove( $formId );
		$this->restart( NULL, TRUE );
	}

	public function removeRule( $formId, $ruleId )
	{
		$this->modelRule->remove( $ruleId );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function removeTransferRule( $formId, $transferRuleId )
	{
		$this->checkTransferRuleId( $transferRuleId );
		$this->modelTransferRule->remove( $transferRuleId );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function setTab( $formId, $tabId )
	{
		$this->session->set( 'manage_forms_tab', $tabId );
		if( $this->request->isAjax() ){
			header( "Content-Type: application/json" );
			print( json_encode( ['status' => 'data', 'data' => 'ok'] ) );
			exit;
		}
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function view( $formId, $mode = NULL )
	{
		$form	= $this->checkId( (int) $formId );
		$this->addData( 'formId', $formId );
		$this->addData( 'mode', (string) $mode );
//		$helper	= new View_Helper_Form( $this->env );
//		return $helper->setId( $formId )->render();
		$this->addData( 'references', ['http://a.b.c']);
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelRule	= new Model_Form_Rule( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
		$this->modelTransferTarget		= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransferRule		= new Model_Form_Transfer_Rule( $this->env );
		$this->modelImportRule			= new Model_Form_Import_Rule( $this->env );
		$this->modelImportConnector		= new Model_Import_Connector( $this->env );
		$this->modelImportConnection	= new Model_Import_Connection( $this->env );

		$module			= $this->env->getModules()->get( 'Manage_Forms' );
		$mailDomains	= trim( $module->config['mailDomains']->value );
		$mailDomains	= strlen( $mailDomains ) ? preg_split( '/\s*,\s*/', $mailDomains ) : [];
		$this->addData( 'mailDomains', $mailDomains );

		$pathApp			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathApp		= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->path			= $pathApp.$this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->addData( 'files', $this->listFiles() );
	}

	protected function checkId( $formId, bool $strict = TRUE )
	{
		if( !$formId )
			throw new RuntimeException( 'No form ID given' );
		if( $form = $this->modelForm->get( $formId ) )
			return $form;
		if( $strict )
			throw new DomainException( 'Invalid form ID given' );
		return FALSE;
	}

	protected function checkIsPost( bool $strict = TRUE ): bool
	{
		if( $this->request->getMethod()->is( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	protected function checkTransferRuleId( $transferRuleId )
	{
		if( !$transferRuleId )
			throw new RuntimeException( 'No transfer rule ID given' );
		if( !( $transferRule = $this->modelTransferRule->get( $transferRuleId ) ) )
			throw new DomainException( 'Invalid transfer rule ID given' );
		return $transferRule;
	}

	protected function getAvailableCustomerMails( $conditions = [], $orders = [] ): array
	{
//		$conditions	= ['identifier' => 'customer_result_%'];
		$conditions	= array_merge( $conditions, ['roleType' => [
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_RESULT,
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_REACT,
			Model_Form_Mail::ROLE_TYPE_CUSTOMER_ALL,
			Model_Form_Mail::ROLE_TYPE_LEADER_RESULT,
			Model_Form_Mail::ROLE_TYPE_LEADER_REACT,
			Model_Form_Mail::ROLE_TYPE_LEADER_ALL,
		] ] );
		$orders		= $orders ? $orders : [
			'roleType'	=> 'ASC',
			'title'		=> 'ASC',
		];
		return $this->modelMail->getAll( $conditions, $orders );
	}

	protected function getAvailableManagerMails( $conditions = [], $orders = [] ): array
	{
//		$conditions	= ['identifier' => 'manager_%'];
		$conditions	= array_merge( $conditions, ['roleType' => [
			Model_Form_Mail::ROLE_TYPE_LEADER_RESULT,
			Model_Form_Mail::ROLE_TYPE_LEADER_REACT,
			Model_Form_Mail::ROLE_TYPE_LEADER_ALL,
			Model_Form_Mail::ROLE_TYPE_MANAGER_RESULT,
			Model_Form_Mail::ROLE_TYPE_MANAGER_REACT,
			Model_Form_Mail::ROLE_TYPE_MANAGER_ALL,
		] ] );
		$orders		= $orders ? $orders : [
			'roleType'	=> 'ASC',
			'title'		=> 'ASC',
		];
		return $this->modelMail->getAll( $conditions, $orders );
	}

	protected function getBlocksFromFormContent( $content ): array
	{
		$modelBlock	= new Model_Form_Block( $this->env );
		$list		= [];
		$matches	= [];
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

	protected function getMimeTypeOfFile( $fileName )
	{
		if( !file_exists( $this->path.$fileName ) )
			throw new RuntimeException( 'File "'.$fileName.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->path.$fileName );
	}

	protected function listFiles()
	{
		$list	= array();
		$index	= new DirectoryIterator( $this->path );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() || $entry->getFilename()[0] === "." )
				continue;
			$key	= strtolower( $entry->getFilename() );
			$list[$entry->getFilename()]	= (object) array(
				'fileName'		=> $entry->getFilename(),
				'mimeType'		=> $this->getMimeTypeOfFile( $entry->getFilename() )
			);
		}
		ksort( $list );
		return $list;
	}
}
