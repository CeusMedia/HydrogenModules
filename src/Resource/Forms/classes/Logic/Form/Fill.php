<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Address as MailAddress;

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

	/**
	 *	@param		int|string		$fillId
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function applyTransfers( int|string $fillId ): array
	{
		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->modelFill->get( $fillId );
		if( NULL === $fill )
			throw new DomainException( 'Invalid fill given' );

		/** @var Entity_Form_Transfer_Rule[] $rules */
		$rules	= $this->modelTransferRule->getAllByIndex( 'formId', $fill->formId );
		if( !$rules )
			return [];

		$formData	= [];
		foreach( json_decode( $fill->data ) as $fieldName => $fieldParameters ){
			$formData[$fieldName]	= $fieldParameters->value;
		}

//		/** @var Entity_Form $form */
//		$form		= $this->modelForm->get( $fill->formId );

		$parser		= new JsonParser;
		$mapper		= new Logic_Form_Transfer_DataMapper( $this->env );

		$transfers		= [];
		foreach( $rules as $rule ){
			if( '' === trim( $rule->rules ) )
				continue;

			/** @var ?Entity_Form_Transfer_Target $target */
			$target	= $this->modelTransferTarget->get( $rule->formTransferTargetId );
			if( Model_Form_Transfer_Target::STATUS_DISABLED === $target->status )
				continue;

			$transferData	= $formData;
			$transfer		= Entity_Form_Transfer_Quest::fromArray( [
				'target'	=> $target,
				'rule'		=> $rule,
				'formData'	=> $formData,
				'data'		=> [],
			] );
			$reportData	= [
				'formId'				=> $transfer->rule->formId,
				'formTransferRuleId'	=> $transfer->rule->formTransferRuleId,
				'formTransferTargetId'	=> $transfer->target->formTransferTargetId,
				'fillId'				=> $fillId,
				'status'				=> Model_Form_Fill_Transfer::STATUS_UNKNOWN,
				'data'					=> json_encode( $transferData ),
				'createdAt'				=> time(),
			];
			try{
				$ruleSet				= $parser->parse( $rule->rules );
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
						$transfer->status		= 'transferred';
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

	/**
	 *	@param		int|string		$fillId
	 *	@param		bool			$strict
	 *	@return		?Entity_Form_Fill
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function checkId( int|string $fillId, bool $strict = TRUE ): ?Entity_Form_Fill
	{
		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->modelFill->get( $fillId );
		if( NULL === $fill ){
			if( $strict )
				throw new RuntimeException( 'Invalid fill ID' );
			return NULL;
		}
		return $fill;
	}

	/**
	 *	@param		int|string		$fillId
	 *	@param		bool			$strict
	 *	@return		?Entity_Form_Fill
	 *	@throws		RuntimeException	if no ID given
	 *	@throws		DomainException		if invalid ID given
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $fillId, bool $strict = TRUE ): ?Entity_Form_Fill
	{
		$fillId	= (int) $fillId;
		if( 0 === $fillId ){
			if( $strict )
				throw new RuntimeException( 'No fill ID given' );
			return NULL;
		}

		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->modelFill->get( $fillId );
		if( NULL === $fill ){
			if( $strict )
				throw new DomainException( 'Invalid fill ID given' );
			return NULL;
		}
		return $fill;
	}

	/**
	 *	@param		int|string		$fillId
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendConfirmMail( int|string $fillId ): bool
	{
		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->modelFill->get( $fillId );
		if( NULL === $fill )
			throw new DomainException( 'Invalid fill given' );
		if( !$fill->email )
			return FALSE;
		/** @var ?Entity_Form $form */
		$form		= $this->modelForm->get( $fill->formId );
		/** @var ?Entity_Form_Mail $formMail */
		$formMail	= $this->modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( NULL === $formMail )
			throw new RuntimeException( 'No confirmation mail defined' );

		//  -  SEND MAIL  --  //
		$config		= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender		= $this->createSenderMailAddress( $config->getAll( 'sender.', TRUE ) );

//		NOT YET IMPLEMENTED ?
//		if( isset( $form->senderAddress ) && $form->senderAddress )
//			$sender		= $form->senderAddress;
		$data		= [
			'fill'				=> $fill,
			'form'				=> $form,
			'mailTemplateId'	=> $config->get( 'template' ),
		];
		$mail		= new Mail_Form_Customer_Confirm( $this->env, $data );
		$mail->setSubject( $formMail->subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) ['email' => $fill->email];
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	/**
	 *	@param		int|string		$fillId
	 *	@return		?bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendCustomerResultMail( int|string $fillId ): ?bool
	{
		$fill	= $this->checkId( $fillId );
		if( '' === trim( $fill->email ) )
			return NULL;

		/** @var ?Entity_Form $form */
		$form		= clone $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );

		/** @var Entity_Form_Rule[] $rulesets */
		$rulesets	= $this->modelRule->getAllByIndices( [
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_CUSTOMER,
		] );
		foreach( $rulesets as $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			if( count( $ruleset->rules ) ){
				$valid	= TRUE;
				foreach( $ruleset->rules as $rule ){
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
		/** @var ?Entity_Form_Mail $formMail */
		$formMail		= $this->modelMail->get( $form->customerMailId );
		if( NULL === $formMail )
			throw new DomainException( 'Invalid mail ID ('.$form->customerMailId.') connected to form ('.$form->formId.')' );

		$form->attachments	= [];
		/** @var Entity_Form_Rule[] $rulesets */
		$rulesets	= $this->modelRule->getAllByIndices( [
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_ATTACHMENT,
		] );
//print_m( $rulesets );

		foreach( $rulesets as $ruleset ){
			$ruleset->rules	= json_decode( $ruleset->rules );
			if( count( $ruleset->rules ) ){
				$valid	= TRUE;
				foreach( $ruleset->rules as $rule ){
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
		$config		= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender		= $this->createSenderMailAddress( $config->getAll( 'sender.', TRUE ) );

//		NOT YET IMPLEMENTED ?
//		if( isset( $form->senderAddress ) && $form->senderAddress )
//			$sender		= $form->senderAddress;

		$subject	= $formMail->subject ?: 'Anfrage erhalten';
		$mail		= new Mail_Form_Customer_Result( $this->env, [
			'fill'				=> $fill,
			'form'				=> $form,
			'mail'				=> $formMail,
			'mailTemplateId'	=> $config->get( 'template' ),
		] );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) ['email' => $fill->email];
		return $this->logicMail->handleMail( $mail, $receiver, $language );
	}

	/**
	 *	@param		int|string		$formId
	 *	@param		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendManagerErrorMail( int|string $formId, $data ): void
	{
		$config		= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender		= $this->createSenderMailAddress( $config->getAll( 'sender.', TRUE ) );

		/** @var ?Entity_Form $form */
		$form		= $this->modelForm->get( $formId );
		$subject	= 'Fehler bei Formular "'.$form->title.'" ('.date( 'd.m.Y' ).')';
		$mail		= new Mail_Form_Manager_Error( $this->env, [
			'form'				=> $form,
			'data'				=> $data,
			'mailTemplateId'	=> $config->get( 'template' ),
		] );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		$receiver	= (object) ['email' => $config->get( 'sender.address' )];
		$this->logicMail->handleMail( $mail, $receiver, $language );
	}

	/**
	 *	@param		int|string		$fillId
	 *	@return		?int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendManagerResultMails( int|string $fillId ): ?int
	{
		$fill		= $this->get( $fillId );
		$form		= $this->modelForm->get( $fill->formId );
		$data		= json_decode( $fill->data, TRUE );
		$receivers	= [];
		$rulesets	= $this->modelRule->getAllByIndices( [
			'formId'	=> $fill->formId,
			'type'		=> Model_Form_Rule::TYPE_MANAGER,
		] );
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

		$config		= $this->env->getConfig()->getAll( 'module.resource_forms.mail.', TRUE );
		$sender		= $this->createSenderMailAddress( $config->getAll( 'sender.', TRUE ) );

		if( isset( $form->senderAddress ) && $form->senderAddress )
			$sender		= $form->senderAddress;

		$mail		= new Mail_Form_Manager_Filled( $this->env, [
			'form'				=> $form,
			'fill'				=> $fill,
			'mailTemplateId'	=> $config->get( 'template' ),
		] );
		$mail->setSubject( $subject );
		$mail->setSender( $sender );
		$language	= $this->env->getLanguage()->getLanguage();
		foreach( $receivers as $receiver ){
			$receiver	= (object) ['email'	=> $receiver];
			$this->logicMail->handleMail( $mail, $receiver, $language );
		}
		return count( $receivers );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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

	/**
	 *	@param		?Dictionary		$config		Module config node containing sender mail address and (optionally) name
	 *	@return		MailAddress
	 */
	protected function createSenderMailAddress( ?Dictionary $config = NULL ): MailAddress
	{
		if( NULL === $config )
			$config	= $this->env->getConfig()->getAll( 'module.resource_forms.mail.sender.', TRUE );
		$sender	= new MailAddress( $config->get( 'address' ) );
		if( $config->get( 'name' ) )
			$sender->setName( $config->get( 'name' ) );
		return $sender;
	}
}
