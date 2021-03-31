<?php
class View_Helper_Module_Files{

	protected $env;
	protected $states	= array(
		-3	=> 'missing',
		-2	=> 'inaccessible',
		-1	=> 'protected',
		0	=> 'new',
		1	=> 'installed',
		2	=> 'changed',
		3	=> 'linked',
		4	=> 'foreign',
		5	=> 'refered',
	);

	public function __construct( $env ){
		$this->setEnv( $env );
	}

	public function render( $files, $words, $options = array() ){
		$options	= array_merge( array(
			'useCheckboxes'	=> TRUE,
			'useActions'	=> TRUE,
		), $options );

		if( !count( $files ) )
			return;
		$list	= array();
		foreach( $files as $file ){
			$actions	= array();
			$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'files[]',
				'value'		=> base64_encode( json_encode( $file ) ),
				'class'		=> 'file-check',
				'checked'	=> in_array( $file->status, array( 0, 1, 2 ) ) ? 'checked' : NULL,
			) );
			if( in_array( $file->status, array( 3, 5 ) ) )
				$checkbox	= '';
			else if( $file->status === 2 ){
				$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
				$actions[]	= UI_HTML_Tag::create( 'a', 'diff', array( 'href' => $url, 'class' => 'layer-html' ) );
			}
			else if( !file_exists( $file->pathSource ) )
				$file->status	= -3;
			else if( !is_readable( $file->pathSource ) )
				$file->status	= -2;
			else if( !is_writable( $file->pathSource ) )
				$file->status	= -1;

			$statusLabel	= $words['update-file-states'][$file->status];
			$statusDesc		= $words['update-file-state-description'][$file->status];
			$status	= UI_HTML_Tag::create( 'acronym', $statusLabel, array( 'title' => $statusDesc ) );
			$cells	= array();
			if( $options['useCheckboxes'] )
				$cells[]	= UI_HTML_Tag::create( 'td', $checkbox, array( 'class' => 'cell-check' ) );
			$cells[]    = UI_HTML_Tag::create( 'td', $words['file-types'][$file->typeKey], array( 'class' => 'cell-type' ) );
			$cells[]    = UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-state' ) );
			$cells[]    = UI_HTML_Tag::create( 'td', $file->name, array( 'class' => 'cell-name' ) );
			if( $options['useActions'] )
				$cells[]    = UI_HTML_Tag::create( 'td', join( " ", $actions ), array( 'class' => 'cell-actions' ) );

			$list[]	= UI_HTML_Tag::create( 'tr', $cells, array(
				'class'	=> 'status-'.$this->states[$file->status],
				'data-file-source'	=> $file->pathSource,
				'data-file-local'	=> $file->pathLocal
			) );
		}
		$checkAll	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'			=> 'checkbox',
			'onchange'		=> 'AdminModuleUpdater.switchAllFiles()',
			'id'			=> 'btn_switch_files',
			'checked'		=> 'checked',
			'data-state'	=> 1,
			'title'			=> 'check/uncheck all',
		) );

		$colgroup	= array();
		$heads		= array();
		if( $options['useCheckboxes'] ){
			$colgroup[]	= "3%";
			$heads[]	= $checkAll;
		}
		$colgroup[]	= "12%";
		$heads[]	= 'Typ';

		$colgroup[]	= "10%";
		$heads[]	= 'Status';

		$colgroup[]	= "60%";
		$heads[]	= 'Datei';

		if( $options['useActions'] ){
			$colgroup[]	= "15%";
			$heads[]	= 'Aktion';
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( $colgroup );
		$heads		= UI_HTML_Elements::TableHeads( $heads ) ;
		$thead		= UI_HTML_Tag::create( 'thead', $heads );
		$tbody		= UI_HTML_Tag::create( 'tbody', $list, array( 'id' => 'file-rows' ) );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table module-update-files' ) );
		return $table;
	}

	public function setEnv( $env ){
		$this->env	= $env;
	}
}
?>
