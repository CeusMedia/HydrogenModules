<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Time\Clock;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_FTP extends Controller
{
	protected Dictionary $config;
	protected Dictionary $session;

	/**	@var	Logic_FTP	$logic */
	protected Logic_FTP $logic;

	public function login(): void
	{
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data	= [
				'host'		=> $request->get( 'ftp_host' ),
				'port'		=> $request->get( 'ftp_port' ),
				'path'		=> $request->get( 'ftp_path' ),
				'username'	=> $request->get( 'ftp_username' ),
				'password'	=> $request->get( 'ftp_password' ),
			];
			$this->session->set( 'module_work_ftp_access', $data );
			$this->connect();
			$this->restart( NULL, TRUE );
		}
		$fromModule		= $this->config->getAll( 'module.work_ftp.access.' );
		$fromSession	= $this->session->get( 'module_work_ftp_access' );
		foreach( array_keys( $fromModule ) as $key )
			if( !isset( $fromSession[$key] ) )
				$fromSession[$key]	= NULL;
		$this->addData( 'fromSession', $fromSession );
		$this->addData( 'fromModule', $fromModule );
	}

	public function ajaxIndex()
	{
		$path		= $this->env->getRequest()->getFromSource( 'path', 'GET' );
		$folders	= [];
		$files		= [];
		foreach( $this->logic->index( $path ) as $entry ){
			$entry	= (object) $entry;
			if( $entry->isdir ){
				$folders[$entry->name]	= $entry;
			}
			else{
				$files[$entry->name]	= $entry;
			}
		}
		ksort( $folders );
		ksort( $files );
		$list	= $folders + $files;
		print( json_encode( $list ) );
		exit;
	}

	public function index(): void
	{
		$this->connect();
		$clock	= new Clock;
		$path		= $this->env->getRequest()->get( 'path' );
		if( $this->env->getRequest()->has( 'refresh' ) )
			$this->logic->uncache( $path );
		$deepPath	= $this->session->get( 'deepestPath' );
		if( $path && ( !$deepPath || !str_starts_with( $deepPath, $path ) ) )
			$this->session->set( 'deepestPath', $deepPath = $path );

		$entries	= $this->logic->index( $path );
		foreach( $entries as $nr => $entry ){
			if( $entry['isdir'] ){
				$pathInner	= $path ? $path.'/'.$entry['name'] : $entry['name'];
				$entries[$nr]['folders']	= $this->logic->countFolders( $pathInner );
				$entries[$nr]['files']		= $this->logic->countFiles( $pathInner );
			}
		}
		$this->addData( 'pathCurrent', (string) $path );
		$this->addData( 'pathDeepest', (string) $deepPath );
		$this->addData( 'entries', $entries );
		$this->addData( 'time', $clock->stop( 3, 3 ) );
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->logic		= new Logic_FTP( $this->env );
	}

	protected function connect()
	{
		if( $this->logic->isConnected() )
			return TRUE;

		$config	= $this->config->getAll( FALSE, TRUE );
		if( $this->session->get( 'module_work_ftp_access' ) ){
			$access	= $this->session->get( 'module_work_ftp_access' );
			foreach( $access as $key => $value )
				$config->set( 'module.work_ftp.access.'.$key, $value );
		}
		else if( class_exists( 'Model_User_Setting' ) ){
			if( ( $userId = $this->session->get( 'auth_user_id' ) ) )
				$config	= Model_User_Setting::applyConfigStatic( $this->env, $userId, FALSE );
		}
		$access		= $config->getAll( "module.work_ftp.access.", TRUE );
		if( $access->get( 'host' )&& $access->get( 'username' ) && $access->get( 'password' ) ){
			try{
				$this->logic->connect(
					$access->get( 'host' ),
					$access->get( 'port' ),
					$access->get( 'username' ),
					$access->get( 'password' ),
					$access->get( 'path' )
				);
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteError( 'Die Verbindung ist fehlgeschlagen. Bitte die Zugangsdaten überprüfen.' );
				$this->restart( 'login', TRUE );
			}
		}
		else{
			$this->env->getMessenger()->noteNotice( 'Die Zugangsdaten fehlen, sind unvollständig oder falsch. Bitte korrigieren.' );
			$this->restart( 'login', TRUE );
		}
	}
}
