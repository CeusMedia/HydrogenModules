<?php
class Model_Finance_Bank_Account_Reader_DWS{

	protected $account;
	protected $userAgent;
	protected $url;
	
	public function __construct( $account ){
		$this->account		= $account;
		$this->userAgent	= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.19 (KHTML, like Gecko) Ubuntu/11.10 Chromium/18.0.1025.151 Chrome/18.0.1025.151 Safari/535.19';
		$this->urlLogin		= 'https://depot.dws.de/';
		$this->urlLogout	= 'https://banking.dkb.de/dkb/-?$part=DkbTransactionBanking.login-status&$event=logout';
	}

	protected function fetchAccountUsingCurl(){
		$ch = curl_init();
		$cookieFile	= 'cookies.jar';
		curl_setopt( $ch, CURLOPT_URL, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_REFERER, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
		$html	= curl_exec( $ch );

		$clickId	= substr( $html, strpos( $html, "<script>Add_ClickGuidToForm1('" ) + 30 );
		$clickId	= substr( $clickId, 0, strpos( $clickId, "');</script><script>" ) );
		$state		= substr( $html, strpos( $html, 'id="__VIEWSTATE" value="' ) + 24 );
		$this->state		= substr( $state, 0, strpos( $state, '"' ) );
#		xmp( $clickId );
#		xmp( $this->state );
#		die;

		curl_setopt( $ch, CURLOPT_URL, $this->urlLogin.'?ClickGuid='.$clickId );
		curl_setopt( $ch, CURLOPT_HEADER, TRUE );
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
xmp( $html );
die;
		$doc	= new DomDocument();
		$xml	= @$doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );
		$path	= $xml->xpath( "//a" );
		if( count( $path ) == 1 ){
			curl_setopt( $ch, CURLOPT_URL, (string) $path[0]->attributes()->href );
			$html	= curl_exec( $ch );
			return $html;
		}
		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}

	protected function getPostString(){
		$data	 = array(
			'_ctl0:MainPlaceHolder:mainPanel:loginPanel:txtUserID'		=> $this->account->username,
			'_ctl0:MainPlaceHolder:mainPanel:loginPanel:passwordBox'	=> $this->account->password,
			'__EVENTTARGET'			=> '_ctl0$MainPlaceHolder$mainPanel$loginPanel$btnGo$btnGoLinkButton',
			'__EVENTARGUMENT'		=> '',
			'__TransferField'		=> '',
			'__VIEWSTATEENCRYPTED'	=> '',
			'__VIEWSTATE'			=> $this->state,
			'SessionID'				=> '@-1',
			'__EVENTVALIDATION'		=> '+LTNAt8l8hoD18Koia0M5a9XLGei65qdbardAZ2UeIMbeR6wp+aPE1FXsLcmZTo1QMsdksRZKP6Y52EjiWflzw==',
		);
		return http_build_query( $data, NULL, '&' );
	}

	public function getAccountValues(){
		if( !file_exists( $this->account->cacheFile ) ){
			$html	= $this->fetchAccountUsingCurl();
			FS_File_Writer::save( $this->account->cacheFile, $html );
		}
		else
			$html	= FS_File_Reader::load( $this->account->cacheFile );
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
