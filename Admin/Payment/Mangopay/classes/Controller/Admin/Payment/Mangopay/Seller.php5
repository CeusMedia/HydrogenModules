<?php
class Controller_Admin_Payment_Mangopay_Seller extends Controller_Admin_Payment_Mangopay{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	public function index(){
		$sellerUserId = $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
		if( $sellerUserId ){
			$user		= $this->mangopay->getUser( $sellerUserId );
			$wallets	= $this->mangopay->getUserWallets( $sellerUserId );
			$banks		= $this->mangopay->getUserBankAccounts( $sellerUserId );
			$this->addData( 'sellerWallets', $wallets );
			$this->addData( 'sellerBanks', $banks );
		}
		else{
			$user	= new \MangoPay\UserLegal();
			$user->CompanyNumber = NULL;
			$user->HeadquartersAddress	= new \MangoPay\Address();
			$user->LegalRepresentativeAddress	= new \MangoPay\Address();

		}
		if( !isset( $user->CompanyNumber ) )
			$user->CompanyNumber	= NULL;
		$this->addData( 'sellerUser', $user );
	}

	public function bank(){
		if( $this->request->isPost() ){
			$sellerUserId	= $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
			$iban			= $this->request->get( 'iban' );
			$bic			= $this->request->get( 'bic' );
			$title			= $this->request->get( 'title' );
			if( $sellerUserId && strlen( trim( $iban ) ) && strlen( trim( $bic ) ) ){
				try{
					$address	= $this->mangopay->createAddress(
						$this->request->get( 'address' ),
						$this->request->get( 'postcode' ),
						$this->request->get( 'city' ),
						$this->request->get( 'country' ),
						$this->request->get( 'region' )
					);
					$this->mangopay->createBankAccount( $sellerUserId, $iban, $bic, $title, $address );
				}
				catch( \MangoPay\Libraries\ResponseException $e ){
					$this->handleMangopayResponseException( $e );
					$this->restart( NULL );
				}
				catch( Exception $e ){
					$this->messenger->noteError( "Exception (".get_class( $e )."): ".$e->getMessage() );
					$this->restart( NULL );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function wallet(){
		if( $this->request->isPost() ){
			$sellerUserId	= $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
			$currency		= $this->request->get( 'currency' );
			if( $sellerUserId && $currency ){
				$wallets	= $this->mangopay->getUserWallets( $sellerUserId );
				$this->mangopay->createUserWallet( $sellerUserId, $currency );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function user(){
		$sellerUserId = $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
		if( $sellerUserId ){
			$this->mangopay->updateLegalUser( $sellerUserId, $this->request->getAll() );
		}
		else{
			try{
				$result	= $this->mangopay->createLegalUser( $this->request->getAll() );
				$this->mangopay->setUserIdForLocalUserId( $result->Id, 0 );
			}
			catch( \MangoPay\Libraries\ResponseException $e ){
				$this->handleMangopayResponseException( $e );
				$this->restart( NULL );
			}
			catch( Exception $e ){
				$this->messenger->noteError( "Exception (".get_class( $e )."): ".$e->getMessage() );
				$this->restart( NULL );
			}
		}
		$this->restart( NULL, TRUE );
	}

	protected function configureLocalModule( $moduleId, $pairs ){
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		if( !is_writable( $fileName ) )
			throw new RuntimeException( 'Config file of module "'.$moduleId.'" is not writable' );
		$xml		= FS_File_Reader::load( $fileName );
		$tree		= new XML_Element( $xml );
		try{
			foreach( $tree->config as $nr => $node ){
				$type	= $node->getAttribute( 'type' );
				$value	= $node->getValue();
				if( in_array( $type, array ( "bool", "boolean" ) ) ){
					$value	= in_array( $value, array( '1', 'yes', 'true' ) ) ? "true" : "false";
				}
				$node->setValue( $value );
			}
			$original	= $tree->asXml();
			foreach( $tree->config as $nr => $node ){
				$name	= $node->getAttribute( 'name' );
				$type	= $node->getAttribute( 'type' );
				if( array_key_exists( $name, $pairs ) ){
					if( in_array( $type, array ("bool", "boolean" ) ) ){
						$pairs[$name]	= in_array( $pairs[$name], array( '1', 'yes', 'true' ) );
						$pairs[$name]	= $pairs[$name] ? "true" : "false";
					}
					$node->setValue( $pairs[$name] );
				}
			}
			if( $original === ( $xmlNew = $tree->asXml() ) )
				return 0;
			$file	= new FS_File_Backup( $fileName );
			$file->store();

			@unlink( "config/modules.cache.serial" );
			return FS_File_Writer::save( $fileName, $xmlNew );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( $e->getMessage() );
			$this->env->getMessenger()->noteNotice( UI_HTML_Exception_View::render( $e ) );
		}
	}
}
