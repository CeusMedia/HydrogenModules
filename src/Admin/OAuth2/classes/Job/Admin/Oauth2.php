<?php
class Job_Auth_Oauth2 extends Job_Abstract
{
	protected $modelProvider;
	protected $modelProviderDefault;
	protected $providersIndex				= [];
	protected $providersAvailable			= [];

	public function migrate()
	{
		$this->migrateScopes();
	}

/*
	public function showMigrationSqlForScopes( $all = TRUE ){
		$list	= [];
		foreach( $this->providersIndex as $providerKey => $providerData ){
			$scopes		= join(',', $providerData->scopes );
			if( $providerData->scopes ){
				$list[]	= vsprintf( "UPDATE %s SET scopes='%s' WHERE title='%s';", array(
					'<%?prefix%>oauth_providers',
					$scopes,
					$providerData->title,
				) );
			}
		}
		xmp( join( PHP_EOL, $list ) );die;
	}*/

	protected function __onInit(): void
	{
		$this->modelProvider		= new Model_Oauth_Provider( $this->env );
		$this->modelProviderDefault	= new Model_Oauth_ProviderDefault();
		$this->providersIndex		= [];
		$this->providersAvailable	= [];
		foreach( $this->modelProviderDefault->getAll() as $provider ){
			$provider->exists = class_exists( $provider->class );
			$this->providersIndex[$provider->class]	= $provider;
		}
		foreach( $this->providersIndex as $provider ){
			if( $provider->exists )
				$this->providersAvailable[]	= $provider;
		}
	}

	protected function migrateScopes()
	{
		$module	= $this->env->modules->get( 'Resource_Authentication_Backend_OAuth2' );
		$versionInstalled	= $module->versionInstalled;
		remark( 'versionInstalled: '.$versionInstalled );
//		if( version_compare( $versionInstalled, ..., '>' ) ){
//			if( version_compare( $versionInstalled, ..., '<=' ) ){
//			}
//		}

		$list	= [];
		foreach( $this->providersIndex as $providerData ){
			$provider	= $this->modelProvider->getByIndex( 'title', $providerData->title );
			if( !$provider )
 				continue;
			if( strlen( trim( $provider->scopes ) ) )
				$provider->scopes	= preg_split( '/\s*,\s*/', $provider->scopes );
			else
				$provider->scopes	= [];
//			remark( 'Provider: '.$providerData->title );
//			remark( 'Provider Scopes: ' );
//			print_m( $provider->scopes );
			if( $provider->scopes !== $providerData->scopes ){
				$scopes	= array_merge( $provider->scopes, $providerData->scopes );
//				remark( 'Provider Config Scopes: ' );
//				print_m( $providerData->scopes );
				$this->out( vsprintf( 'Updating scopes of provider "%s" from "%s" to "%s".', array(
					$providerData->title,
					join( ',', $provider->scopes ),
					join( ',', $scopes ),
				) ) );
				$this->modelProvider->edit( $provider->oauthProviderId, array(
					'scopes' => join( ',', $scopes )
				) );
			}
		}
	}
}
