<?php
use CeusMedia\Common\FS\File\CSV\Writer as CsvFileWriter;
use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\UI\Image\Captcha as ImageCaptcha;

class Job_Shop extends Job_Abstract
{
	protected $versionShop;

	protected $configFileOldCustomers;

	protected $data;

	public function cleanupOldCustomers()
	{
		$this->cleanupOldCustomerTestOrders();
//		$this->cleanupOldCustomerInvalidOrders();
		$this->sanitizeOldCustomerCountries();
	}

	public function cleanupOldCustomerTestOrders()
	{
		$this->loadConfig();
		if( !isset( $this->data->testOrders ) ){
			$this->out( 'No test order configuration found in '.$this->configFileOldCustomers );
			return;
		}
		$dataDefault	= $this->data->testOrders->template;
		$dataTesters	= $this->data->testOrders->accounts;
		$dataEmails		= $this->data->testOrders->emails;

		$transEmail		= [];
		foreach( $dataEmails as $email => $accountKey )
			$transEmail[$email]	= array_merge( (array) $dataDefault, (array) $dataTesters->{$accountKey} );

		$modelCustomer	= new Model_Shop_Customer( $this->env );
		if( version_compare( $this->versionShop, '0.8', '>=' ) )
			$modelCustomer	= new Model_Shop_CustomerOld( $this->env );
		$customers		= $modelCustomer->getAll( ['email' => array_keys( $transEmail )] );
		foreach( $customers as $nr => $customer ){
			$data		= $transEmail[$customer->email];
			if( $customer->institution === $data['institution'] && $customer->firstname === $data['firstname'] )
				unset( $customers[$nr] );
		}

		if( ( $total = count( $customers ) ) ){
			$this->out( 'Synchronizing '.count( $customers ).' test orders' );
			$count		= 0;
			$handled	= 0;
			foreach( $customers as $customer ){
				$data		= $transEmail[$customer->email];
				$this->showProgress( ++$count, $total );
				$handled++;
				$modelCustomer->edit( $customer->customerId, $data );
			}
			$this->out();
		}
	}

	public function createOldCustomersConfig( array $arguments = [], array $parameters = [] )
	{
		$force	= in_array( 'force', $arguments );
		if( file_exists( $this->configFileOldCustomers ) && !$force ){
			$this->out( 'Shop job configuration is already existing in '.$this->configFileOldCustomers );
			return;
		}
		$data	= array(
			'testOrders'	=> [
				'template'	=> [
					'country'				=> 'Deutschland',
					'city'					=> 'Leipzig',
					'region'				=> 'Sachsen',
					'alternative'			=> 0,
					'billing_institution'	=> '',
					'billing_firstname'		=> '',
					'billing_lastname'		=> '',
					'billing_tnr'			=> '',
					'billing_country'		=> '',
					'billing_city'			=> '',
					'billing_postcode'		=> '',
					'billing_address'		=> '',
					'billing_phone'			=> '',
					'billing_email'			=> '',
				],
				'accounts'	=> [
					'test1'	=> [
						'institution'	=> 'Mein Unternehmen',
						'email'			=> 'test1@meinunternehmen.de',
						'firstname'		=> 'Hans',
						'lastname'		=> 'Testmann',
						'postcode'		=> '04105',
						'address'		=> 'Am Markt 1',
						'phone'			=> '0049 341 12 34 567',
					],
				],
				'emails'	=> [
					'test2@meinunternehmen.de'	=> 'test1',
				],
			],
			'countries'	=> [
				'sanitizeMap'	=> [
					'Deutschland'		=> [
						'Germany',
						'BRD',
						'Deutschland (Germany)',
						'Bundesrepublik Deutschland',
						'D',
						'Deutschland (DEU)',
						'BR Deutschland',
						'Deutschlan',
						'DEutschland',
					],
				]
			],
			'migrants'	=> [
				'skipEmails'	=> [
					'test1@meinunternehmen.de',
					'test2@meinunternehmen.de'
				],
				'captcha'	=> [
					'length'		=> 12,
					'useDigits'		=> FALSE,
					'useSymbols'	=> FALSE
				]
			]
		);
		file_put_contents( $this->configFileOldCustomers, json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	public function importOldCustomersAsMigrantsAndSaveAsCsv( array $arguments = [], array $parameters = [] )
	{
		$this->loadConfig();
		if( !isset( $this->data->migrants ) ){
			$this->out( 'No migrants configuration found in '.$this->configFileOldCustomers );
			return;
		}
		$force	= in_array( 'force', $arguments );
		$modelCustomer	= new Model_Shop_Customer( $this->env );
		$modelOrder		= new Model_Shop_Order( $this->env );
		$modelMigrant	= new Model_Shop_Migrant( $this->env );
		if( version_compare( $this->versionShop, '0.8', '>=' ) )
			$modelCustomer	= new Model_Shop_CustomerOld( $this->env );
		if( $force )
			$modelMigrant->truncate();
		if( $modelMigrant->count() ){
			$this->out( 'Import of old customers to migrants already done' );
			return;
		}

		$blocked		= $this->data->migrants->skipEmails;
		$countryMap		= array_flip( $this->env->getLanguage()->getWords( 'countries' ) );
		$emails			= [];
		$conditions		= ['customerId' => '> 0', 'status' => '>= '.Model_Shop_Order::STATUS_ORDERED];
		$orders			= ['orderId' => 'ASC'];
		$shopOrders		= $modelOrder->getAll( $conditions, $orders );
		foreach( $shopOrders as $order ){
			$this->out( '- Order: '.$order->orderId );
			if( !$order->customerId )
				continue;
			$customer	= $modelCustomer->get( $order->customerId );
			if( !$customer )
				continue;
			if( in_array( $customer->email, $blocked ) )
				continue;
			if( array_key_exists( $customer->email, $emails ) )
 				continue;
			if( !array_key_exists( $customer->country, $countryMap ) )
 				continue;
			$emails[$customer->email]	= (object) [
				'customer'	=> $customer,
				'order'		=> $order,
			];
		}
		$captcha	= new ImageCaptcha();
		$captcha->length		= $this->data->migrants->captcha->length;
		$captcha->useDigits		= $this->data->migrants->captcha->useDigits;
		$captcha->useSymbols	= $this->data->migrants->captcha->useSymbols;
		$regExp		= '/^(.+)\s+([0-9]+.*)$/';
		$count		= 0;
		$total		= count( $emails );
		$migrants	= [];
		foreach( $emails as $email => $data ){
			$data->customer->number	= '';
			if( preg_match( $regExp, $data->customer->address ) ){
				$data->customer->number		= preg_replace( $regExp, '\\2',$data->customer->address );
				$data->customer->address	= preg_replace( $regExp, '\\1', $data->customer->address );
			}
			$data	= array(
				'customerId'	=> $data->customer->customerId,
				'orderId'		=> $data->order->orderId,
				'firstname'		=> str_replace( '"', '', trim( $data->customer->firstname ) ),
				'surname'		=> str_replace( '"', '', trim( $data->customer->lastname ) ),
				'email'			=> str_replace( '"', '', trim( $data->customer->email ) ),
				'country'		=> $countryMap[$data->customer->country],
				'postcode'		=> str_replace( '"', '', trim( $data->customer->postcode ) ),
				'city'			=> str_replace( '"', '', trim( $data->customer->city ) ),
				'street'		=> str_replace( '"', '', trim( $data->customer->address ) ),
				'number'		=> str_replace( '"', '', trim( $data->customer->number ) ),
				'phone'			=> str_replace( '"', '', trim( $data->customer->phone ) ),
				'orderDate'		=> date( 'Y-m-d H:i:s', (int) $data->order->createdAt ),
				'hash'			=> $captcha->generateWord(),
			);
			$migrantId	= $modelMigrant->add( $data );
			$migrants[]	= array_merge(
				array('shopMigrantId' => $migrantId ),
				$data
			);
			$this->showProgress( ++$count, $total );
		}
		$this->out();
		$this->out( 'Imported '.count( $migrants ).' customers as migrants' );
		if( $migrants ){
			$headers	= array_keys( $migrants[0] );
			$csv	= new CsvFileWriter( 'migrants.csv' );
			$csv->write( $migrants, $headers );
			$this->out( 'Saved migrants for marketing uses in migrants.csv' );
		}
	}

	public function migrateOldCustomers( array $arguments = [], array $parameters = [] )
	{
		$modelCustomerNew	= new Model_Shop_Customer( $this->env );
		$modelCustomerOld	= new Model_Shop_CustomerOld( $this->env );
		$modelAddress		= new Model_Address( $this->env );
		$modelOrders		= new Model_Shop_Order( $this->env );
		$pathLocales		= $this->env->getConfig()->get( 'path.locales' );
//		$modelOrders->getAll()
		$conditions	= [];
		$orders		= ['customerId' => 'ASC'];
		$limit		= [0, 1000];
		$countries	= IniFileReader::load( $pathLocales.'de/countries.ini' );
		$customers	= $modelCustomerOld->getAll( $conditions, $orders/*, $limit*/ );
		if( !$customers ){
			$this->out( 'Migration already done' );
			return;
		}
		$count		= 0;
		foreach( $customers as $customer ){
			$order	= $modelOrders->getByIndex( 'customerId', $customer->customerId );
			if( $order ){
				$country	= $countries['DE'];
				if( array_key_exists( $customer->country, $countries ) )
					$country	= $countries[$customer->country];
				$modelAddress->add( [
					'relationId'	=> $customer->customerId,
					'relationType'	=> 'customer',
					'type'			=> Model_Address::TYPE_DELIVERY,
					'country'		=> $country,
					'region'		=> $customer->region,
					'city'			=> $customer->city,
					'postcode'		=> $customer->postcode,
					'street'		=> $customer->address,
					'phone'			=> $customer->phone,
					'email'			=> $customer->email,
					'firstname'		=> $customer->firstname,
					'surname'		=> $customer->lastname,
					'institution'	=> $customer->institution,
					'createdAt'		=> $order->createdAt,
					'modifiedAt'	=> $order->createdAt,
				] );
				if( (int) $customer->alternative > 0 ){
					$country	= $countries['DE'];
					if( array_key_exists( $customer->billing_country, $countries ) )
						$country	= $countries[$customer->billing_country];
					$modelAddress->add( [
						'relationId'	=> $customer->customerId,
						'relationType'	=> 'customer',
						'type'			=> Model_Address::TYPE_BILLING,
						'country'		=> $country,
						'city'			=> $customer->billing_city,
						'postcode'		=> $customer->billing_postcode,
						'street'		=> $customer->billing_address,
						'phone'			=> $customer->billing_phone,
						'email'			=> $customer->billing_email,
						'firstname'		=> $customer->billing_firstname,
						'createdAt'		=> $order->createdAt,
						'modifiedAt'	=> $order->createdAt,
					] );
				}
				$modelCustomerNew->add( ['customerId' => $customer->customerId] );
				$modelCustomerOld->remove( $customer->customerId );
			}
			$this->showProgress( ++$count, count( $customers ) );
		}
		$this->out();
	}

	public function sanitizeOldCustomerCountries()
	{
		$this->loadConfig();
		if( !isset( $this->data->countries->sanitizeMap ) ){
			$this->out( 'No country sanitation configuration found in '.$this->configFileOldCustomers );
			return;
		}
		$mapCountries	= IniFileReader::load( 'contents/locales/de/countries.ini' );
		$transCountries	= $this->data->countries->sanitizeMap;
		$modelCustomer	= new Model_Shop_Customer( $this->env );
		if( version_compare( $this->versionShop, '0.8', '>=' ) )
			$modelCustomer	= new Model_Shop_CustomerOld( $this->env );

		$countries		= [];
		$list			= [];
		$customers		= $modelCustomer->getAll();
		foreach( $customers as $nr => $customer ){
			$customer->country	= trim( $customer->country );
			if( array_search( $customer->country, $mapCountries ) )
				unset( $customers[$nr] );
		}
		if( ( $total = count( $customers ) ) ){
			$this->out( 'Evaulating '.count( $customers ).' order customer countries:' );
			$count			= 0;
			foreach( $customers as $customer ){
				$found	= FALSE;
				foreach( $transCountries as $target => $sources ){
					if( in_array( $customer->country, $sources ) ){
						$this->showProgress( ++$count, $total );
						$modelCustomer->edit( $customer->customerId, ['country' => $target] );
						$found	= TRUE;
						break;
					}
				}
				if( $found )
					continue;
				$countries[]		= $customer->country;
			}
			if( $count ){
				$this->out();
				$this->out( $count.' countries sanitized' );
			}
			$countries		= array_unique( $countries );
			$this->out( count( $countries ).' countries NOT found:' );
			$this->out( '- '.join( PHP_EOL.'- ', $countries ) );
		}
	}

	/*  --  PROTECTED  --  */

	protected function __onInit(): void
	{
		$this->versionShop	= $this->env->getModules()->get( 'Shop' )->versionInstalled;
		$this->configFileOldCustomers	= 'config/job.shop.oldCustomers.json';
	}

	protected function loadConfig()
	{
		if( $this->data )
			return;
		if( !file_exists( $this->configFileOldCustomers ) )
		 	$this->createOldCustomersConfig();
		$this->data	= json_decode( file_get_contents( $this->configFileOldCustomers ) );
	}

	/**
	 *	seems to be dysfunctional or rather incomplete (takes no actions)
	 */
	protected function cleanupOldCustomerInvalidOrders(){
		$this->loadConfig();
		$dataDefault		= [
			'country'				=> 'Deutschland',
			'firstname'				=> 'Test',
			'lastname'				=> 'Invalid',
			'alternative'			=> 0,
			'billing_institution'	=> '',
			'billing_firstname'		=> '',
			'billing_lastname'		=> '',
			'billing_tnr'			=> '',
			'billing_country'		=> '',
			'billing_city'			=> '',
			'billing_postcode'		=> '',
			'billing_address'		=> '',
			'billing_phone'			=> '',
			'billing_email'			=> '',
		];

		$modelCustomer	= new Model_Shop_Customer( $this->env );
		if( version_compare( $this->versionShop, '0.8', '>=' ) )
			$modelCustomer	= new Model_Shop_CustomerOld( $this->env );

		$customers		= $modelCustomer->getAll();
		foreach( $customers as $nr => $customer ){
			if( preg_match( '/^.+@.+\..+$/', $customer->email ) )
				unset( $customers[$nr] );
		}

		if( ( $total = count( $customers ) ) ){
			$this->out( 'Invalidating '.count( $customers ).' foreign test orders' );
			$count		= 0;
			foreach( $customers as $customer ){
				$this->showProgress( ++$count, $total );
//				$modelCustomer->edit( $customer->customerId, $dataDefault);
			}
			$this->out();
		}
	}
}
