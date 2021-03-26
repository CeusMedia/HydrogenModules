<?php
class View_Helper_Navigation_Bootstrap_DropdownList
{
	protected static $matches	= array();

	public static function render( $map, $current ): string
	{
		$current	= self::calculateMatches( $map, $current ? $current : 'index' );
		$list		= array();
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
		return UI_HTML_Tag::create( 'ul', $list, array(
			'class'		=> 'nav',
			'role'		=> 'navigation'
		) );
	}

	protected static function calculateMatches( $map, $current )
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
			return UI_HTML_Tag::create( 'li', '', array( 'class' => 'divider-vertical' ) );
		return UI_HTML_Tag::create( 'li', '', array( 'class' => 'divider' ) );
	}

	protected static function renderDropdownItem( $map, $current, $nr ): string
	{
		$list	= array();
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
		$caret		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'caret' ) );
		$toggle		= UI_HTML_Tag::create( 'a', $caret.'&nbsp;&nbsp;'.$map->label, array(
			'class'			=> 'dropdown-toggle',
			'data-toggle'	=> 'dropdown',
			'role'			=> 'button',
			'href'			=> '#',
			'id'			=> 'drop-'.$nr,
		) );
		$menu	= UI_HTML_Tag::create( 'ul', $list, array(
			'class'				=> 'dropdown-menu',
			'role'				=> 'menu',
			'aria-labelledby'	=> 'drop-'.$nr
		) );
		return UI_HTML_Tag::create( 'li', $toggle.$menu, array(
			'class'		=> $active ? 'dropdown active' : 'dropdown'
		) );
	}

	protected static function renderLabelWithIcon( $entry ): string
	{
		if( !isset( $entry->icon ) )
			return $entry->label;
		if( preg_match( '/^fa /', $entry->icon ) )
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $entry->icon ) );
		else
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-'.$entry->icon ) );
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
		$link		= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> $entry->path,
			'title'	=> !empty( $entry->desc ) ? $entry->desc : NULL,
		) );
		return UI_HTML_Tag::create( 'li', $link, array(
			'class'	=> $class,
			'role'	=> 'presentation'
		) );
	}
}
