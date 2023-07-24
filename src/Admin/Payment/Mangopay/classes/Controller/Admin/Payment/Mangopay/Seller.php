<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Backup as FileBackup;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Exception\View as HtmlExceptionView;
use CeusMedia\Common\XML\Element as XmlElement;
use MangoPay\Address;
use MangoPay\Libraries\ResponseException;
use MangoPay\UserLegal;

class Controller_Admin_Payment_Mangopay_Seller extends Controller_Admin_Payment_Mangopay
{
	protected HttpRequest $request;
	protected Logic_Payment_Mangopay $mangopay;
	protected Dictionary $moduleConfig;

	public function index()
	{
		$sellerUserId = $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
		if( $sellerUserId ){
			$user		= $this->mangopay->getUser( $sellerUserId );
			$wallets	= $this->mangopay->getUserWallets( $sellerUserId );
			$banks		= $this->mangopay->getUserBankAccounts( $sellerUserId );
			$this->addData( 'sellerWallets', $wallets );
			$this->addData( 'sellerBanks', $banks );
		}
		else{
			$user	= new UserLegal();
			$user->CompanyNumber = NULL;
			$user->HeadquartersAddress	= new Address();
			$user->LegalRepresentativeAddress	= new Address();

		}
		if( !isset( $user->CompanyNumber ) )
			$user->CompanyNumber	= NULL;
		$this->addData( 'sellerUser', $user );
	}

	public function bank()
	{
		if( $this->request->getMethod()->isPost() ){
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
				/** @noinspection PhpUndefinedClassInspection */
				catch( ResponseException $e ){
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

	public function wallet(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$sellerUserId	= $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
			$currency		= $this->request->get( 'currency' );
			if( $sellerUserId && $currency ){
				$wallets	= $this->mangopay->getUserWallets( $sellerUserId );
				$this->mangopay->createUserWallet( $sellerUserId, $currency );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function user(): void
	{
		$sellerUserId = $this->mangopay->getUserIdFromLocalUserId( 0, FALSE );
		if( $sellerUserId ){
			$this->mangopay->updateLegalUser( $sellerUserId, $this->request->getAll() );
		}
		else{
			try{
				$result	= $this->mangopay->createLegalUser( $this->request->getAll() );
				$this->mangopay->setUserIdForLocalUserId( $result->Id, 0 );
			}
			/** @noinspection PhpUndefinedClassInspection */
			catch( ResponseException $e ){
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

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}


	protected function configureLocalModule( $moduleId, $pairs )
	{
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		if( !is_writable( $fileName ) )
			throw new RuntimeException( 'Config file of module "'.$moduleId.'" is not writable' );
		$xml		= FileReader::load( $fileName );
		$tree		= new XmlElement( $xml );
		try{
			foreach( $tree->config as $nr => $node ){
				$type	= $node->getAttribute( 'type' );
				$value	= $node->getValue();
				if( in_array( $type, array ( "bool", "boolean" ) ) ){
					$value	= in_array( $value, ['1', 'yes', 'true'] ) ? "true" : "false";
				}
				$node->setValue( $value );
			}
			$original	= $tree->asXml();
			foreach( $tree->config as $nr => $node ){
				$name	= $node->getAttribute( 'name' );
				$type	= $node->getAttribute( 'type' );
				if( array_key_exists( $name, $pairs ) ){
					if( in_array( $type, array ("bool", "boolean" ) ) ){
						$pairs[$name]	= in_array( $pairs[$name], ['1', 'yes', 'true'] );
						$pairs[$name]	= $pairs[$name] ? "true" : "false";
					}
					$node->setValue( $pairs[$name] );
				}
			}
			if( $original === ( $xmlNew = $tree->asXml() ) )
				return 0;
			$file	= new FileBackup( $fileName );
			$file->store();

			@unlink( "config/modules.cache.serial" );
			return FileWriter::save( $fileName, $xmlNew );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( $e->getMessage() );
			$this->env->getMessenger()->noteNotice( HtmlExceptionView::render( $e ) );
		}
	}
}
