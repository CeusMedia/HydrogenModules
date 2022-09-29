<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$title		= empty( $title ) ? '' : $title;
$description	= empty( $description ) ? '' : $description;
$description	= empty( $status ) ? '' : $status;

$optStatus	= $words['states'];
krsort( $optStatus );
$optStatus['_selected']	= $status;

return '
<div class="column-right-33">
</div>
<div class="column-left-66">
	'.HtmlTag::create( 'h2', $words['add']['heading'] ).'
	'.HtmlElements::Form( 'add', './admin/project/add' ).'
		<fieldset>
			<legend>'.$words['add']['legend'].'</legend>
			<ul class="input">
				<li>
					'.HtmlTag::create( 'label', $words['add']['labelTitle'], ['for' => 'title'] ).'<br/>
					'.HtmlElements::Input( 'title', htmlspecialchars( $title, ENT_COMPAT, 'UTF-8' ), 'l' ).'
				</li>
				<li>
					'.HtmlTag::create( 'description', $words['add']['labelDescription'], ['for' => 'description'] ).'<br/>
					'.HtmlElements::TextArea( 'description', htmlspecialchars( $description, ENT_COMPAT, 'UTF-8' ), 'xl-m' ).'
				</li>
				<li>
					'.HtmlTag::create( 'label', $words['add']['labelStatus'], ['for' => 'status'] ).'<br/>
					'.HtmlElements::Select( 'status', $optStatus, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				</div>
				'.HtmlElements::LinkButton( './admin/project', $words['add']['buttonCancel'], 'button cancel' ).'
				'.HtmlElements::Button( 'doAdd', $words['add']['buttonSave'], 'button save' ).'
				<div class="clearfloat"></div>
			</div>
		</fieldset>
	</form>
</div>
';

