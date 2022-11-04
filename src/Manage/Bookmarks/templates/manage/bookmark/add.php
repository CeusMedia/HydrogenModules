<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconList		= HtmlTag::create( 'i', '', ['class' => 'icon-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
}

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				'.$this->renderList().'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>Neues Lesezeichen</h3>
			<div class="content-panel-inner">
				<form action="./manage/bookmark/add" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">Internet-Adresse <small class="muted">(vollständige URL)</small></label>
							<input class="span12" type="text" name="url" id="input_url" required="required" value=""/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							<input class="span12" type="text" name="title" id="input_title" required="required" value=""/>
						</div>
					</div>
					<div class="buttonbar">
						<!--<a class="btn btn-small" href="./manage/bookmark">'.$iconList.' zur Liste</a>-->
						<button type="submit" name="save" class="btn not-btn-small btn-primary">'.$iconSave.' speichern</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
?>
