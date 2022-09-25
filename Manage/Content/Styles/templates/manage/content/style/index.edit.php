<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index.edit'];

if( $file ){
	$textarea	= HtmlTag::create( 'textarea', $content, array(
			'name'	=> 'content',
			'id'	=> 'input_content',
			'class'	=> 'ace-auto CodeMirror-auto span12',
			'style'	=> 'height: 660px',
			'rows'	=> 10,
		), 	array(
			'ace-mode'						=> 'css',
			'ace-option-maxLines'			=> 30,
			'codemirror-mode'				=> 'text/css',
			'codemirror-read-only'			=> $readonly ? 'nocursor' : NULL,
			'codemirror-callback-change'	=> 'ModuleManageStyle.onCodeMirrorChange',
			'codemirror-callback-save'		=> 'ModuleManageStyle.onCodeMirrorSave',
	) );
	$buttonSave	= HtmlTag::create( 'button', '<i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave, array(
		'type'		=> 'submit',
		'name'		=> 'save',
		'class'		=> 'btn btn-primary',
		'disabled'	=> $readonly ? 'disabled' : NULL,
	) );
	$noteReadonly	= '&nbsp;<em class="muted">File is read-only.</em>';
	if( !$readonly )
		$noteReadonly	= '';
	return '
		<div class="content-panel" id="panel-file-editor" style="display: none">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/content/style/'.$file.'" method="post">
					'.$textarea.'
					<div class="buttonbar">
						'.$buttonSave.'
						'.$noteReadonly.'
					</div>
				</form>
			</div>
		</div>';
}
return '
	<div class="content-panel">
		<h3>'.$w->heading.'</h3>
		<div class="content-panel-inner">
			<div class="muted"><em>'.$w->noFileSelected.'<em></div>
			<br/>
			<div class="buttonbar">
				<button type="button" name="save" class="btn btn-primary" disabled="disabled"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</div>
	</div>';
?>
