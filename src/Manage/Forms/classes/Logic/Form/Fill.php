<?php

use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Form_Fill extends Logic
{
	protected Logic_Mail $logicMail;
	protected Model_Form $modelForm;
	protected Model_Form_Fill $modelFill;
	protected Model_Form_Fill_Transfer $modelFillTransfer;
	protected Model_Form_Mail $modelMail;
	protected Model_Form_Rule $modelRule;
	protected Model_Form_Transfer_Rule $modelTransferRule;
	protected Model_Form_Transfer_Target $modelTransferTarget;

	protected array $transferTargetMap	= [];

	public function applyTransfers( $fillId ): array
	{
		if( !( $fill = $this->modelFill->get( $fillId ) ) )
			throw new DomainException( 'Invalid fill given' );

		$rules	= $this->modelTransferRule->getAllByIndex( 'formId', $fill->formId );
		if( !$rules )
			return [];

		$formData	= [];
		foreach( json_decode( $fill->data ) as $fieldName => $fieldParameters ){
			$formData[$fieldName]	= $fieldParameters->value;
		}
		$form		= $this->modelForm->get( $fill->formId );

		$parser		= new JsonParser;
		$mapper		= new Logic_Form_Transfer_DataMapper( $this->env );

		$transfers	= [];
		$transferData	= [];
		foreach( $rules as $rule ){
			$target = $this->modelTransferTarget->get( $rule->formTransferTargetId );
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
				'formTransferTargetId'	=> $transfer->target->formTransferTargetId,
				'fillId'				=> $fillId,
				'status'				=> Model_Form_Fill_Transfer::STATUS_UNKNOWN,
				'data'					=> json_encode( $transferData ),
				'createdAt'				=> time(),
			);
			try{
				$ruleSet				= $parser->parse( $rule->rules, FALSE );
				$transfer->status		= 'parsed';
				$transferData			= $mapper->applyRulesToFormData( $formData, $ruleSet );
				$transfer->data			= $transferData;
				$transfer->status		= 'applied';

				$targetId				= $transfer->target->formTransferTargetId;
				$factory				= new ObjectFactory( [$this->env] );
				$transferInstance		= $factory->create( $transfer->target->className );
				$transfer->result		= $transferInstance->transfer( $targetId, $transfer );
				$reportData['status']	= (int) $transfer->result->status;
				switch( (int) $transfer->result->status ){
					case Model_Form_Fill_Transfer::STATUS_SUCCESS:
						$transfer->status		= 'transfered';
						break;
					case Model_Form_Fill_Transfer::STATUS_ERROR:
						$transfer->status		= 'error';
//						$reportData['message']	= join( PHP_EOL, $transfer->result->errors );
						$reportData['message']	= json_encode( $transfer->result->errors, JSON_PRETTY_PRINT );
						break;
					case Model_Form_Fill_Transfer::STATUS_EXCEPTION:
						$transfer->status		= 'exception';
//						$reportData['message']	= join( PHP_EOL, $transfer->result->errors );
						$reportData['message']	= json_encode( $transfer->result->errors, JSON_PRETTY_PRINT );
						if( !empty( $transfer->result->trace ) )
							$reportData['trace']	= $transfer->result->trace;
						break;
				}
			}
			catch( Throwable $t ){
				$transfer->status		= 'exception';
				$transfer->error		= $t->getMessage();
				$this->env->getLog()->logException( $t );
				$reportData['status']	= Model_Form_Fill_Transfer::STATUS_EXCEPTION;
				$reportData['message']	= 'Exception: '.$t->getMessage().' in '.$t->getFile().'('.$t->getLine().')';
				$reportData['trace']	= $t->getTraceAsString();
			}
			$this->modelFillTransfer->add( $reportData );
			$transfers[]	= $transfer;
		}
		return $transfers;
	}

	public function checkId( $fillId, bool $strict = TRUE )
	{
		$fill	= $this->modelFill->get( $fillId );
		if( !$fill ){
			if( $strict )
				throw new RuntimeException( 'Invalid fill ID' );
			return NULL;
		}
		return $fill;
	}

	public function get( $fillId, bool $strict = TRUE )
	{
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

	/**
	 *	Returns one fill or many fills by form ID as CSV.
	 *	@access		public
	 *	@param		string		$type		Type of ID (form|fill)
	 *	@param		array		$ids		ID list of fill or form of fills
	 *	@param		int|NULL	$status		...
	 *	@return		string
	 */
	public function renderToCsv( string $type, array $ids, int $status = NULL ): string
	{
		$types	= ['fill', 'form'];
		if( !in_array( $type, $types, TRUE ) )
			throw new DomainException( 'Invalid type given' );

		$data	= [];
		$keys	= ['dateCreated', 'dateConfirmed', 'formId'];
		$indices	= [$type.'Id' => $ids];
		if( !is_null( $status ) )
			$indices['status']	= $status;
		$fills	= $this->modelFill->getAllByIndices( $indices );
		foreach( $fills as $fill ){
			$fill->data	= json_decode( $fill->data );
			$row		= [
				'dateCreated'	=> date( 'Y-m-d H:i:s', $fill->createdAt ),
				'dateConfirmed'	=> $fill->modifiedAt ? date( 'Y-m-d H:i:s', $fill->modifiedAt ) : '',
				'formId'		=> $fill->formId,
			];
			foreach( $fill->data as $item ){
				$row[$item->name]	= $item->value;
				if( !empty( $item->valueLabel ) )
					$row[$item->name]	= $item->valueLabel;
				if( !in_array( $item->name, $keys ) )
					$keys[]	= $item->name;
			}
			$data[]	= $row;
		}
		$lines	= [join( ';', $keys )];
		foreach( $data as $line ){
			$row	= [];
			foreach( $keys as $key ){
				$value	= isset( $line[$key] ) ? $line[$key] : '';
				$row[]	= '"'.addslashes( $value ).'"';
			}
			$lines[]	= join( ';', $row );
		}
		return join( "\r\n", $lines );
	}

	public function sendConfirmMail( $fillId )
	{
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
		$sender			= $this->createMailAddress( $configResource->get( 'sender.address' ) );
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
		$receiver	= (object) ['email' => $fill->email];
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	public function sendCustomerResultMail( $fillId )
	{
		$fill	= $this->checkId( $fillId );
		if( !$fill->email )
			return NULL;

		$form		= clone $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );
		$rulesets	= $this->modelRule->getAllByIndices( array(
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		) );
		foreach( $rulesets as $rulesetNr => $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			if( count( $ruleset->rules ) ){
				$valid	= TRUE;
				foreach( $ruleset->rules as $ruleNr => $rule ){
					if( !isset( $data[$rule->key] ) )
						$valid = FALSE;
					else if( (string) $data[$rule->key]['value'] !== (string) $rule->value )
						$valid = FALSE;
				}
				if( $valid ){
					$form->customerMailId	= $ruleset->mailId;
					break;
				}
			}
		}
		if( !$form->customerMailId )
			return NULL;
		$formMail		= $this->modelMail->get( $form->customerMailId );
		if( !$formMail )
			throw new DomainException( 'Invalid mail ID ('.$form->customerMailId.') connected to form ('.$form->formId.')' );

		$form->attachments	= [];
		$rulesets	= $this->modelRule->getAllByIndices( array(
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_ATTACHMENT,
		) );
//print_m( $rulesets );

		foreach( $rulesets as $rulesetNr => $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			if( count( $ruleset->rules ) ){
				$valid	= TRUE;
				foreach( $ruleset->rules as $ruleNr => $rule ){
					if( !isset( $data[$rule->key] ) )
						$valid = FALSE;
					else if( (string) $data[$rule->key]['value'] !== (string) $rule->value )
						$valid = FALSE;
				}
				if( $valid ){
					$form->attachments[]	= $ruleset->filePath;
				}
			}
		}

//print_m( $form->attachments );
//die;

		//  -  SEND MAIL  --  //
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender			= $this->createMailAddress( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );
		if( isset( $form->senderAddress ) && $form->senderAddress )
			$sender		= $form->senderAddress;
		$subject	= $formMail->subject ? $formMail->subject : 'Anfrage erhalten';
		$mail		= new Mail_Form_Customer_Result( $this->env, array(
			'fill'				=> $fill,
			'form'				=> $form,
			'mail'				=> $formMail,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		) );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) ['email' => $fill->email];
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	public function sendManagerErrorMail( $formId, $data )
	{
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender			= $this->createMailAddress( $configResource->get( 'sender.address' ) );
		if( $configResource->get( 'sender.name' ) )
			$sender->setName( $configResource->get( 'sender.name' ) );

		$form		= $this->modelForm->get( $formId );
		$subject	= 'Fehler bei Formular "'.$form->title.'" ('.date( 'd.m.Y' ).')';
		$mail		= new Mail_Form_Manager_Error( $this->env, array(
			'form'				=> $form,
			'data'				=> $data,
			'mailTemplateId'	=> $configResource->get( 'template' ),
		) );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) ['email' => $configResource->get( 'sender.address' )];
		$this->logicMail->handleMail( $mail, $receiver, $language );
	}

	public function sendManagerResultMails( $fillId )
	{
		$fill		= $this->get( $fillId );
		$form		= $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );
		$receivers	= [];
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
			foreach( [ 'base', 'interestBase' ] as $field )
				if( isset( $data[$field] ) && strlen( trim( $data[$field]['value'] ) ) )
					foreach( preg_split( '/\s*,\s*/', $data[$field]['value'] ) as $address )
						if( preg_match( '/^\S+@\S+$/', $address ) )
							$receivers[]	= $address;
		}

		$receivers		= array_unique( $receivers );

		//  -  SEND MAIL  --  //
		$subject		= $form->title.' ('.date( 'd.m.Y' ).')';
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender			= $this->createMailAddress( $configResource->get( 'sender.address' ) );
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
			$receiver	= (object) ['email'	=> $receiver];
			$this->logicMail->handleMail( $mail, $receiver, $language );
		}
		return count( $receivers );
	}

	protected function __onInit(): void
	{
		$this->logicMail			= $this->env->getLogic()->get( 'Mail' );
		$this->modelForm			= $this->getModel( 'Form' );
		$this->modelFill			= $this->getModel( 'FormFill' );
		$this->modelRule			= $this->getModel( 'FormRule' );
		$this->modelMail			= $this->getModel( 'FormMail' );
		$this->modelTransferTarget	= $this->getModel( 'FormTransferTarget' );
		$this->modelTransferRule	= $this->getModel( 'FormTransferRule' );
		$this->modelFillTransfer	= $this->getModel( 'FormFillTransfer' );

		foreach( $this->modelTransferTarget->getAll() as $target )
			$this->transferTargetMap[$target->formTransferTargetId]	= $target;
	}

	protected function createMailAddress( string $address )
	{
		if( class_exists( '\CeusMedia\Mail\Participant' ) )
			return new \CeusMedia\Mail\Participant( $address );
		return new \CeusMedia\Mail\Address( $address );
	}
}
