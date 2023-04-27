<?php
class Logic_Payment_Stripe_Event_Payin_Normal_Created extends Logic_Payment_Stripe_Event_Payin_Normal{

	public function handle(){
		$payin		= $this->entity;
		$data		= array(
			"status"		=> Model_Stripe_Payin::STATUS_CREATED,
			"id"			=> $this->event->id,
			"userId"		=> $payin->AuthorId,
			"data"			=> json_encode( [
				'created'	=> $payin,
				'failed'	=> NULL,
				'succeeded'	=> NULL,
			] ),
			"type"			=> Model_Stripe_Payin::getTypeId( $payin->PaymentType ),
			"currency"		=> $payin->CreditedFunds->Currency,
			"amount"		=> $payin->CreditedFunds->Amount / 100,
//			"fees"			=> $payin->Fees->Amount / 100,
			"createdAt"		=> $this->event->triggeredAt,
			"modifiedAt"	=> $this->event->triggeredAt,
		);
		$payinId		= $this->modelPayin->add( $data );
		$this->uncache( 'wallet_'.$payin->CreditedWalletId.'_transactions' );

		$data			= $this->modelPayin->get( $payinId );
		$data->status	= Model_Stripe_Payin::getStatusLabel( $data->status );
		$data->type		= Model_Stripe_Payin::getTypeLabel( $data->type );
		unset( $data->data );
		$mailData	= array(
			'payin'			=> $payin,
			'data'			=> $data,
			'user'			=> $this->logicStripe->getUser( $payin->AuthorId ),
			'event'			=> $this->event,
		);
		$receiver	= ['email' => 'dev@ceusmedia.de'];
		$this->sendMail( 'Stripe_Event_Payin', $mailData, $receiver, 'de' );
		return time();
	}
}
