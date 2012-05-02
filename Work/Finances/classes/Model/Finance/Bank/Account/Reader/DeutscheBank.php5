<?php
class Model_Finance_Bank_Account_Reader_DeutscheBank{

	protected $account;
	protected $userAgent;
	protected $url;
	
	public function __construct( $account ){
		$this->account		= $account;
		$this->userAgent	= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.19 (KHTML, like Gecko) Ubuntu/11.10 Chromium/18.0.1025.151 Chrome/18.0.1025.151 Safari/535.19';
		$this->urlLogin		= 'https://meine.deutsche-bank.de/trxm/db/gvo/login/login.do';
#		$this->urlLogout	= '';
	}
	
	protected function fetchAccountUsingWget(){
		$cacheFile	= 'cache.'.$this->account->bankAccountId.'.html';
		$post		= $this->getPostString();
		$options	= '-O'.$cacheFile.' --keep-session-cookies --save-cookies=cookies.txt --load-cookies=cookies.txt --no-check-certificate --post-data=\''.$post.'\' --user-agent="'.$this->userAgent.'"';
		$command	= 'wget '.$options.' '.$this->urlLogin;
		exec( $command, $a, $b );
		if( $b )
			throw new RuntimeException( 'Request failed with code '.$b );
		$html	= File_Reader::load( $cacheFile );
		unlink( $cacheFile );
#		@unlink( 'cookies.txt' );
#		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}

	protected function fetchAccountUsingCurl(){
		$ch = curl_init();
		$cookieFile	= 'cookie.jar';
		curl_setopt( $ch, CURLOPT_URL, $this->urlLogin );
		curl_setopt( $ch, CURLOPT_HEADER, TRUE );
		curl_setopt( $ch, CURLOPT_REFERER, 'https://meine.deutsche-bank.de/trxm/db' );
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
#		Net_Reader::readUrl( $this->urlLogout );
		return $html;
	}

	protected function getPostString(){
		$number		= str_pad( $this->account->account, 10, 0, STR_PAD_RIGHT );
		$data	 = array(
			'branch'			=> $this->account->code,
			'account'			=> substr( $number, 0, 7 ),
			'subaccount'		=> substr( $number, -2 ),
			'pin'				=> $this->account->password,
			'quickLink'			=> 'DisplayFinancialOverview',
			'gvo'				=> 'DisplayFinancialOverview',
			'loginTab'			=> 'iTAN',
			'javascriptEnabled'	=> 'false',
			'submit'			=> 'Login ausführen'
		);
		return http_build_query( $data, NULL, '&' );
	}

	public function getAccount(){
		if( !file_exists( $this->account->cacheFile ) ){
			$html	= $this->fetchAccountUsingCurl();
#			$html	= $this->fetchAccountUsingWget();
			File_Writer::save( $this->account->cacheFile, $html );
		}
		else
			$html	= File_Reader::load( $this->account->cacheFile );
		return $this->parseAccount( $html );
	}

	protected function parseAccount( $html ){
		print( $html );
		die;
	}
}
?>