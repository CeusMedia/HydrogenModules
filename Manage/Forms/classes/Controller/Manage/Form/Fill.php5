<?php
class Controller_Manage_Form_Fill extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;
	protected $modelMail;

	public function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	protected function checkId( $fillId ){
		if( !$fillId )
			throw new RuntimeException( 'No fill ID given' );
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill ID given' );
		return $fill;
	}

	protected function checkIsPost(){
		if( $this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function confirm( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		$urlGlue	= preg_match( '/\?/', $fill->referer ) ? '&' : '?';
		if( $fill->status != Model_Fill::STATUS_NEW ){
			if( $fill->referer )
				$this->env->restart( $fill->referer.$urlGlue.'rc=3' );
			throw new DomainException( 'Fill already confirmed' );
		}
		$this->modelFill->edit( $fillId, array(
			'status'		=> Model_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		) );
		$this->sendResultMail( $fillId );
		$this->sendFillToReceivers( $fillId );
		if( $fill->referer )
			$this->restart( $fill->referer.$urlGlue.'rc=2' );
		$this->restart( 'confirmed/'.$fillId, TRUE );
	}

	protected function getTransport(){
		$config		= $this->env->getConfig()->getAll( 'smtp.', TRUE );
		$transport	= new \CeusMedia\Mail\Transport\SMTP(
			$config->get( 'host' ),
			$config->get( 'port' ),
			$config->get( 'username' ),
			$config->get( 'password' )
		);
		return $transport;
	}

	public function index( $page = NULL ){

		$limit		= 10;
		$pages		= ceil( $this->modelFill->count() / $limit );
		$page		= (int) $page;
		if( $page >= $pages )
			$page	= 0;
		$orders		= array( 'fillId' => 'DESC' );
		$limits		= array( $page * $limit, $limit );
		$fills		= $this->modelFill->getAll( array(), $orders, $limits );

		$this->addData( 'fills', $fills );
		$this->addData( 'page', $page );
		$this->addData( 'pages', $pages );
		$this->addData( 'limit', $limit );
	}

	public function receive(){
		header('Access-Control-Allow-Origin: *');
		ini_set( 'display_errors', FALSE );
		$request	= $this->env->getRequest();
//		if( !$request->isAjax() )
//			throw new RuntimeException( 'AJAX requests allowed only' );
		try{
//			if( !$request->isMethod( 'POST' ) )
//				throw new RuntimeException( 'POST requests allowed only' );
			$data	= $request->getAll();
//			if( !isset( $data['inputs'] ) || !$data['inputs'] )
//				throw new Exception( 'No form data given.' );

			$formId	= 0;
			$email	= '';
			foreach( $data['inputs'] as $nr => $input ){
				if( $input['name'] === 'formId' ){
					$formId	= $input['value'];
					unset( $data['inputs'][$nr] );
				}
				else if( $input['name'] === 'email' )
					$email	= $input['value'];
			}
			if( !$formId )
				throw new DomainException( 'No form ID given.' );

			$form		= $this->modelForm->get( $formId );
			$data		= array(
				'formId'	=> $formId,
				'status'	=> $form->type == Model_Form::TYPE_CONFIRM ? Model_Fill::STATUS_NEW : Model_Fill::STATUS_CONFIRMED,
				'email'		=> $email,
				'data'		=> json_encode( $data['inputs'], JSON_PRETTY_PRINT ),
				'referer'	=> getEnv( 'HTTP_REFERER' ) ? getEnv( 'HTTP_REFERER' ) : '',
				'agent'		=> getEnv( 'HTTP_USER_AGENT' ),
				'createdAt'	=> time(),
			);
			$fillId		= $this->modelFill->add( $data );
			if( $form->type == Model_Form::TYPE_NORMAL ){
				$this->sendResultMail( $fillId );
				$this->sendFillToReceivers( $fillId );
			}
			else if( $form->type == Model_Form::TYPE_CONFIRM ){
				$this->sendConfirmMail( $fillId );
			}
			$status	= 'ok';
			$data	= array(
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
			);
		}
		catch( Exception $e ){
			$status	= 'error';
			$data	= array(
				'error'		=> $e->getMessage(),
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
			);
		}
		header( 'Content-Type: application/json' );
		print( json_encode( array( 'status' => $status, 'data' => $data ) ) );
		exit;
	}

	public function remove( $fillId ){
		$page		= (int) $this->env->getRequest()->get( 'page' );
		if( !$fillId )
			throw new DomainException( 'No fill ID given' );
		$fill	= $this->modelFill->get( $fillId );
		if( !$fill )
			throw new DomainException( 'Invalid fill ID given' );
		$this->modelFill->remove( $fillId );
		$this->restart( $page ? '/'.$page : '', TRUE );
	}

	protected function sendFillToReceivers( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		$form		= $this->modelForm->get( $fill->formId );
		$transport	= $this->getTransport();

		$receivers	= array();
		if( strlen( trim( $form->receivers ) ) )
			foreach( preg_split( '/\s*,\s*/', $form->receivers ) as $receiver )
				if( preg_match( '/^\S+@\S+$/', $receiver ) )
					$receivers[]	= $receiver;
		$data	= json_decode( $fill->data, TRUE );
		if( isset( $data['base'] ) && strlen( trim( $data['base'] ) ) )
			foreach( preg_split( '/\s*,\s*/', $data['base']['value'] ) as $receiver )
				if( preg_match( '/^\S+@\S+$/', $receiver ) )
					$receivers[]	= $receiver;

		if( isset( $data['interestBase'] ) && strlen( trim( $data['interestBase'] ) ) )
			foreach( preg_split( '/\s*,\s*/', $data['interestBase']['value'] ) as $receiver )
				if( preg_match( '/^\S+@\S+$/', $receiver ) )
					$receivers[]	= $receiver;


		$mail		= new Mail_Manager_Filled( $this->app );
		$mail->setFill( $fill )->setForm( $form );
		foreach( $receivers as $receiver ){
			$message	= new \CeusMedia\Mail\Message();
			$message->addHtml( $mail->render() );
			$message->setSubject( 'DtHPS: '.$form->title.' ('.date( 'd.m.Y' ).')' );
			$message->setSender( $this->env->getConfig()->get( 'app.email' ) );
			$message->addRecipient( $receiver );
			$message->addInlineImage( 'image1', 'inc/DTHPS_LOGO.png' );
			$transport->send( $message );
		}
		return TRUE;
	}

	protected function sendConfirmMail( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		if( !$fill->email )
			return FALSE;
		$form		= $this->modelForm->get( $fill->formId );
		$formMail	= $this->modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( !$formMail )
			throw new RuntimeException( 'No confirmation mail defined' );

		$transport	= $this->getTransport();
		$mail		= new Mail_Customer_Confirm( $this->app );
		$mail->setFill( $fill )->setForm( $form );
		$message	= new \CeusMedia\Mail\Message();
		$message->setSubject( $formMail->subject );
		$message->setSender( $this->env->getConfig()->get( 'app.email' ) );
		$message->addRecipient( $fill->email );
		if( $formMail->format == Model_Mail::FORMAT_HTML ){
			$message->addHtml( $mail->render() );
			$message->addInlineImage( 'image1', 'inc/DTHPS_LOGO.png' );
		}
		else
			$message->addText( $mail->render() );
		return $transport->send( $message );
	}

	protected function sendResultMail( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		if( !$fill->email )
			return FALSE;
		$form		= $this->modelForm->get( $fill->formId );
		if( !$form->mailId ){
			$default	= $this->modelMail->getByIndex( 'identifier', 'customer_result_default' );
			if( $default )
				$form->mailId	= $default->mailId;
		}
		if( !$form->mailId )
			return;
		$formMail		= $this->modelMail->get( $form->mailId );
		if( !$formMail )
			throw new DomainException( 'Invalid mail ID connected to form' );
		$transport	= $this->getTransport();
		$subject	= $formMail->subject ? $formMail->subject : 'DtHPS: Anfrage erhalten';
		$mail		= new Mail_Customer_Result( $this->app );
		$mail->setFill( $fill )->setForm( $form )->setMail( $formMail );
		$message	= new \CeusMedia\Mail\Message();
		$message->setSubject( $subject );
		$message->setSender( $this->env->getConfig()->get( 'app.email' ) );
		$message->addRecipient( $fill->email );
		if( $formMail->format == Model_Mail::FORMAT_HTML ){
			$message->addHtml( $mail->render() );
			$message->addInlineImage( 'image1', 'inc/DTHPS_LOGO.png' );
		}
		else
			$message->addText( $mail->render() );
		return $transport->send( $message );
	}
}
