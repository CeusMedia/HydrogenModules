<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Import extends Controller
{
	protected HttpRequest $request;

	protected Dictionary $session;

	protected Model_Form $modelForm;

	protected Model_Form_Import_Rule $modelRule;

	protected Model_Import_Connection $modelConnection;

	protected Model_Import_Connector $modelConnector;

	protected array $connectionMap		= [];

	protected array $formMap			= [];

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->checkIsPost( FALSE ) ){
			$data		= [
				'importConnectionId'	=> $this->request->get( 'importConnectionId' ),
				'formId'				=> $this->request->get( 'formId' ),
				'status'				=> $this->request->get( 'status' ),
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

	/**
	 *	@param		int|string		$ruleId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $ruleId ): void
	{
		$rule		= $this->modelRule->get( $ruleId );
		if( NULL === $rule ){
			$this->env->getMessenger()->noteError( 'Invalid Rule ID' );
			$this->restart( NULL, TRUE );
		}

		/** @var ?Entity_Import_Connection $connection */
		$connection	= $this->modelConnection->get( $rule->importConnectionId );
		if( NULL === $connection ){
			$this->env->getMessenger()->noteError( 'Related connections ID is invalid' );
			$this->restart( NULL, TRUE );
		}

		/** @var ?Entity_Import_Connector $connector */
		$connector	= $this->modelConnector->get( $connection->importConnectorId );
		if( NULL === $connector ){
			$this->env->getMessenger()->noteError( 'Related connector ID is invalid' );
			$this->restart( NULL, TRUE );
		}

		$factory		= new ObjectFactory();

		/** @var Logic_Import_Connector_Interface $remoteResource */
		$remoteResource	= $factory->create( $connector->className, [$this->env] );
		$remoteResource->setConnection( $connection )->connect();
		$folders		= $remoteResource->getFolders( TRUE );

//		if( strlen( trim( $this->request->get( 'moveTo' ) ) ) > 0 )
//			if( !in_array( $this->request->get( 'moveTo' ), $folders ) )
//				throw new InvalidArgumentException( 'Invalid folder' );

		if( $this->checkIsPost( FALSE ) ){
			$data		= [
				'importConnectionId'	=> $this->request->get( 'importConnectionId' ),
				'formId'				=> $this->request->get( 'formId' ),
				'status'				=> $this->request->get( 'status' ),
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

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$rules	= $this->modelRule->getAll();
		$this->addData( 'rules', $rules );
		foreach( $rules as $rule ){
			$rule->form	= $this->formMap[$rule->formId];
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return	void
	 */
	protected function __onInit(): void
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

	/**
	 *	Checks whether the current request is done via POST.
	 *	Throws exception in strict mode.
	 *	@param		bool		$strict		Flag: throw exception if not POST and strict mode (default)
	 *	@return		bool
	 *	@throws		RuntimeException		if request method is not POST and strict mode is enabled
	 */
	protected function checkIsPost( bool $strict = TRUE ): bool
	{
		if( $this->request->getMethod()->is( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}
}
