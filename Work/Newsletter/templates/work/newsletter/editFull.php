<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';

$value		= htmlentities( $newsletter->html, ENT_QUOTES, 'UTF-8' );
$editor		= UI_HTML_Tag::create( 'textarea', $value, array(
	'name'		=> 'html',
	'id'		=> 'input_html',
	'rows'		=> '15',
) );

$html		= $template->html;
$values		= array(
	'nr'				=> $newsletter->newsletterId,
	'title'				=> $newsletter->heading,
	'content'			=> $editor,
	'salutation'		=> $words->preview['salutation'],
	'prefix'			=> $words->preview['prefix'],
	'firstname'			=> $words->preview['firstname'],
	'surname'			=> $words->preview['surname'],
	'registerDate'		=> $words->preview['registerDate'],
	'registerTime'		=> $words->preview['registerTime'],
	'tracking'			=> '',
);
foreach( $values as $key => $value )
	$html	= str_replace( '[#'.$key.'#]', $value, $html );

$w			= (object) $words->edit_fullscreen;

$body		= '
<script>
$(document).ready(function(){
	ModuleWorkNewsletter.init("'.$env->url.'", '.$newsletter->newsletterTemplateId.', 0);
});
</script>
<form action="./work/newsletter/edit/'.$newsletter->newsletterId.'" method="post">
	<div class="nav navbar navbar-fixed-top">
		<div class="navbar-inner">
			<center>
				<a href="./work/newsletter/edit/'.$newsletter->newsletterId.'" class="btn">'.$iconCancel.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-success">'.$iconSave.$w->buttonSave.'</button>
			</center>
		</div>
	</div>
	<div style="margin-top: 50px">
		'.$html.'
	</div>
</form>';

$page		= $env->get( 'page' );

$baseUrl	= $env->url;
//if( $env->getModules()->has( 'Resource_Frontend' ) )
//	$baseUrl	= Logic_Frontend::getInstance( $env )->getUri();
$page->setBaseHref( $baseUrl );
/*
$page	= new UI_HTML_PageFrame();
foreach( explode( ",", $template->styles ) as $style )
	$page->addStylesheet( trim( $style ) );*/

//$page->addStylesheet( './work/newsletter/template/ajaxGetStyle/'.$newsletter->newsletterTemplateId );
$page->addBody( $body );
//$page->addBody( $script );
$page->addBody( UI_HTML_Tag::create( 'style', $template->style ) );
print( $page->build( array( 'class' => 'mail mail-newsletter' ) ) );
exit;
