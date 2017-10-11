<?php
class Logic_Payment_Mangopay_Event_Payin_Normal_Succeeded extends Logic_Payment_Mangopay_Event_Payin_Normal{

	public function handle(){
		$indices	= array(
			'status' 	=> Model_Mangopay_Payin::STATUS_CREATED,
			'id'		=> $this->event->id,
		);
		$data		= $this->modelPayin->getByIndices( $indices );
		if( !$data )
			return FALSE;
		$payin		= $this->logicMangopay->getPayin( $this->event->id );
		$data->data				= json_decode( $data->data );
		$data->data->succeeded	= $payin;
		$data->data				= json_encode( $data->data );
		$data->status			= Model_Mangopay_Payin::STATUS_SUCCEEDED;
		$data->currency			= $payin->DebitedFunds->Currency;
		$data->amount			= $payin->DebitedFunds->Amount / 100;
		$data->modifiedAt		= $this->event->triggeredAt;
		$this->modelPayin->edit( $data->payinId, (array) $data );

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
