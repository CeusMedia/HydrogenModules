<?php

$w			= (object) $words['editRights'];

$showAll	= !TRUE;

$usedModules	= [];
foreach( $controllerActions as $controller )
	$usedModules[]	= $controller->module->id;
$usedModules	= array_unique( $usedModules );
asort( $usedModules );

$list	= [];
foreach( $usedModules as $usedModule ){
	$result	= renderModuleControllers( $acl, $roleId, $usedModule, $controllerActions, $words );
	$list[] = $result->list;
}

$tableRights	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'
		<div class="pull-right">
		<button type="button" class="btn btn-mini btn-info" id="button-toggle">
			<label class="checkbox">
				'.UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> "checkbox",
					'id'		=> "input-toggle-rights-all",
					'checked'	=> $showAll ? 'checked' : NULL,
				) ).'
				'.$w->labelShowAll.'
			</label>
		</button>
		</div>
	</h3>
	<div class="content-panel-inner" id="role-edit-rights">
		'.$tableRights.'
	</div>
</div>';

function renderModuleControllers( $acl, $roleId, $moduleId, $controllerActions, $words ){
	$list		= [];
	$rows		= [];
	$changable	= FALSE;
	$iconModule		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) ).'&nbsp;';
	$iconController	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) ).'&nbsp;';
	foreach( $controllerActions as $controller ){
		if( $controller->module && $controller->module->id != $moduleId )
			continue;
		$actionToggles	= renderControllerActions( $acl, $roleId, $controller, $words );
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
				$module			= UI_HTML_Tag::create( 'abbr', $module, array( 'title' => $description ) );
			}
		}
		$actions	= UI_HTML_Tag::create( 'ul', join( $actionToggles->list ), array() );
		$path		= strtolower( str_replace( '_', '/', $controller->name ) )/*.'/'*/;
		$path		= UI_HTML_Tag::create( 'abbr', $path, array( 'title' => 'Controller: '.$controller->name ) );
		$path		= UI_HTML_Tag::create( 'small', '&nbsp;'.$iconController.$path, array( 'class' => 'not-muted' ) );

		$label		= $path;

		$labelPath			= UI_HTML_Tag::create( 'span', $label, array( 'class' => 'label-path' ) );
		$labelModule		= UI_HTML_Tag::create( 'small', '<br/>Modul: '.$module, array( 'class' => 'muted label-module', 'style' => 0/*$showAll*/ ? NULL : 'display: none' ) );
		$labelController	= UI_HTML_Tag::create( 'small', '<br/>Controller: '.$controller->name, array( 'class' => 'muted label-controller', 'style' => 0/*$showAll*/ ? NULL : 'display: none' ) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $labelPath/*.$labelController/*.$labelModule*/, array( 'class' => 'column-controller autocut' ) ),
			UI_HTML_Tag::create( 'td', $actions, array( 'class' => 'column-actions' ) ),
		), array( 'style' => $actionToggles->changable || 0/*$showAll*/ ? NULL : 'display: none' ) );
	}
	if( $rows ){
		$rows	= UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( '300px', '' ),
			UI_HTML_Tag::create( 'tbody', $rows ),
		), array( 'class' => 'table table-fixed table-condensed' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $iconModule.$module.$rows,  array(
			'class'	=> 'acl-module '.( $changable ? 'changable' : '' ),
			'style' => $changable || 0/*$showAll*/ ? NULL : 'display: none'
		) );
	}
	return (object) array( 'list' => $list, 'changable' => $changable );
}

function renderControllerActions( $acl, $roleId, $controller, $words ){
	$list		= [];
	$changableAtAll	= FALSE;
	foreach( $controller->methods as $action => $method ){
//print_m( $modules[$controller->module->id] );die;
		$access	= $acl->hasRight( $roleId, $controller->name, $action );
		$check	= "";
		$for	= NULL;
		$title	= $words['type-right'][$access];
		$id		= 'input-role-right-'.$roleId.'-'.$controller->name.'-'.$action;
		$changable	= FALSE;
		switch( $access ){
			case -2:								//  public outside
				$class	= "gray";
				break;
			case 0:									//  not allowed
				$class	= "red changable";
				$changable	= true;
				break;
			case 1:									//  allowed by role right
				$class	= "green changable";
				$changable	= true;
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
		$label	= UI_HTML_Tag::create( 'span', $method->name, array() );
		$list[]	= UI_HTML_Tag::create( 'li', $check.$label, array(
			'class'	=> 'action '.$class,
			'id'	=> $changable ? $id : NULL,
			'title'	=> $title,
			'style'	=> $changable /*|| $showAll*/ ? NULL : 'display: none',
		), array(
			'controller'	=> $controller->name,
			'action'		=> $action,
		) );
		$changableAtAll	= $changableAtAll || $changable;
	}
	return (object) array( 'list' => $list, 'changable' => $changableAtAll );
}
