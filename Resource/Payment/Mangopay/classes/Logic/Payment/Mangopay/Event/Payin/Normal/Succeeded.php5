<?php
class Logic_Payment_Mangopay_Event_Payin_Normal_Succeeded extends Logic_Payment_Mangopay_Event_Payin_Normal{

	public function handle(){
		$indices	= array(
			'status' 	=> Model_Mangopay_Payin::STATUS_CREATED,
			'id'		=> $this->event->id,
		);
		$payin		= $this->entity;
		$data		= $this->modelPayin->getByIndices( $indices );
		if( !$data )
			return FALSE;
		$data->data				= json_decode( $data->data );
		$data->data->succeeded	= $payin;
		$data->data				= json_encode( $data->data );
		$data->status			= Model_Mangopay_Payin::STATUS_SUCCEEDED;
		$data->currency			= $payin->DebitedFunds->Currency;
		$data->amount			= $payin->DebitedFunds->Amount / 100;
		$data->modifiedAt		= $this->event->triggeredAt;
		$this->modelPayin->edit( $data->payinId, (array) $data );

		$this->uncache( 'user_'.$payin->CreditedUserId.'_wallets' );
		$this->uncache( 'user_'.$payin->CreditedUserId.'_wallet_'.$payin->CreditedWalletId );
		$this->uncache( 'wallet_'.$payin->CreditedWalletId.'_transactions' );

		$data			= $this->modelPayin->get( $data->payinId );
		$data->status	= Model_Mangopay_Payin::getStatusLabel( $data->status );
		$data->type		= Model_Mangopay_Payin::getTypeLabel( $data->type );
		unset( $data->data );
		$mailData	= array(
			'payin'			=> $payin,
			'data'			=> $data,
			'user'			=> $this->logicMangopay->getUser( $payin->AuthorId ),
			'event'			=> $this->event,
		);
		$receiver	= ['email' => 'dev@ceusmedia.de'];
		$this->sendMail( 'Mangopay_Event_Payin', $mailData, $receiver, 'de' );
		return time();
	}
}
