<?php
class Logic_Payment_Mangopay_Event_Payin_Normal_Created extends Logic_Payment_Mangopay_Event_Payin_Normal{

	public function handle(){
		$payin		= $this->entity;
		$data		= array(
			"status"		=> Model_Mangopay_Payin::STATUS_CREATED,
			"id"			=> $this->event->id,
			"userId"		=> $payin->AuthorId,
			"data"			=> json_encode( array(
				'created'	=> $payin,
				'failed'	=> NULL,
				'succeeded'	=> NULL,
			) ),
			"type"			=> Model_Mangopay_Payin::getTypeId( $payin->PaymentType ),
			"currency"		=> $payin->CreditedFunds->Currency,
			"amount"		=> $payin->CreditedFunds->Amount / 100,
//			"fees"			=> $payin->Fees->Amount / 100,
			"createdAt"		=> $this->event->triggeredAt,
			"modifiedAt"	=> $this->event->triggeredAt,
		);
		$payinId		= $this->modelPayin->add( $data );
		$this->uncache( 'wallet_'.$payin->CreditedWalletId.'_transactions' );

		$data			= $this->modelPayin->get( $payinId );
		$data->status	= Model_Mangopay_Payin::getStatusLabel( $data->status );
		$data->type		= Model_Mangopay_Payin::getTypeLabel( $data->type );
		unset( $data->data );
		$mailData	= array(
			'payin'			=> $payin,
			'data'			=> $data,
			'user'			=> $this->logicMangopay->getUser( $payin->AuthorId ),
			'event'			=> $this->event,
		);
		$receiver	= array( 'email' => 'dev@ceusmedia.de' );
		$this->sendMail( 'Mangopay_Event_Payin', $mailData, $receiver, 'de' );
		return time();
	}
}
