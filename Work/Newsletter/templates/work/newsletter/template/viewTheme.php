<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconInstall	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus-circle' ) );

$helper	= new View_Helper_Work_Newsletter_ThemeFacts( $env );
$helper->setThemeData( $theme );
//$helper->setListAttributes( array( 'class' => 'dl-horizontal' ) );

return '
<div class="content-panel">
	<h3>Theme</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
		 	<div class="span6">
				<h4>'.$theme->title.'</h4>
				<div class="description" style="font-size: 0.9em">'.nl2br( $theme->description ).'</div>
				'.$helper->render().'
			</div>
		 	<div class="span6" style="text-align: center">
				<img src="'.$themePath.$theme->folder.'/template.png" style="max-height: 400px; border: 1px solid gray; box-shadow: 1px 2px 4px gray"/>
			</div>
		</div>
		<div class="buttonbar">
			<a href="./work/newsletter/template/" class="btn">'.$iconCancel.'&nbsp;zurück</a>
			<a href="./work/newsletter/template/installTheme/'.$theme->id.'" class="btn btn-success">'.$iconInstall.'&nbsp;installieren</a>
		</div>
	</div>
</div>';

return print_m( $theme, NULL, NULL, TRUE );
