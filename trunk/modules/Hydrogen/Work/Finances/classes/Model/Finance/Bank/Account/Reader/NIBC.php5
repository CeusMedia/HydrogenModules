<?php
class Model_Finance_Bank_Account_Reader_NIBC{
	
	public function __construct( $account ){
		$this->account		= $account;
		$this->userAgent	= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.19 (KHTML, like Gecko) Ubuntu/11.10 Chromium/18.0.1025.151 Chrome/18.0.1025.151 Safari/535.19';
		$this->urlBase		= 'https://finanzportal.fiducia.de';
		$this->urlLogin		= '/entry?appid=ebpe&bankid=XC2290';
		$this->urlRead		= '/p13pepe/portal?menuId=Banking&token=%s';
		$this->urlLogout	= '/p13pepe/portal?token=%s';
	}

	public function getAccountValues(){

		//  --  GET LOGIN FORM  --  //
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		@unlink( $cookieFile );
		curl_setopt( $ch, CURLOPT_URL, $this->urlBase.$this->urlLogin );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		$html	= curl_exec( $ch );

		$html	= str_replace( '&', '&amp;', $html );
		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );
		$form	= $xml->body->div->div[4]->form;
		$data	= array();
		for( $i=0; $i<4; $i++)
			$data[(string) $form->input[$i]['name']]	= (string) $form->input[$i]['value'];
		$data[(string) $form->div[1]->div->table->tr[2]->td[1]->input['name']]	= $this->account->username;
		$data[(string) $form->div[1]->div->table->tr[3]->td[1]->input['name']]	= $this->account->password;
		
		//  --  SEND LOGIN DATA  --  //
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		curl_setopt( $ch, CURLOPT_URL, $this->urlBase.$form['action'] );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data, NULL, '&' ) );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		$html	= curl_exec( $ch );
		
		$html	= str_replace( '&', '&amp;', $html );
		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );

		//  --  HINT PAGE  --  //
		if( substr_count( $html, '<h1 class="stackedFrontletTitle">Hinweis</h1>' ) ){
			$data	= array();
			$form	= $xml->body->div->div[4]->form;
			foreach( $form->input as $input )
				$data[(string) $input['name']]	= (string) $input['value'];
			$data[(string) $form->div[2]->div->input['name']]	= (string) $form->div[2]->div->input['value'];
			$ch = curl_init();
			$cookieFile	= 'cookie.jar';
			curl_setopt( $ch, CURLOPT_URL, $this->urlBase.$form['action'] );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data, NULL, '&' ) );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
			curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
			curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
			$html	= curl_exec( $ch );
			$html	= str_replace( '&', '&amp;', $html );
			$doc	= new DomDocument();
			$xml	= @$doc->loadHTML( $html );
			$xml	= simplexml_import_dom( $doc );
		}
		
		$token	= (string) $xml->body->form->input[0]['value'];
		
		//  --  GET BANKING PAGE  --  //
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		curl_setopt( $ch, CURLOPT_URL, $this->urlBase.sprintf( $this->urlRead, $token ) );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		$html	= curl_exec( $ch );
		
		$html	= str_replace( '&', '&amp;', $html );
		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );

		$table	= $xml->body->div->div[4]->form->div[1]->div->table->tr->td->table->tbody;
		
		$accounts	= array();
		for( $i=2; $i<10; $i++){
			if( isset( $table->tr[$i]->td[1]->span ) ){
				$key	= (string) $table->tr[$i]->td[1]->span;
				$value	= (string) $table->tr[$i]->td[2]->div->table->tr->td[1]->div->table->tr->td->span;
				$value	= explode( " ", $value );
				$value	= ( (float) str_replace( ',', '.', $value[0] ) ) * ( $value[1] == 'S' ? -1 : 1 );
				$accounts[$key]	= $value;
			}
		}
	
		$button	= $xml->body->div[0]->div[3]->div->form->div->div->table->tr[1]->td->div->table->tr->td->div->table->tr->td->span->input[1];
		
		$data	= array();
		$data['token']		= (string) $xml->body->form->input[0]['value'];
		$data['frontletId']	= (string) $xml->body->form->input[1]['value'];
		$data[(string) $button['name']]	= (string) $button['value'];
		
		//  --  REQUEST LOGOUT  --  //
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		curl_setopt( $ch, CURLOPT_URL, $this->urlBase.sprintf( $this->urlLogout, $data['token'] ) );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data, NULL, '&' ) );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		curl_exec( $ch );

		return $accounts;
	}
}
?>