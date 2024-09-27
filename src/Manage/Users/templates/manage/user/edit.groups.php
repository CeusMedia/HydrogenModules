<?php
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Entity_User $user */
/** @var Entity_Group[] $groups */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['editGroups'];

$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$assignedGroupList	= [];
foreach( $user->groups as $group ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRemove, [
		'href'	=> 'manage/user/removeGroup/'.$user->userId.'/'.$group->groupId,
		'class' => 'btn btn-danger btn-small',
	] );
	$assignedGroupList[]	= HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $buttonRemove, ['class' => 'pull-right'] ),
		HtmlTag::create( 'span', $group->title ),
	] );
}

$list	= [];
foreach( $groups as $group ){
	if( !in_array( $group, $user->groups, TRUE ) )
		$list[$group->groupId] = $group->title;
}
$optAdditionalGroups	= HtmlElements::Options( $list );

$buttonAdd	= HtmlTag::create( 'button', $w->buttonAdd, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		join( $assignedGroupList ),
		HtmlTag::create( 'hr' ),
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'select', $optAdditionalGroups, [
						'name'	=> 'group',
						'class'	=> '',
					] ),
				], ['class' => 'span6'] ),
				HtmlTag::create( 'div', $buttonAdd, ['class' => 'span2'] ),
			], ['class' => 'row-fluid'] ),
		], [
			'action'	=> 'manage/user/addUserToGroup/'.$user->userId,
			'method'	=> 'POST',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );