<?php
class Logic_Payment_Mangopay_Event_Payin_Normal_Failed extends Logic_Payment_Mangopay_Event_Payin_Normal{

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
		$data->data->failed		= $payin;
		$data->data				= json_encode( $data->data );
		$data->status			= Model_Mangopay_Payin::STATUS_FAILED;
		$data->modifiedAt		= $this->event->triggeredAt;
		$this->modelPayin->edit( $data->payinId, (array) $data );

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
		$receiver	= array( 'email' => 'dev@ceusmedia.de' );
		$this->sendMail( 'Mangopay_Event_Payin', $mailData, $receiver, 'de' );
		return time();
	}
}
