<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Navigation_Bootstrap_DropdownList
{
	protected static array $matches	= [];

	public static function render( $map, $current ): string
	{
		$current	= self::calculateMatches( $map, $current ?: 'index' );
		$list		= [];
		foreach( $map as $nr => $entry ){
			$entry	= (object) $entry;
			switch( $entry->type ){
				case 'menu':
					$list[]	= self::renderDropdownItem( $entry, $current, $nr );
					break;
				case 'link':
					$list[]	= self::renderLinkItem( $entry, $current );
					break;
				case 'divider':
					$list[]	= self::renderDivider( $entry, TRUE );
					break;
			}
		}
		return HtmlTag::create( 'ul', $list, [
			'class'		=> 'nav',
			'role'		=> 'navigation'
		] );
	}

	protected static function calculateMatches( $map, $current ): int|string|NULL
	{
		foreach( $map as $entry ){
			if( $entry->type === "menu" )
				self::calculateMatches( $entry->links, $current );
			else if( $entry->type === "link" )
				self::$matches[$entry->path]	= levenshtein( $current, $entry->path );
		}
		asort( self::$matches );
		$matches	= array_keys( self::$matches );
		return array_shift( $matches );
	}

	protected static function renderDivider( $entry, bool $vertical = FALSE ): string
	{
		if( $vertical )
			return HtmlTag::create( 'li', '', ['class' => 'divider-vertical'] );
		return HtmlTag::create( 'li', '', ['class' => 'divider'] );
	}

	protected static function renderDropdownItem( $map, $current, $nr ): string
	{
		$list	= [];
		$active	= FALSE;
		foreach( $map->links as $entry ){
			$entry	= (object) $entry;
			switch( $entry->type ){
				case 'link':
					$list[]	= self::renderLinkItem( $entry, $current );
					if( $current === $entry->path )
						$active	= TRUE;
					break;
				case 'divider':
					$list[]	= self::renderDivider( $entry );
					break;
			}
		}
		$caret		= HtmlTag::create( 'b', '', ['class' => 'caret'] );
		$toggle		= HtmlTag::create( 'a', $caret.'&nbsp;&nbsp;'.$map->label, [
			'class'			=> 'dropdown-toggle',
			'data-toggle'	=> 'dropdown',
			'role'			=> 'button',
			'href'			=> '#',
			'id'			=> 'drop-'.$nr,
		] );
		$menu	= HtmlTag::create( 'ul', $list, [
			'class'				=> 'dropdown-menu',
			'role'				=> 'menu',
			'aria-labelledby'	=> 'drop-'.$nr
		] );
		return HtmlTag::create( 'li', $toggle.$menu, [
			'class'		=> $active ? 'dropdown active' : 'dropdown'
		] );
	}

	protected static function renderLabelWithIcon( $entry ): string
	{
		if( !isset( $entry->icon ) )
			return $entry->label;
		if( str_starts_with( $entry->icon, 'fa ' ) )
			$icon	= HtmlTag::create( 'i', '', ['class' => $entry->icon] );
		else
			$icon	= HtmlTag::create( 'i', '', ['class' => 'icon-'.$entry->icon] );
		return $icon.'&nbsp;'.$entry->label;
	}

	protected static function renderLinkItem( $entry, $current ): string
	{
		$class	= NULL;
		if( $current === $entry->path ){
			$active	= TRUE;
			$class	= 'active';
		}
		$label		= self::renderLabelWithIcon( $entry );
		$link		= HtmlTag::create( 'a', $label, array(
			'href'	=> $entry->path,
			'title'	=> !empty( $entry->desc ) ? $entry->desc : NULL,
		) );
		return HtmlTag::create( 'li', $link, [
			'class'	=> $class,
			'role'	=> 'presentation'
		] );
	}
}
