<?php

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPrev	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconNext	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/edit/'.$form->formId.'" method="post">
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $form->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				'.$navButtons['list'].'
<!--				'./*$navButtons['prevBlocks'].*/'-->
				'.$navButtons['prevView'].'
				'.UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				) ).'
				'.$navButtons['nextManager'].'
			</div>
		</form>
	</div>
</div>';
?>
