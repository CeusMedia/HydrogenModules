<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Group $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $group */
/** @var int $userCount */
/** @var array $controllerActions */

$w			= (object) $words['editRights'];

$showAll	= !TRUE;

$usedModules	= [];
foreach( $controllerActions as $controller )
	$usedModules[]	= $controller->module->id;
$usedModules	= array_unique( $usedModules );
asort( $usedModules );

$list	= [];
foreach( $usedModules as $usedModule ){
	$result	= renderModuleControllers( $acl, $group->groupId, $usedModule, $controllerActions, $words );
	$list[] = $result->list;
}

$tableRights	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'
		<div class="pull-right">
		<button type="button" class="btn btn-mini btn-info" id="button-toggle">
			<label class="checkbox">
				'.HtmlTag::create( 'input', NULL, [
					'type'		=> "checkbox",
					'id'		=> "input-toggle-rights-all",
					'checked'	=> $showAll ? 'checked' : NULL,
				] ).'
				'.$w->labelShowAll.'
			</label>
		</button>
		</div>
	</h3>
	<div class="content-panel-inner" id="group-edit-rights">
		'.$tableRights.'
	</div>
</div>';

function renderModuleControllers( $acl, int|string $groupId, $moduleId, array $controllerActions, array $words ): object
{
	$list		= [];
	$rows		= [];
	$changable	= FALSE;
	$iconModule		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-archive'] ).'&nbsp;';
	$iconController	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cog'] ).'&nbsp;';
	foreach( $controllerActions as $controller ){
		if( $controller->module && $controller->module->id != $moduleId )
			continue;
		$actionToggles	= renderControllerActions( $acl, $groupId, $controller, $words );
		if( !$actionToggles->list )
			continue;
		$changable	= $changable || $actionToggles->changable;
		$module	= $controller->module ? $controller->module->title : 'local';
		if( $controller->module ){
			$module	= $controller->module->title;
			if( $controller->moduleWords && isset( $controller->moduleWords['title'] ) )
				$module	= $controller->moduleWords['title'];
			if( $controller->module->description ){
				$description	= array_slice( explode( "\n", $controller->module->description ), 0, 1 );
				$module			= HtmlTag::create( 'abbr', $module, ['title' => $description] );
			}
		}
		$actions	= HtmlTag::create( 'ul', join( $actionToggles->list ), [] );
		$path		= strtolower( str_replace( '_', '/', $controller->name ) )/*.'/'*/;
		$path		= HtmlTag::create( 'abbr', $path, ['title' => 'Controller: '.$controller->name] );
		$path		= HtmlTag::create( 'small', '&nbsp;'.$iconController.$path, ['class' => 'not-muted'] );

		$label		= $path;

		$labelPath			= HtmlTag::create( 'span', $label, ['class' => 'label-path'] );
		$labelModule		= HtmlTag::create( 'small', '<br/>Modul: '.$module, ['class' => 'muted label-module', 'style' => 0/*$showAll*/ ? NULL : 'display: none'] );
		$labelController	= HtmlTag::create( 'small', '<br/>Controller: '.$controller->name, ['class' => 'muted label-controller', 'style' => 0/*$showAll*/ ? NULL : 'display: none'] );

		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $labelPath/*.$labelController/*.$labelModule*/, ['class' => 'column-controller autocut'] ),
			HtmlTag::create( 'td', $actions, ['class' => 'column-actions'] ),
		], ['style' => $actionToggles->changable || 0/*$showAll*/ ? NULL : 'display: none'] );
	}
	if( $rows ){
		$rows	= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '300px', '' ),
			HtmlTag::create( 'tbody', $rows ),
		], ['class' => 'table table-fixed table-condensed'] );
		$list[]	= HtmlTag::create( 'li', $iconModule.$module.$rows, [
			'class'	=> 'acl-module '.( $changable ? 'changable' : '' ),
			'style' => $changable || 0/*$showAll*/ ? NULL : 'display: none'
		] );
	}
	return (object) ['list' => $list, 'changable' => $changable];
}

function renderControllerActions( $acl, int|string $groupId, object $controller, array $words ): object
{
	$list		= [];
	$changableAtAll	= FALSE;
	foreach( $controller->methods as $action => $method ){
//print_m( $modules[$controller->module->id] );die;
		$access	= $acl->hasRight( $groupId, $controller->name, $action );
		$check	= "";
		$title	= $words['type-right'][$access];
		$id		= 'input-group-right-'.$groupId.'-'.$controller->name.'-'.$action;
		$changable	= FALSE;
		switch( $access ){
			case -2:								//  public outside
				$class	= "gray";
				break;
			case 0:									//  not allowed
				$class	= "red changable";
				$changable	= TRUE;
				break;
			case 1:									//  allowed by group right
				$class	= "green changable";
				$changable	= TRUE;
				break;
			case 2:									//  full access
			case 3:									//  public
			case 5:									//  public inside
				$class	= "green";
				break;
			case -1:								//  access denied
			default:
				$class	= "red";
				break;
		}
		$label	= HtmlTag::create( 'span', $method->name );
		$list[]	= HtmlTag::create( 'li', $check.$label, [
			'class'	=> 'action '.$class,
			'id'	=> $changable ? $id : NULL,
			'title'	=> $title,
			'style'	=> $changable /*|| $showAll*/ ? NULL : 'display: none',
		], [
			'controller'	=> $controller->name,
			'action'		=> $action,
		] );
		$changableAtAll	= $changableAtAll || $changable;
	}
	return (object) ['list' => $list, 'changable' => $changableAtAll];
}
