<?php

use CeusMedia\Common\FS\File\RecursiveTodoLister as RecursiveTodoFileLister;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Dev_Todo extends Controller{
	public function index(){
		$exts		= ['php5', 'php', 'js', 'css'];
		$sources	= [
			[
				'path'	=> './',
				'title'	=> 'self'
			],
			[
				'path'	=> '../../projects/Chat/client',
				'title'	=> 'Chat::Client'
			],
			[
				'path'	=> '../../projects/Chat/admin',
				'title'	=> 'Chat::Admin'
			],
			[
				'path'	=> '../../projects/Chat/server',
				'title'	=> 'Chat::Server'
			],
		];
		foreach( $sources as $nr => $source ){
			$sources[$nr]['exts']	= empty( $source['exts'] ) ? $exts : $source['exts'];
			$sources[$nr]['data']	= $this->check( $source['path'], $sources[$nr]['exts'] );
		}
		$this->addData( 'data', $sources );
	}

	protected function check( $path, $extensions = [] ){
		$list	= [];
		$keys	= ["//TODO", "@deprecated", "@todo"];
		$lister	= new RecursiveTodoFileLister( $extensions, $keys );
		$lister->scan( $path );
		$data	= array(
			'total'	=> $lister->getNumberScanned(),
			'lines'	=> $lister->getNumberLines(),
			'found'	=> $lister->getNumberFound(),
			'todos'	=> $lister->getNumberTodos(),
			'files'	=> $lister->getList( TRUE ),
		);
		$data['ratio']	= $data['total'] ? 100 - round( $data['found'] / $data['total'] * 100, 2 ) : 0;
		return $data;
	}
}
?>
