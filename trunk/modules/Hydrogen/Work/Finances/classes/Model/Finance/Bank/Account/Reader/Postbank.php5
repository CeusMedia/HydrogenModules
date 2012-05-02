<?php
class Model_Finance_Bank_Account_Reader_Postbank{
	
	protected $bank;
	protected $userAgent;
	protected $url;
	
	public function __construct( $bank ){
		$this->bank		= $bank;
		$this->userAgent	= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.19 (KHTML, like Gecko) Ubuntu/11.10 Chromium/18.0.1025.151 Chrome/18.0.1025.151 Safari/535.19';
		$this->urlLogin		= 'https://banking.postbank.de/rai/login/wicket:interface/:0:login:loginForm::IFormSubmitListener::';
#		$this->urlLogout	= 'http://localhost/sandbox/Hydrogen/?wicket:bookmarkablePage=:de.postbank.ucp.application.rai.fs.FinanzstatusPage&wicket:interface=:1:header:4:navLogout::ILinkListener::';
	}

	protected function getPostString(){
		$data	 = array(
			'nutzername'	=> $this->bank->account,
			'kennwort'		=> $this->bank->password,
			'loginButton'	=> 'Anmelden',
			'jsDisabled'	=> 'true'
		);
		return http_build_query( $data, NULL, '&' );
	}
	
	protected function fetchAccountsUsingWget(){
		$cacheFile	= 'cache.'.$this->bank->bankId.'.html';
		$post		= $this->getPostString();
		$options	= '-O'.$cacheFile.' --no-check-certificate --post-data=\''.$post.'\' --user-agent="'.$this->userAgent.'"';
		$command	= 'wget '.$options.' '.$this->urlLogin;
		exec( $command, $a, $b );
		if( $b )
			throw new RuntimeException( 'Request failed with code '.$b );
		$html	= File_Reader::load( $cacheFile );
		unlink( $cacheFile );
#		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}

	protected function fetchAccountsUsingCurl(){
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		@unlink( $cookieFile );
		curl_setopt( $ch, CURLOPT_URL, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
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
#		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}	

	protected function parseAccount( $html, $nr ){
		$values	= $this->parseAccounts( $html );
		return $values[$nr];
	}
	
	protected function parseAccounts( $html ){
		$html	= str_replace( '></img>', '/>', $html );
		$html	= str_replace( ' p class="account-notice">', '<p class="account-notice">', $html );
		$html	= preg_replace( '/<wicket:[^>]+>.*<\/wicket:[^>]+>/iU', '', $html );

		$doc	= new DomDocument();
		$xml	= $doc->loadHTML( $html );
		$xml	= simplexml_import_dom( $doc );
		$path	= $xml->xpath( "//div[@class='accordion-bd']" );

		$accounts	= $path[0]->div->div->div->xpath( "//span[@class='account-number']" );
		$balances	= $path[0]->div->div->div->xpath( "//div[@class='account-balance']/p" );

		$values		= array();
		foreach( $accounts as $nr => $a1 ){
			$value	= str_replace( '.', '', $balances[$nr] );
			$values[trim( $a1 )]	= (float) str_replace( ',', '.', $value );
		}
		return $values;
	}

	public function getAccountValues(){
#		if( !file_exists( $this->bank->cacheFile ) ){
			$html	= $this->fetchAccountsUsingCurl();
#			$html	= $this->fetchAccountsUsingWget();
#			File_Writer::save( $this->bank->cacheFile, $html );
#		}
#		else
#			$html	= File_Reader::load( $this->bank->cacheFile );
		return $this->parseAccounts( $html );
	}
}
?>