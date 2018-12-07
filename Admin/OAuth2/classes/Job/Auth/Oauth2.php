<?php
class Job_Auth_Oauth2 extends Job_Abstract{

	protected function __onInit(){
		$this->
	}


	public function migrate(){
		$this->migrateScopes();
	}

	protected function migrateScopes(){
		$module	= $this->env->modules->get( 'Resource_Authentication_Backend_OAuth2' );
		$versionInstalled	= $module->versionInstalled;
		remark( 'versionInstalled: '.$versionInstalled );
//		if( version_compare( $versionInstalled, ..., '>' ) ){
//			if( version_compare( $versionInstalled, ..., '<=' ) ){
//			}
//		}

		$list	= array();
		foreach( $this->providersIndex as $providerData ){
			$provider	= $this->modelProvider->getByIndex( 'title', $providerData->title );
			if( !$provider )
 				continue;
			if( strlen( trim( $provider->scopes ) ) )
				$provider->scopes	= preg_split( '/\s*,\s*/', $provider->scopes );
			else
				$provider->scopes	= array();
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
/*
	public function showMigrationSqlForScopes( $all = TRUE ){
		$list	= array();
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

}
