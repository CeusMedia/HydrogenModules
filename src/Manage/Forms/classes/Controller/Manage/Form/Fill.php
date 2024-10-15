<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Fill extends Controller
{
	protected HttpRequest $request;
	protected HttpSession $session;
	protected Logic_Mail $logicMail;
	protected Logic_Form_FillManager $logicFill;

	protected Model_Form $modelForm;
	protected Model_Form_Fill $modelFill;
	protected Model_Form_Transfer_Target $modelTransferTarget;
	protected Model_Form_Transfer_Rule $modelTransferRule;
	protected Model_Form_Fill_Transfer $modelFillTransfer;

	protected array $transferTargetMap	= [];
	protected string $sessionFilterPrefix	= 'manage_form_fill_';
	protected array $filters				= [
		'fillId',
		'email',
		'formId',
		'status',
	];

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		DomainException		if given fill ID is invalid
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function confirm( string $fillId ): void
	{
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill ID given' );
		if( $fill->status != Model_Form_Fill::STATUS_NEW ){
			if( $fill->referer ){
				$urlGlue	= preg_match( '/\?/', $fill->referer ) ? '&' : '?';
				$this->restart( $fill->referer.$urlGlue.'rc=3', FALSE, NULL, TRUE );
			}
			throw new DomainException( 'Fill already confirmed' );
		}
		$this->modelFill->edit( $fillId, [
			'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		] );
		$this->logicFill->sendCustomerResultMail( $fillId );
		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );

		$form	= $this->modelForm->get( $fill->formId );
		if( $form->forwardOnSuccess ){
			$urlGlue	= preg_match( '/\?/', $fill->forwardOnSuccess ) ? '&' : '?';
			$this->restart( $fill->forwardOnSuccess.$urlGlue.'rc=2', FALSE, NULL, TRUE );
		}

		$urlGlue	= preg_match( '/\?/', $fill->referer ) ? '&' : '?';
		if( $fill->referer )
			$this->restart( $fill->referer.$urlGlue.'rc=2', FALSE, NULL, TRUE );
		$this->restart( 'confirmed/'.$fillId, TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function testResultMails( string $fillId ): void
	{
		$this->logicFill->sendCustomerResultMail( $fillId );
//		$this->logicFill->sendManagerResultMails( $fillId );
		$this->env->getMessenger()->noteSuccess( 'Result-Mails versendet' );
		$this->restart( 'view/'.$fillId, TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		never
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function testTransfer( string $fillId ): never
	{
		print_m( $this->logicFill->applyTransfers( $fillId ) );
		exit;
	}

	/**
	 *	@param		string			$format		Data format, currently no used, since only CSV is supported
	 *	@param		string			$type		Type of ID (form|fill)
	 *	@param		string			$ids		Comma separated list of fill or form IDs or empty string
	 *	@param		int|string|NULL	$status
	 *	@return		void
	 *	@throws		DomainException			if type is not (form|fill)
	 *	@throws		JsonException			if decoding JSON of fill data failed
	 */
	public function export( string $format, string $type, string $ids, int|string|NULL $status = NULL ): void
	{
		$ids		= explode( ',', $ids );
		$basename	= 'Export_'.date( 'Y-m-d_H:i:s' );
		switch( $format ){
			case 'csv':
			default:
				$fileName	= $basename.'.csv';
				if( $status !== NULL )
					$data		= $this->logicFill->renderToCsv( $type, $ids, (int) $status );
				else
					$data		= $this->logicFill->renderToCsv( $type, $ids );
		}
		HttpDownload::sendString( $data, $fileName );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset )
			foreach( $this->filters as $filter )
				$this->session->remove( $this->sessionFilterPrefix.$filter );

		foreach( $this->filters as $filter )
			if( $this->request->has( $filter ) )
				$this->session->set( $this->sessionFilterPrefix.$filter, $this->request->get( $filter ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		integer		$page
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $page = 0 ): void
	{
		$filterFillId	= $this->session->get( $this->sessionFilterPrefix.'fillId', '' );
		$filterEmail	= $this->session->get( $this->sessionFilterPrefix.'email', '' );
		$filterFormId	= $this->session->get( $this->sessionFilterPrefix.'formId', [] );
		$filterStatus	= $this->session->get( $this->sessionFilterPrefix.'status', '' );

		$conditions		= [];
		if( 0 !== strlen( trim( $filterFillId ) ) )
			$conditions['fillId']	= $filterFillId;
		if( 0 !== strlen( trim( $filterEmail ) ) )
			$conditions['email']	= '%'.$filterEmail.'%';
//		if( strlen( trim( $filterFormId ) ) )
		if( 0 !== count( array_filter( $filterFormId ) ) )
			$conditions['formId']	= array_filter( $filterFormId );
		if( 0 !== strlen( trim( $filterStatus ) ) )
			$conditions['status']	= $filterStatus;

		$limit		= 10;
		$pages		= ceil( $this->modelFill->count( $conditions ) / $limit );
		if( $page >= $pages )
			$page	= 0;
		$orders		= ['fillId' => 'DESC'];
		$limits		= [$page * $limit, $limit];
		$fills		= $this->modelFill->getAll( $conditions, $orders, $limits );
		$forms		= $this->modelForm->getAll( [], ['title' => 'ASC'] );

		foreach( $fills as $fill ){
			$fill->transfers	= $this->modelFillTransfer->getAllByIndex( 'fillId', $fill->fillId );
			$fill->form			= $this->modelForm->get( $fill->formId );
		}

		$transferTargetMap  = [];
		foreach( $this->modelTransferTarget->getAll() as $target )
			$transferTargetMap[$target->formTransferTargetId]   = $target;
		$this->addData( 'transferTargets', $transferTargetMap );

		$this->addData( 'fills', $fills );
		$this->addData( 'forms', $forms );
		$this->addData( 'page', $page );
		$this->addData( 'pages', $pages );
		$this->addData( 'limit', $limit );

		$this->addData( 'filterFillId', $filterFillId );
		$this->addData( 'filterEmail', $filterEmail );
		$this->addData( 'filterFormId', $filterFormId );
		$this->addData( 'filterStatus', $filterStatus );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsConfirmed( string $fillId ): void
	{
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, [
			'status'	=> Model_Form_Fill::STATUS_CONFIRMED
		] );
		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );
		$page		= (int) $this->request->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsHandled( string $fillId ): void
	{
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, [
			'status'	=> Model_Form_Fill::STATUS_HANDLED
		] );
		$page		= (int) $this->request->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		DomainException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $fillId ): void
	{
		$page		= (int) $this->request->get( 'page' );
		if( !$fillId )
			throw new DomainException( 'No fill ID given' );
		$fill	= $this->modelFill->get( $fillId );
		if( !$fill )
			throw new DomainException( 'Invalid fill ID given' );
		$this->modelFill->remove( $fillId );
		$this->restart( $page ? '/'.$page : '', TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function resendManagerMails( string $fillId ): void
	{
		$this->logicFill->sendManagerResultMails( $fillId );
		$page		= (int) $this->request->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	/**
	 *	@param		string		$fillId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $fillId ): void
	{
		$fill	= $this->checkId( $fillId );
		$form	= $this->modelForm->get( $fill->formId );
		$form->transferRules	= [];
		foreach( $this->modelTransferRule->getAllByIndex( 'formId', $fill->formId ) as $transferRule )
			$form->transferRules[$transferRule->formTransferRuleId]	= $transferRule;
		$this->addData( 'fill', $fill );
		$this->addData( 'form', $form );
		$this->addData( 'fillTransfers', $this->modelFillTransfer->getAllByIndex( 'fillId', $fillId ) );
		$this->addData( 'transferTargetMap', $this->transferTargetMap );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request				= $this->env->getRequest();
		$this->session				= $this->env->getSession();
		$this->modelForm			= new Model_Form( $this->env );
		$this->modelFill			= new Model_Form_Fill( $this->env );
		$this->modelTransferTarget	= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransferRule	= new Model_Form_Transfer_Rule( $this->env );
		$this->modelFillTransfer	= new Model_Form_Fill_Transfer( $this->env );
		$this->logicMail			= Logic_Mail::getInstance( $this->env );
		$this->logicFill			= new Logic_Form_FillManager( $this->env );

		foreach( $this->modelTransferTarget->getAll() as $target )
			$this->transferTargetMap[$target->formTransferTargetId]	= $target;
	}

	/**
	 *	@param		int|string		$fillId
	 *	@param		bool			$strict
	 *	@return		object
	 *	@throws		RuntimeException	if no ID given
	 *	@throws		DomainException		if invalid ID given
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkId( int|string $fillId, bool $strict = TRUE ): object
	{
		return $this->logicFill->get( $fillId, $strict );
	}
}
