<?php
class Controller_Manage_Form_Fill extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;
	protected $modelMail;
	protected $modelRule;

	public function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
		$this->modelRule	= new Model_Form_Rule( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
	}

	protected function checkId( $fillId, $strict = TRUE ){
		$fillId	= (int) $fillId;
		if( !$fillId ){
			if( $strict )
				throw new RuntimeException( 'No fill ID given' );
			return NULL;
		}
		if( !( $fill = $this->modelFill->get( $fillId ) ) ){
			if( $strict )
				throw new DomainException( 'Invalid fill ID given' );
			return NULL;
		}
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
		if( $this->env->getRequest()->getMethod()->is( 'POST' ) )
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
		$this->sendCustomerResultMail( $fillId );
		$this->sendManagerResultMails( $fillId );
		$this->applyTransfers( $fillId );
		if( $fill->referer )
			$this->restart( $fill->referer.$urlGlue.'rc=2', FALSE, NULL, TRUE );
		$this->restart( 'confirmed/'.$fillId, TRUE );
	}

	public function testTransfer( $fillId ){
		$this->applyTransfers( $fillId );
		exit;
	}

	protected function applyTransfers( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );

		$modelRule		= new Model_Form_Transfer_Rule( $this->env );
		$modelTarget	= new Model_Form_Transfer_Target( $this->env );
		$rules			= $modelRule->getAllByIndex( 'formId', $fill->formId );
		if( !$rules )
			return [];

		$formData	= [];
		foreach( json_decode( $fill->data ) as $fieldName => $fieldParameters ){
			$formData[$fieldName]	= $fieldParameters->value;
		}
		$form		= $this->modelForm->get( $fill->formId );

		$parser		= new ADT_JSON_Parser;
		$mapper		= new Logic_Form_Transfer_DataMapper( $this->env );

		$transfers	= array();
		$transferData	= [];
		foreach( $rules as $rule ){
			$target = $modelTarget->get( $rule->formTransferTargetId );
			if( $target->status < Model_Form_Transfer_Target::STATUS_ENABLED )
				continue;

			$transferData	= $formData;
			$transfer	= (object) [
				'status'	=> 'none',
				'rule'		=> $rule,
				'target'	=> $target,
				'formData'	=> $formData,
				'data'		=> NULL,
				'error'		=> NULL,
			];
			$transfer->data	= $transferData;
			if( !strlen( trim( $rule->rules ) ) )
				continue;
			$transfer->data	= [];
			$reportData	= array(
				'formId'				=> $transfer->rule->formId,
				'formTransferRuleId'	=> $transfer->rule->formTransferRuleId,
				'transferTargetId'		=> $transfer->target->formTransferTargetId,
				'fillId'				=> $fillId,
				'status'				=> Model_Form_Fill_Transfer::STATUS_UNKOWN,
			);
			try{
				$ruleSet				= $parser->parse( $rule->rules, FALSE );
				$transfer->status		= 'parsed';
				$transferData			= $mapper->applyRulesToFormData( $formData, $ruleSet );
				$transfer->data			= $transferData;
				$transfer->status		= 'applied';

				$targetId				= $transfer->target->formTransferTargetId;
				$factory				= new Alg_Object_Factory( [$this->env] );
				$transferInstance		= $factory->create( $transfer->target->className );
				$transfer->result		= $transferInstance->transfer( $targetId, $transfer );
				$transfer->status		= 'transfered';
				$reportData['status']	= Model_Form_Fill_Transfer::STATUS_SUCCESS;
			}
			catch( RuntimeException $e ){
				$transfer->status		= 'failed';
				$transfer->error		= $e->getMessage();
				$this->env->getLog()->logException( $e );
				$reportData['status']	= Model_Form_Fill_Transfer::STATUS_ERROR;
				$reportData['message']	= $e->getMessage();
			}
			$modelFormFillTransfer->add( $reportData );
			$transfers[]	= $transfer;
		}
		return $transfers;
	}


	public function export( $format, $type, $id ){
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

	public function markAsConfirmed( $fillId ){
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'	=> Model_Form_Fill::STATUS_CONFIRMED
		) );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	public function markAsHandled( $fillId ){
		$this->checkId( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'	=> Model_Form_Fill::STATUS_HANDLED
		) );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
	}

	public function receive(){
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
				$this->sendCustomerResultMail( $fillId );
				$this->sendManagerResultMails( $fillId );
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
//			$this->sendManagerErrorMail( @$data );
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

	public function resendManagerMails( $fillId ){
		$this->sendManagerResultMails( $fillId );
		$page		= (int) $this->env->getRequest()->get( 'page' );
		$this->restart( 'view/'.$fillId.( $page ? '?page='.$page : '' ), TRUE );
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

	protected function sendCustomerResultMail( $fillId ){
		$fill	= $this->checkId( $fillId );
		if( !$fill->email )
			return NULL;

		$form		= $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );
		$rulesets	= $this->modelRule->getAllByIndices( array(
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		) );
		foreach( $rulesets as $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			$valid	= TRUE;
			foreach( $ruleset->rules as $rule ){
				if( !isset( $data[$rule->key] ) )
					$valid = FALSE;
				else if( $data[$rule->key]['value'] != $rule->value )
					$valid = FALSE;
			}
			if( $valid ){
				$form->customerMailId	= $ruleset->mailId;
				break;
			}
		}
		if( !$form->customerMailId )
			return NULL;
		$formMail		= $this->modelMail->get( $form->customerMailId );
		if( !$formMail )
			throw new DomainException( 'Invalid mail ID ('.$form->customerMailId.') connected to form ('.$form->formId.')' );

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

	protected function sendManagerErrorMail($formId, $data){
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		if( class_exists( '\CeusMedia\Mail\Participant' ) )
			$sender		= new \CeusMedia\Mail\Participant( $configResource->get( 'sender.address' ) );
		else
			$sender		= new \CeusMedia\Mail\Address( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );

		$form		= $this->modelForm->get( $formId );
		$subject	= 'DtHPS: Fehler bei Formular "'.$form->title.'" ('.date( 'd.m.Y' ).')';
		$mail		= new Mail_Form_Manager_Error( $this->env, array(
			'form'				=> $form,
			'data'				=> $data,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		) );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) array( 'email' => $configResource->get( 'sender.address' ) );
		$this->logicMail->handleMail( $mail, $receiver, $language );
	}

	protected function sendManagerResultMails( $fillId ){
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );
		$form		= $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );
		$receivers	= array();
		$rulesets	= $this->modelRule->getAllByIndices( array(
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_MANAGER,
		) );
		foreach( $rulesets as $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			$valid	= TRUE;
			foreach( $ruleset->rules as $rule ){
				if( !isset( $data[$rule->key] ) )
					$valid = FALSE;
				else if( $data[$rule->key]['value'] != $rule->value )
					$valid = FALSE;
			}
			if( $valid ){
				foreach( preg_split( '/\s*,\s*/', $ruleset->mailAddresses ) as $address )
					if( preg_match( '/^\S+@\S+$/', $address ) )
						$receivers[]	= $address;
			}
		}
		if( !$receivers ){
			if( strlen( trim( $form->receivers ) ) )
				foreach( preg_split( '/\s*,\s*/', $form->receivers ) as $address )
					if( preg_match( '/^\S+@\S+$/', $address ) )
						$receivers[]	= $address;
			if( isset( $data['base'] ) && strlen( trim( $data['base']['value'] ) ) )
				foreach( preg_split( '/\s*,\s*/', $data['base']['value'] ) as $address )
					if( preg_match( '/^\S+@\S+$/', $address ) )
						$receivers[]	= $address;
			if( isset( $data['interestBase'] ) && strlen( trim( $data['interestBase'] ) ) )
				foreach( preg_split( '/\s*,\s*/', $data['interestBase']['value'] ) as $address )
					if( preg_match( '/^\S+@\S+$/', $address ) )
						$receivers[]	= $address;
		}

		$receivers		= array_unique( $receivers );
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

	public function view( $fillId ){
		$fill	= $this->checkId( $fillId );
		$this->addData( 'fill', $fill );
		$this->addData( 'form', $this->modelForm->get( $fill->formId ) );
	}
}
