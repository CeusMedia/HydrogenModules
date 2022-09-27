<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Import extends Controller
{
	protected $request;
	protected $modelForm;
	protected $modelRule;
	protected $modelConnection;
	protected $modelConnector;
	protected $connectionMap	= [];
	protected $formMap			= [];

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->modelForm		= new Model_Form( $this->env );
		$this->modelRule		= new Model_Form_Import_Rule( $this->env );
		$this->modelConnection	= new Model_Import_Connection( $this->env );
		$this->modelConnector	= new Model_Import_Connector( $this->env );
		foreach( $this->modelForm->getAll( [], ['title' => 'ASC'] ) as $form )
			$this->formMap[$form->formId]	= $form;
		foreach( $this->modelConnection->getAll( [], ['title' => 'ASC'] ) as $connection )
			$this->connectionMap[$connection->importConnectionId] = $connection;
	}

	public function add()
	{
		if( $this->request->getMethod()->isPost() ){
			$data		= [
				'importConnectionId'	=> $this->request->get( 'importConnectionId' ),
				'formId'				=> $this->request->get( 'formId' ),
				'title'					=> $this->request->get( 'title' ),
				'searchCriteria'		=> $this->request->get( 'searchCriteria' ),
				'options'				=> $this->request->get( 'options' ),
				'rules'					=> $this->request->get( 'rules' ),
				'renameTo'				=> $this->request->get( 'renameTo' ),
				'moveTo'				=> $this->request->get( 'moveTo' ),
				'createdAt'				=> time(),
				'modifiedAt'			=> time(),
			];
			$ruleId		= $this->modelRule->add( $data );
			$this->restart( 'edit/'.$ruleId, TRUE );
		}
		$this->addData( 'forms', $this->formMap );
		$this->addData( 'connections', $this->connectionMap );
	}

	public function ajaxTestRules()
	{
		$this->checkIsPost();
		$ruleId	= $this->request->get( 'ruleId' );
		$this->checkImportRuleId( $ruleId );
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

		$this->respondData( $response );
	}

	public function edit( $ruleId )
	{
		$rule		= $this->modelRule->get( $ruleId );
		$connection	= $this->modelConnection->get( $rule->importConnectionId );
		$connector	= $this->modelConnector->get( $connection->importConnectorId );

		$factory	= new ObjectFactory();
		$remoteResource	= $factory->create( $connector->className, array( $this->env ) );
		$remoteResource->setConnection( $connection )->connect();
		$folders	= $remoteResource->getFolders( TRUE );

//		if( strlen( trim( $this->request->get( 'moveTo' ) ) ) > 0 )
//			if( !in_array( $this->request->get( 'moveTo' ), $folders ) )
//				throw new InvalidArgumentException( 'Invalid folder' );

		if( $this->request->getMethod()->isPost() ){
			$data		= [
				'importConnectionId'	=> $this->request->get( 'importConnectionId' ),
				'formId'				=> $this->request->get( 'formId' ),
				'title'					=> $this->request->get( 'title' ),
				'searchCriteria'		=> $this->request->get( 'searchCriteria' ),
				'options'				=> $this->request->get( 'options' ),
				'rules'					=> $this->request->get( 'rules' ),
				'renameTo'				=> $this->request->get( 'renameTo' ),
				'moveTo'				=> $this->request->get( 'moveTo' ),
				'modifiedAt'			=> time(),
			];
			$this->modelRule->edit( $ruleId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'rule', $this->modelRule->get( $ruleId ) );
		$this->addData( 'forms', $this->formMap );
		$this->addData( 'folders', $folders );
		$this->addData( 'connections', $this->connectionMap );
	}

	public function index()
	{
		$rules	= $this->modelRule->getAll();
		$this->addData( 'rules', $rules );
		foreach( $rules as $rule ){
			$rule->form	= $this->formMap[$rule->formId];
		}
	}

	//  --  PROTECTED  --  //

	protected function checkIsPost( $strict = TRUE ){
		if( $this->request->getMethod()->is( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	protected function checkImportRuleId( $importRuleId ){
		if( !$importRuleId )
			throw new RuntimeException( 'No import rule ID given' );
		if( !( $importRule = $this->modelRule->get( $importRuleId ) ) )
			throw new DomainException( 'Invalid import rule ID given' );
		return $importRule;
	}
}
