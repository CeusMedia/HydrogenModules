<?php
class Controller_Work_FTP extends CMF_Hydrogen_Controller {

	/**	@var	Logic_FTP	$logic */
	protected $logic;

	protected function __onInit(){
		$access			= $this->env->getConfig()->getAll( "module.work_ftp.access.", TRUE );
		$this->logic	= new Logic_FTP(
			$access->get( 'host' ),
			$access->get( 'port' ),
			$access->get( 'username' ),
			$access->get( 'password' ),
			$access->get( 'path' )
		);
		$this->session	= $this->env->getSession();
	}

	public function ajaxIndex(){
		$path		= $this->env->getRequest()->get( 'path' );
		$folders	= array();
		$files		= array();
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

	public function index(){
		$clock	= new Alg_Time_Clock;
		$path		= $this->env->getRequest()->get( 'path' );
		if( $this->env->getRequest()->has( 'refresh' ) )
			$this->logic->uncache( $path );
		$deepPath	= $this->session->get( 'deepestPath' );
		if( $path && ( !$deepPath || substr( $deepPath, 0, strlen( $path ) ) !== $path ) )
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
}
?>
