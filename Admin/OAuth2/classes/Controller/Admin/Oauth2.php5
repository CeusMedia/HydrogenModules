<?php
class Controller_Admin_Oauth2 extends CMF_Hydrogen_Controller{

	protected $request;
	protected $session;
	protected $messenger;
	protected $moduleConfig;
	protected $modelProvider;
	protected $providerIndex				= array();
	protected $providersAvailable			= array();

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin_oauth2.', TRUE );
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
		$this->providersIndex		= FS_File_JSON_Reader::load( 'config/oauth2_providers.json' );
		$this->providersAvailable	= array();
		foreach( $this->providersIndex as $provider ){
			if( class_exists( $provider->class ) )
				$this->providersAvailable[]	= $provider;
		}
	}

	public function add(){
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['status']	= Model_Oauth_Provider::STATUS_NEW;
			$data['createdAt']	= time();
			$data['modifiedAt']	= time();
			if( ( $providerKey = $this->request->get( 'providerKey' ) ) ){
				foreach( $this->providersIndex as $item ){
					if( $item->package === $providerKey ){
						$data['icon']				= $item->icon;
						$data['className']			= $item->class;
						$data['composerPackage']	= $item->package;
						break;
					}
				}
			}
			$providerId	= $this->modelProvider->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( 'edit/'.$providerId, TRUE );
		}

		$provider	= array();
		foreach( $this->modelProvider->getColumns() as $column )
			if( !in_array( $column, array( 'oauthProviderId', 'createdAt', 'modifiedAt' ) ) )
				$provider[$column]	= $this->request->get( $column );
		if( ( $providerKey = $this->request->get( 'providerKey' ) ) ){
			foreach( $this->providersIndex as $item ){
				if( $item->package === $providerKey ){
					$provider['title']				= $item->title;
					$provider['icon']				= $item->icon;
					$provider['className']			= $item->class;
					$provider['composerPackage']	= $item->package;
					break;
				}
			}
		}
		$this->addData( 'provider', (object) $provider );
		$this->addData( 'providersIndex', $this->providersIndex );
		$this->addData( 'providersAvailable', $this->providersAvailable );
		$this->addData( 'providers', $this->modelProvider->getAll() );
	}

	public function edit( $providerId ){
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'exists', class_exists( $provider->className ) );
		$this->addData( 'providerId', $providerId );
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$this->modelProvider->edit( $providerId, $this->request->getAll(), FALSE );
		}
		$this->addData( 'providerId', $providerId );
		$this->addData( 'provider', $provider );
	}

	public function filter( $reset = NULL ){
		if( $reset ){

		}
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$conditions	= array();
		$orders		= array( 'rank' => 'ASC' );
		$providers	= $this->modelProvider->getAll( $conditions, $orders );
		$this->addData( 'providers', $providers );
	}

	public function setStatus( $providerId, $status ){
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->modelProvider->edit( $providerId, array( 'status' => $status ) );
		$this->restart( 'edit/'.$providerId, TRUE );
	}

	/*  --  PROTECTED  --  */
	protected function scanInstalledProviders(){
		$list	= array();
		$regexPackage	= '/^oauth2-(\S+)$/';
		foreach( new DirectoryIterator( 'vendor' ) as $vendor ){
			if( $vendor->isDot() || !$vendor->isDir() )
				continue;
			$pathPackages	= 'vendor/'.$vendor->getFilename();
			foreach( new DirectoryIterator( $pathPackages ) as $package ){
				if( $package->isDot() || !$package->isDir() )
					continue;
				$packageKey	= $package->getFilename();
				if( preg_match( $regexPackage, $packageKey ) ){
					$provider	= preg_replace( $regexPackage, '\\1', $packageKey );
					if( $provider === "client" )
						continue;
					$list[]	= array(
						'vendor'		=> $vendor->getFilename(),
						'package'		=> $packageKey,
						'provider'		=> $provider,
					);
				}
			}
		}
		$this->providersAvailable	= $list;
	}
}
