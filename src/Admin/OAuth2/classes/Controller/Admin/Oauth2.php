<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Oauth2 extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Model_Oauth_Provider $modelProvider;
	protected Model_Oauth_ProviderDefault $modelProviderDefault;
	protected array $providersIndex				= [];
	protected array $providersAvailable			= [];
	protected string $filterPrefix				= 'filter_admin_oauth2_';

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['status']		= Model_Oauth_Provider::STATUS_NEW;
			$data['createdAt']	= time();
			$data['modifiedAt']	= time();
			$providerKey = str_replace( '__', '/', $this->request->get( 'providerKey' ) );
			if( $providerKey ){
				foreach( $this->modelProviderDefault->getAll() as $item ){
					if( $item->package === $providerKey ){
						$data['icon']				= $item->icon;
						$data['className']			= $item->class;
						$data['composerPackage']	= $item->package;
						if( isset( $item->options ) && count( (array) $item->options ) )
							$data['options']		= json_encode( $item->options );
						if( isset( $item->scopes ) )
							$data['scopes']		= join( ',', $item->scopes );
						break;
					}
				}
			}
			$providerId	= $this->modelProvider->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( 'edit/'.$providerId, TRUE );
		}

		$provider	= [];
		foreach( $this->modelProvider->getColumns() as $column )
			if( !in_array( $column, ['oauthProviderId', 'createdAt', 'modifiedAt'] ) )
				$provider[$column]	= $this->request->get( $column );
		if( ( $providerKey = $this->request->get( 'providerKey' ) ) ){
			foreach( $this->providersIndex as $item ){
				if( $item->package === $providerKey ){
					$provider['title']				= $item->title;
					$provider['icon']				= $item->icon;
					$provider['className']			= $item->class;
					$provider['composerPackage']	= $item->package;
					if( isset( $item->options ) )
						$provider['options']		= json_encode( $item->options );
					break;
				}
			}
		}
		$this->addData( 'provider', (object) $provider );
		$this->addData( 'providersIndex', array_values( $this->providersIndex ) );
		$this->addData( 'providersAvailable', $this->providersAvailable );
		$this->addData( 'providers', $this->modelProvider->getAll() );
	}

	/**
	 *	@param		int|string		$providerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $providerId ): void
	{
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
//		$this->addData( 'exists', class_exists( $provider->className ) );
		$this->addData( 'exists', $this->providersIndex[$provider->className]->exists );
		$this->addData( 'providerId', $providerId );
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$this->modelProvider->edit( $providerId, $this->request->getAll(), FALSE );
			$this->restart( 'edit/'.$providerId, TRUE );
		}
		$this->addData( 'providerId', $providerId );
		$this->addData( 'provider', $provider );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$filters	= $this->session->getAll( $this->filterPrefix );
			foreach( array_keys( $filters ) as $filterKey )
				$this->session->remove( $this->filterPrefix.$filterKey );
		}
/*		$filters	= [];
		foreach( $filters as $filter ){
			if( $this->request->has( $filter ) ){
				$this->session->set( $this->filterPrefix.$filter, $this->request->get( $filter ) );
			}
		}*/
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$conditions	= [];
		$orders		= ['rank' => 'ASC'];
		$providers	= $this->modelProvider->getAll( $conditions, $orders );
		$this->addData( 'providersIndex', array_values( $this->providersIndex ) );
		$this->addData( 'providersAvailable', $this->providersAvailable );
		$this->addData( 'providers', $providers );
	}

	/**
	 *	@param		int|string		$providerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $providerId ): void
	{
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( 'edit/'.$providerId, TRUE );
		}
		if( $provider->status == Model_Oauth_Provider::STATUS_ACTIVE ){
			$this->messenger->noteError( 'Provider is active right now. Deactivate first!' );
			$this->restart( 'edit/'.$providerId, TRUE );
		}
		$this->modelProvider->remove( $providerId );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$providerId
	 *	@param		$status
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( int|string $providerId, $status ): void
	{
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->modelProvider->edit( $providerId, ['status' => $status] );
		$this->restart( 'edit/'.$providerId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin_oauth2.', TRUE );
		$this->modelProvider		= new Model_Oauth_Provider( $this->env );
		$this->modelProviderDefault	= new Model_Oauth_ProviderDefault();
		$this->providersIndex		= [];
		$this->providersAvailable	= [];
		foreach( $this->modelProviderDefault->getAll() as $provider ){
			$provider->exists	= class_exists( $provider->class );
			$this->providersIndex[$provider->class]	= $provider;
		}
		foreach( $this->providersIndex as $provider ){
			if( $provider->exists )
				$this->providersAvailable[]	= $provider;
		}
	}
}
