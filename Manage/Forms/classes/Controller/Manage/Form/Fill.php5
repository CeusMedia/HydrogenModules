<?php
class Controller_Manage_Form_Fill extends CMF_Hydrogen_Controller
{
	protected $logicMail;
	protected $logicFill;

	protected $modelForm;
	protected $modelFill;
	protected $modelMail;
	protected $modelRule;
	protected $modelTransferTarget;
	protected $modelFillTransfer;
	protected $modelTransferRule;

	protected $transferTargetMap	= [];

	public function confirm( $fillId )
	{
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		$urlGlue	= preg_match( '/\?/', $fill->referer ) ? '&' : '?';
		if( $fill->status != Model_Form_Fill::STATUS_NEW ){
			if( $fill->referer )
				$this->restart( $fill->referer.$urlGlue.'rc=3', FALSE, NULL, TRUE );
			throw new DomainException( 'Fill already confirmed' );
		}
		$this->modelFill->edit( $fillId, array(
			'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		) );
		$this->logicFill->sendCustomerResultMail( $fillId );
		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );
		if( $fill->referer )
			$this->restart( $fill->referer.$urlGlue.'rc=2', FALSE, NULL, TRUE );
		$this->restart( 'confirmed/'.$fillId, TRUE );
	}

	public function testResultMails( $fillId )
	{
		$this->logicFill->sendCustomerResultMail( $fillId );
//		$this->logicFill->sendManagerResultMails( $fillId );
		$this->env->getMessenger()->noteSuccess( 'Result-Mails versendet' );
		$this->restart( 'view/'.$fillId, TRUE );
	}

	public function testTransfer( $fillId )
	{
		print_m( $this->logicFill->applyTransfers( $fillId ) );
		exit;
	}

	public function export( $format, $type, $id )
	{
		$data	= array();
		$keys	= array( 'dateCreated', 'dateConfirmed' );

		if( $type == "form" ){
			$fills	= $this->modelFill->getAllByIndex( 'formId', $id );
		}
		else if( $type == "fill" ){
			$fills	= $this->modelFill->getAllByIndex( 'fillId', $id );
		}
		foreach( $fills as $fill ){
//print_m( $fill );
			$fill->data	= json_decode( $fill->data );
			$row	= array(
				'dateCreated'	=> date( 'Y-m-d H:i:s', $fill->createdAt ),
				'dateConfirmed'	=> $fill->modifiedAt ? date( 'Y-m-d H:i:s', $fill->modifiedAt ) : '',
			);
			foreach( $fill->data as $item ){
				if( !empty( $item->valueLabel ) )
					$row[$item->name]	= $item->valueLabel;
				else
					$row[$item->name]	= $item->value;
				if( !in_array( $item->name, $keys ) ){
					$keys[]	= $item->name;
				}
			}
			$data[]	= $row;
		}
		$lines	= array( join( ';', $keys ) );
		foreach( $data as $line ){
			$row	= array(
			);
			foreach( $keys as $key ){
				$value = isset( $line[$key] ) ? $line[$key] : '';
				$row[]	= '"'.addslashes( $value ).'"';
			}
			$lines[]	= join( ';', $row );
		}
		$csv	= join( "\r\n", $lines );
		$fileName	= 'Export_'.date( 'Y-m-d_H:i:s' ).'.csv';
		Net_HTTP_Download::sendString( $csv, $fileName, TRUE );
		xmp( $csv );
//		print_m( $keys );
//		print_m( $data );
		die;
	}

	public function filter( $reset = NULL )
	{
		$session	= $this->env->getSession();
		$request	= $this->env->getRequest();
		if( $reset ){
			$session->remove( 'manage_form_fill_fillId' );
			$session->remove( 'manage_form_fill_email' );
			$session->remove( 'manage_form_fill_formId' );
			$session->remove( 'manage_form_fill_status' );
		}
		if( $request->has( 'fillId' ) )
			$session->set( 'manage_form_fill_fillId', $request->get( 'fillId' ) );
		if( $request->has( 'email' ) )
			$session->set( 'manage_form_fill_email', $request->get( 'email' ) );
		if( $request->has( 'formId' ) )
			$session->set( 'manage_form_fill_formId', $request->get( 'formId' ) );
		if( $request->has( 'status' ) )
			$session->set( 'manage_form_fill_status', $request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL )
	{
		$session		= $this->env->getSession();
		$filterFillId	= $session->get( 'manage_form_fill_fillId' );
		$filterEmail	= $session->get( 'manage_form_fill_email' );
		$filterFormId	= $session->get( 'manage_form_fill_formId' );
		$filterStatus	= $session->get( 'manage_form_fill_status' );

		$conditions		= array();
		if( strlen( trim( $filterFillId ) ) )
			$conditions['fillId']	= $filterFillId;
		if( strlen( trim( $filterEmail ) ) )
			$conditions['email']	= '%'.$filterEmail.'%';
		if( strlen( trim( $filterFormId ) ) )
			$conditions['formId']	= $filterFormId;
		if( strlen( trim( $filterStatus ) ) )
			$conditions['status']	= $filterStatus;

		$limit		= 10;
		$pages		= ceil( $this->modelFill->count( $conditions ) / $limit );
		$page		= (int) $page;
		if( $page >= $pages )
			$page	= 0;
		$orders		= array( 'fillId' => 'DESC' );
		$limits		= array( $page * $limit, $limit );
		$fills		= $this->modelFill->getAll( $conditions, $orders, $limits );
		$forms		= $this->modelForm->getAll( array(), array( 'title' => 'ASC' ) );

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

	public function markAsConfirmed( $fillId )
	{
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'	=> Model_Form_Fill::STATUS_CONFIRMED
		) );
		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	public function markAsHandled( $fillId )
	{
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'	=> Model_Form_Fill::STATUS_HANDLED
		) );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	public function receive()
	{
		error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
		$origin	= $this->env->getConfig()->get( 'module.manage_forms.origin' );
		$origin	= $origin ? $origin : $this->env->getBaseUrl();
		$origin	= rtrim( $origin, '/' );
		header( 'Access-Control-Allow-Origin: '.$origin );
		header( 'Access-Control-Allow-Credentials: true' );
		$request	= $this->env->getRequest();
//		ini_set( 'display_errors', FALSE );
//		$this->checkIsAjax();
		try{
			$this->checkIsPost();
			$data	= $request->getAll();
			if( !isset( $data['inputs'] ) || !$data['inputs'] )
				throw new Exception( 'No form data given.' );
			if( !( $formId = $request->get( 'formId' ) ) )
				throw new Exception( 'No form ID given.' );
			if( !preg_match( '/^[0-9]+$/', $formId ) )
				throw new Exception( 'Invalid form ID given.' );
			$form		= $this->modelForm->get( $formId );
//			if( $data['inputs']['surname']['value'] === "Testmann" )
//				throw new Exception( 'Hallo Herr Testmann!' );
			$email		= '';
			$captcha	= '';
			foreach( $data['inputs'] as $nr => $input ){
				if( $input['name'] === 'email' )
					$email	= strip_tags( $input['value'] );
				if( $input['name'] === 'captcha' ){
					$captcha	= $input['value'];
					unset( $data['inputs'][$nr] );
				}
			}
			if( $captcha ){
				if( !View_Helper_Captcha::checkCaptcha( $this->env, $captcha ) ){
					header( 'Content-Type: application/json' );
					print( json_encode( array( 'status' => 'captcha', 'data' => array(
						'captcha'	=> $captcha,
						'real'		=> $this->env->getSession()->get( 'captcha' ),
						'formId'	=> $formId,
						'formType'	=> @$form->type,
					) ) ) );
					exit;
				}
			}
			if( !isset( $input) )
				throw new DomainException( 'No form ID given.' );

			$status		= Model_Form_Fill::STATUS_CONFIRMED;
			if( $form->type == Model_Form::TYPE_CONFIRM )
				$status	= Model_Form_Fill::STATUS_NEW;

			foreach( $data['inputs'] as $index => $input )
				$input['value']	= strip_tags( $input['value'] );

			$data		= array(
				'formId'	=> $formId,
				'status'	=> $status,
				'email'		=> strip_tags( $email ),
//				'data'		=> json_encode( $data['inputs'], JSON_PRETTY_PRINT ),
				'data'		=> json_encode( $data['inputs'] ),
				'referer'	=> getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
				'agent'		=> strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
				'createdAt'	=> time(),
			);
			$fillId		= $this->modelFill->add( $data, FALSE );
			if( $form->type == Model_Form::TYPE_NORMAL ){
				$this->logicFill->sendCustomerResultMail( $fillId );
				$this->logicFill->sendManagerResultMails( $fillId );
				$this->logicFill->applyTransfers( $fillId );
			}
			else if( $form->type == Model_Form::TYPE_CONFIRM ){
				$this->logicFill->sendConfirmMail( $fillId );
			}
			$status	= 'ok';
			$data	= array(
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
			);
		}
		catch( Exception $e ){
//			$this->logicFill->sendManagerErrorMail( @$data );
			$status	= 'error';
			$data	= array(
				'error'		=> $e->getMessage(),
				'trace'		=> $e->getTraceAsString(),
				'formId'	=> @$form->formId,
				'formType'	=> @$form->type,
			);
		}
		header( 'Content-Type: application/json' );
		print( json_encode( array( 'status' => $status, 'data' => $data ) ) );
		exit;
	}

	public function remove( $fillId )
	{
		$page		= (int) $this->env->getRequest()->get( 'page' );
		if( !$fillId )
			throw new DomainException( 'No fill ID given' );
		$fill	= $this->modelFill->get( $fillId );
		if( !$fill )
			throw new DomainException( 'Invalid fill ID given' );
		$this->modelFill->remove( $fillId );
		$this->restart( $page ? '/'.$page : '', TRUE );
	}

	public function resendManagerMails( $fillId )
	{
		$this->logicFill->sendManagerResultMails( $fillId );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	public function view( $fillId )
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

	protected function __onInit()
	{
		$this->modelForm			= new Model_Form( $this->env );
		$this->modelFill			= new Model_Form_Fill( $this->env );
		$this->modelMail			= new Model_Form_Mail( $this->env );
		$this->modelRule			= new Model_Form_Rule( $this->env );
		$this->modelTransferTarget	= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransferRule	= new Model_Form_Transfer_Rule( $this->env );
		$this->modelFillTransfer	= new Model_Form_Fill_Transfer( $this->env );
		$this->logicMail			= Logic_Mail::getInstance( $this->env );
		$this->logicFill			= $this->getLogic( 'formFill' );

		foreach( $this->modelTransferTarget->getAll() as $target )
			$this->transferTargetMap[$target->formTransferTargetId]	= $target;
	}

	protected function checkId( $fillId, bool $strict = TRUE )
	{
		return $this->logicFill->get( $fillId, $strict );
	}

	protected function checkIsAjax( bool $strict = TRUE )
	{
		if( $request->isAjax() )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'AJAX requests allowed only' );
		return FALSE;
	}

	protected function checkIsPost( bool $strict = TRUE )
	{
		if( $this->env->getRequest()->getMethod()->is( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}
}
