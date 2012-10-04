<?php
class Controller_Dev_Todo extends CMF_Hydrogen_Controller{
	public function index(){
		$exts		= array( 'php5', 'php', 'js', 'css' );
		$sources	= array(
			array(
				'path'	=> './',
				'title'	=> 'self'
			),
			array(
				'path'	=> '../../projects/Chat/client',
				'title'	=> 'Chat::Client'
			),
			array(
				'path'	=> '../../projects/Chat/admin',
				'title'	=> 'Chat::Admin'
			),
			array(
				'path'	=> '../../projects/Chat/server',
				'title'	=> 'Chat::Server'
			),
		);
		foreach( $sources as $nr => $source ){
			$sources[$nr]['exts']	= empty( $source['exts'] ) ? $exts : $source['exts'];
			$sources[$nr]['data']	= $this->check( $source['path'], $sources[$nr]['exts'] );
		}
		$this->addData( 'data', $sources );
	}

	protected function check( $path, $extensions = array() ){
		$list	= array();
		$keys	= array( "//TODO", "@deprecated", "@todo" );
		$lister	= new File_RecursiveTodoLister( $extensions, $keys );
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
