<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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

	public function render( $files, $words, $options = [] ){
		$options	= array_merge( array(
			'useCheckboxes'	=> TRUE,
			'useActions'	=> TRUE,
		), $options );

		if( !count( $files ) )
			return;
		$list	= [];
		foreach( $files as $file ){
			$actions	= [];
			$checkbox	= HtmlTag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'files[]',
				'value'		=> base64_encode( json_encode( $file ) ),
				'class'		=> 'file-check',
				'checked'	=> in_array( $file->status, [0, 1, 2] ) ? 'checked' : NULL,
			) );
			if( in_array( $file->status, [3, 5] ) )
				$checkbox	= '';
			else if( $file->status === 2 ){
				$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
				$actions[]	= HtmlTag::create( 'a', 'diff', ['href' => $url, 'class' => 'layer-html'] );
			}
			else if( !file_exists( $file->pathSource ) )
				$file->status	= -3;
			else if( !is_readable( $file->pathSource ) )
				$file->status	= -2;
			else if( !is_writable( $file->pathSource ) )
				$file->status	= -1;

			$statusLabel	= $words['update-file-states'][$file->status];
			$statusDesc		= $words['update-file-state-description'][$file->status];
			$status	= HtmlTag::create( 'acronym', $statusLabel, ['title' => $statusDesc] );
			$cells	= [];
			if( $options['useCheckboxes'] )
				$cells[]	= HtmlTag::create( 'td', $checkbox, ['class' => 'cell-check'] );
			$cells[]    = HtmlTag::create( 'td', $words['file-types'][$file->typeKey], ['class' => 'cell-type'] );
			$cells[]    = HtmlTag::create( 'td', $status, ['class' => 'cell-state'] );
			$cells[]    = HtmlTag::create( 'td', $file->name, ['class' => 'cell-name'] );
			if( $options['useActions'] )
				$cells[]    = HtmlTag::create( 'td', join( " ", $actions ), ['class' => 'cell-actions'] );

			$list[]	= HtmlTag::create( 'tr', $cells, array(
				'class'	=> 'status-'.$this->states[$file->status],
				'data-file-source'	=> $file->pathSource,
				'data-file-local'	=> $file->pathLocal
			) );
		}
		$checkAll	= HtmlTag::create( 'input', NULL, array(
			'type'			=> 'checkbox',
			'onchange'		=> 'AdminModuleUpdater.switchAllFiles()',
			'id'			=> 'btn_switch_files',
			'checked'		=> 'checked',
			'data-state'	=> 1,
			'title'			=> 'check/uncheck all',
		) );

		$colgroup	= [];
		$heads		= [];
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
		$colgroup	= HtmlElements::ColumnGroup( $colgroup );
		$heads		= HtmlElements::TableHeads( $heads ) ;
		$thead		= HtmlTag::create( 'thead', $heads );
		$tbody		= HtmlTag::create( 'tbody', $list, ['id' => 'file-rows'] );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table module-update-files'] );
		return $table;
	}

	public function setEnv( $env ){
		$this->env	= $env;
	}
}
?>
