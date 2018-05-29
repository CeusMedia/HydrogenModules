<?php
class Controller_Manage_Form_Fill extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;
	protected $modelMail;

	public function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
	}

	protected function checkId( $fillId ){
		$fillId	= (int) $fillId;
		if( !$fillId )
			throw new RuntimeException( 'No fill ID given' );
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill ID given' );
		return $fill;
	}

	protected function checkIsAjax( $strict = TRUE ){
		if( $request->isAjax() )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'AJAX requests allowed only' );
		return FALSE;
	}

	protected function checkIsPost( $strict = TRUE ){
		if( $this->env->getRequest()->isMethod( 'POST' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	public function confirm( $fillId ){
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
		$this->sendResultMail( $fillId );
		$this->sendFillToReceivers( $fillId );
		if( $fill->referer )
			$this->restart( $fill->referer.$urlGlue.'rc=2', FALSE, NULL, TRUE );
		$this->restart( 'confirmed/'.$fillId, TRUE );
	}

	public function filter( $reset = NULL ){
		$session	= $this->env->getSession();
		$request	= $this->env->getRequest();
		if( $reset ){
			$session->remove( 'manage_form_fill_email' );
			$session->remove( 'manage_form_fill_formId' );
			$session->remove( 'manage_form_fill_status' );
		}
		if( $request->has( 'email' ) )
			$session->set( 'manage_form_fill_email', $request->get( 'email' ) );
		if( $request->has( 'formId' ) )
			$session->set( 'manage_form_fill_formId', $request->get( 'formId' ) );
		if( $request->has( 'status' ) )
			$session->set( 'manage_form_fill_status', $request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL ){
		$session		= $this->env->getSession();
		$filterEmail	= $session->get( 'manage_form_fill_email' );
		$filterFormId	= $session->get( 'manage_form_fill_formId' );
		$filterStatus	= $session->get( 'manage_form_fill_status' );

		$conditions		= array();
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

		$this->addData( 'filterEmail', $filterEmail );
		$this->addData( 'filterFormId', $filterFormId );
		$this->addData( 'filterStatus', $filterStatus );
	}

	public function receive(){
		$origin	= $this->env->getConfig()->get( 'module.manage_forms.origin' );
		$origin	= $origin ? $origin : $this->env->baseUrl;
		$origin	= rtrim( $origin, '/' );
		header( 'Access-Control-Allow-Origin: '.$origin );
		header( 'Access-Control-Allow-Credentials: true' );
//		ini_set( 'display_errors', FALSE );
		$request	= $this->env->getRequest();
//		$this->checkIsAjax();
		try{
//			$this->checkIsPost();
			$data	= $request->getAll();
			if( !isset( $data['inputs'] ) || !$data['inputs'] )
				throw new Exception( 'No form data given.' );

			$formId		= 0;
			$captcha	= '';
			$email		= '';
			foreach( $data['inputs'] as $nr => $input ){
				if( $input['name'] === 'formId' ){
					$formId	= $input['value'];
					unset( $data['inputs'][$nr] );
				}
				else if( $input['name'] === 'captcha' ){
					$captcha	= $input['value'];
					unset( $data['inputs'][$nr] );
				}
				else if( $input['name'] === 'email' )
					$email	= $input['value'];
			}
			if( !$formId )
				throw new DomainException( 'No form ID given.' );
			$form		= $this->modelForm->get( $formId );
			if( $captcha ){
				if( !View_Helper_Captcha::checkCaptcha( $this->env, $captcha ) ){
					header( 'Content-Type: application/json' );
					print( json_encode( array( 'status' => 'captcha', 'data' => array(
						'captcha'	=> $captcha,
						'real'		=> $this->env->getSession()->get( 'captcha' ),
						'formId'	=> @$form->formId,
						'formType'	=> @$form->type,
					) ) ) );
					exit;
				}
			}
			if( !isset( $input) )
				throw new DomainException( 'No form ID given.' );

			$data		= array(
				'formId'	=> $formId,
				'status'	=> $form->type == Model_Form::TYPE_CONFIRM ? Model_Form_Fill::STATUS_NEW : Model_Form_Fill::STATUS_CONFIRMED,
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
				'formId'	=> @$form->formId,
				'formType'	=> @$form->type,
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


		//  -  SEND MAIL  --  //
		$subject		= 'DtHPS: '.$form->title.' ('.date( 'd.m.Y' ).')';
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		if( class_exists( '\CeusMedia\Mail\Participant' ) )
			$sender			= new \CeusMedia\Mail\Participant( $configResource->get( 'sender.address' ) );
		else
			$sender			= new \CeusMedia\Mail\Address( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );
		if( isset( $form->senderAddress ) && $form->senderAddress )
			$sender		= $form->senderAddress;
		$mail		= new Mail_Form_Manager_Filled( $this->env, array(
			'form'				=> $form,
			'fill'				=> $fill,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		) );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		foreach( $receivers as $receiver ){
			$receiver	= (object) array( 'email'	=> $receiver );
			$this->logicMail->handleMail( $mail, $receiver, $language );
		}
		return count( $receivers );
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

		//  -  SEND MAIL  --  //
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		if( class_exists( '\CeusMedia\Mail\Participant' ) )
			$sender			= new \CeusMedia\Mail\Participant( $configResource->get( 'sender.address' ) );
		else
			$sender			= new \CeusMedia\Mail\Address( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );
		if( isset( $form->senderAddress ) && $form->senderAddress )
			$sender		= $form->senderAddress;
		$data		= array(
			'fill'				=> $fill,
			'form'				=> $form,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		);
		$mail		= new Mail_Form_Customer_Confirm( $this->env, $data );
		$mail->setSubject( $formMail->subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) array( 'email'	=> $fill->email );
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	protected function sendResultMail( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		if( !$fill->email )
			return FALSE;
		$form		= $this->modelForm->get( $fill->formId );
		if( !$form->mailId )
			return NULL;
		$formMail		= $this->modelMail->get( $form->mailId );
		if( !$formMail )
			throw new DomainException( 'Invalid mail ID connected to form' );

		//  -  SEND MAIL  --  //
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		if( class_exists( '\CeusMedia\Mail\Participant' ) )
			$sender			= new \CeusMedia\Mail\Participant( $configResource->get( 'sender.address' ) );
		else
			$sender			= new \CeusMedia\Mail\Address( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );
		if( isset( $form->senderAddress ) && $form->senderAddress )
			$sender		= $form->senderAddress;
		$subject	= $formMail->subject ? $formMail->subject : 'DtHPS: Anfrage erhalten';
		$mail		= new Mail_Form_Customer_Result( $this->env, array(
			'fill'				=> $fill,
			'form'				=> $form,
			'mail'				=> $formMail,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		) );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) array( 'email'	=> $fill->email );
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	public function view( $fillId ){
		$fill	= $this->checkId( $fillId );
		$this->addData( 'fill', $fill );
		$this->addData( 'form', $this->modelForm->get( $fill->formId ) );
	}
}
