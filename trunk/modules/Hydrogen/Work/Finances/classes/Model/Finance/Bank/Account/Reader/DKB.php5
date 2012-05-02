<?php
class Model_Finance_Bank_Account_Reader_DKB{

	protected $account;
	protected $userAgent;
	protected $url;
	
	public function __construct( $account ){
		$this->account		= $account;
		$this->userAgent	= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.19 (KHTML, like Gecko) Ubuntu/11.10 Chromium/18.0.1025.151 Chrome/18.0.1025.151 Safari/535.19';
		$this->urlLogin		= 'https://banking.dkb.de/dkb/-';
		$this->urlLogout	= 'https://banking.dkb.de/dkb/-?$part=DkbTransactionBanking.login-status&$event=logout';
	}

	protected function fetchAccountUsingCurl(){
		$ch = curl_init();
		$cookieFile	= 'cookies.jar';
		@unlink( $cookieFile );
		curl_setopt( $ch, CURLOPT_URL, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_REFERER, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->getPostString() );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		$html	= curl_exec( $ch );

		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );
		$path	= $xml->xpath( "//a" );
		if( count( $path ) == 1 ){
			curl_setopt( $ch, CURLOPT_URL, (string) $path[0]->attributes()->href );
			$html	= curl_exec( $ch );
		}
		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}

	protected function getPostString(){
		$data	 = array(
			'j_username'		=> $this->account->account,
			'j_password'		=> $this->account->password,
		);
		return http_build_query( $data, NULL, '&' );
	}

	public function getAccountValues(){
#		if( !file_exists( $this->account->cacheFile ) ){
			$html	= $this->fetchAccountUsingCurl();
#			File_Writer::save( $this->account->cacheFile, $html );
#		}
#		else
#			$html	= File_Reader::load( $this->account->cacheFile );
		return $this->parseAccount( $html );
	}

	protected function parseAccount( $html ){
		$html	= str_replace( '&', '&amp;', $html );
		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );
		$path	= $xml->xpath( "//div[@class='body']" );

		$values	= array();
		foreach( $path[0]->form[1]->div[1]->table->tr as $row ){
			if( !$row->td || strlen( $row->td[0] ) == 0 )
				continue;
			$values[trim( $row->td[1] )]	= (float) str_replace( ',', '.', $row->td[3]->span );
		}
		return $values;
	}
}
?>