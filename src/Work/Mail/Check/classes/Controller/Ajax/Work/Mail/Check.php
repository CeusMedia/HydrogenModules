<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Mail\Transport\SMTP\Code as SmtpCode;

class Controller_Ajax_Work_Mail_Check extends AjaxController
{
	protected MessengerResource $messenger;
	protected HttpRequest $request;
	protected Dictionary $moduleOptions;
	protected Model_Mail_Check_Address $modelAddress;
	protected Model_Mail_Check_Address_Run $modelCheck;
	protected Model_Mail_Check_Group $modelGroup;

	/**
	 *	@param		string		$addressId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function address( string $addressId ): void
	{
		/** @var array<string,array<string,string>> $words */
		$words		= $this->env->getLanguage()->getWords( 'work.mail.check' );

		/** @var object $address */
		$address	= $this->modelAddress->get( $addressId );
		if( NULL === $address )
			$this->respondError( 0, 'Invalid address ID' );

		$runs 			= HtmlTag::create('div', 'Keine Prüfungen bisher.', ['class' => 'text text-info']);
		$address->runs	= $this->modelCheck->getAllByIndex( 'mailCheckAddressId', $addressId, ['createdAt' => 'DESC'] );
		if( $address->runs ){
			$rows	= [];
			foreach( $address->runs as $run ){
				$codeLabel	= $this->renderCodeBadge( $run );
				$codeDesc	= SmtpCode::getText( $run->code );

				$errorLabel	= ucwords( strtolower( str_replace( "_", " ", $words['errorCodes'][$run->error] ) ) );
				$errorDesc	= $words['errorLabels'][$run->error];

				$facts		= $this->renderFacts( [
					'SMTP-Code'			=> $codeLabel.' <small class="muted">'.$codeDesc.'</small>',
					'Fehler'			=> $errorLabel.' <small class="muted">'.$errorDesc.'</small>',
					'Servermeldung'		=> '<not-pre>'.$run->message.'</not-pre>',
					'Datum / Uhrzeit'	=> date( 'Y-m-d', $run->createdAt ).' <small class="muted">'.date( 'H:i:s', $run->createdAt ).'</small>',
				] );
				$rows[] = HtmlTag::create( 'tr', [
					HtmlTag::create( 'td', $facts ),
				] );
			}
			$runs	= HtmlTag::create( 'table', $rows, ['class' => 'table table-striped'] );
		}
		/** @noinspection XmlDeprecatedElement */
		/** @noinspection HtmlDeprecatedTag */
		$html	= '
<big><span class="muted">Adresse: </span>'.$address->address.'</big>
<h4>Prüfungen <small class="muted">('.count( $address->runs ).')</small></h4>
'.$runs.'
<br/>
<br/>';
		$this->respondData( $html );

	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editAddress(): void
	{
		$addressId	= $this->request->get( 'id' );
		$address	= $this->request->get( 'address' );

		$result	= FALSE;
		if( $this->modelAddress->get( $addressId ) ){
			$this->modelAddress->edit( $addressId, [
				'address'	=> trim( $address ),
				'status'	=> 0
			] );
			$result	= TRUE;
		}
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleOptions	= $this->env->getConfig()->getAll( 'module.work_mail_check.', TRUE );

		//  --  PREPARE MODELS  --  //
		$this->modelAddress		= new Model_Mail_Check_Address( $this->env );
		$this->modelCheck		= new Model_Mail_Check_Address_Run( $this->env );
		$this->modelGroup		= new Model_Mail_Check_Group( $this->env );
	}

	/**
	 * @param $check
	 * @param string|NULL $label
	 * @return string
	 */
	protected function renderCodeBadge( $check, string $label = NULL ): string
	{
		$code	= $check->code;
		switch( (int) substr( $check->code, 0, 1 ) ){
			case 0:
				$code	= str_pad( $check->error, 3, "0", STR_PAD_LEFT );
				$labelCode	= 'label-inverse';
				break;
			case 1:
			case 2:
			case 3:
				$labelCode	= 'label-success';
				break;
			case 4:
				$labelCode	= 'label-warning';
				break;
			case 5:
				$labelCode	= 'label-important';
				break;
			default:
				$labelCode	= '<em>unknown</em>';
				break;
		}
		$label	= strlen( trim( $label ) ) ? trim( $label ) : $code;
		return HtmlTag::create( 'span', $label, ['class' => 'label '.$labelCode] );
	}

	/**
	 * @param array $facts
	 * @return string
	 */
	protected function renderFacts( array $facts ): string
	{
		$list	= [];
		foreach( $facts as $term => $definition ){
			$list[]	= HtmlTag::create( 'dt', $term );
			$list[]	= HtmlTag::create( 'dd', $definition );
		}
		return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
	}
}

