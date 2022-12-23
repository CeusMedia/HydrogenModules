<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Work_FTP extends View
{
	public function index()
	{
		$pathCurrent	= $this->getData( 'pathCurrent' );
		$pathDeepest	= $this->getData( 'pathDeepest' );
		$entries		= $this->getData( 'entries' );

		$this->addData( 'table', $this->renderFileTable( $pathCurrent, $entries ) );
		$this->addData( 'position', $this->renderPosition( $pathCurrent, $pathDeepest ) );
	}

	public function login()
	{
	}

	protected function renderPosition( string $pathCurrent, string $pathDeepest, string $labelHome = "Home", string $labelPosition = "Position: " ): string
	{
		$way	= "";
		$levels	= [''	=> $labelHome];
		foreach( explode( "/", $pathDeepest ) as $part ){
			if( strlen( trim( $part ) ) ){
				$way .= $way ? '/'.$part : $part;
				$levels[$way]	= $part;
			}
		}
		foreach( $levels as $path => $label ){
			$divider	= "";
			$attrItem	= ['class' => 'active'];
			if( $path !== $pathDeepest )
				$divider	= HtmlTag::create( 'span', '/', ['class' => 'divider'] );
			if( $pathCurrent !== $path ){
				$url	= './work/FTP'.( $path ? "?path=".$path : "" );
				$label	= HtmlTag::create( 'a', $label, ['href' => $url] );
				$attrItem['class']	= NULL;
			}
			$list[]	= HtmlTag::create( 'li', $label.' '.$divider, $attrItem );
		}
		if( $labelPosition )
			array_unshift( $list, HtmlTag::create( 'li', $labelPosition ) );
		return HtmlTag::create( 'ul', $list, ['class' => 'breadcrumb'] );
	}

	protected function renderFileTable( string $path, array $entries ): string
	{
		$rows		= [];
		$folders	= [];
		$files		= [];
		foreach( $entries as $entry ){
#			print_m( $entry );
#			die;
			$entry	= (object) $entry;
			$icon	= '<i class="icon-'.( $entry->isdir ? 'folder-close' : 'file' ).'"></i> ';
			$label		= $icon.$entry->name;

			if( $entry->isdir ){
				$pathNew	= $path ? $path.'/'.$entry->name : $entry->name;
				$link		= HtmlTag::create( 'a', $label, ['href' => './work/FTP?path='.$pathNew] );
				$size		= $entry->folders.' <i class="icon-folder-close"></i> / '.$entry->files.' <i class="icon-file"></i>';
			}
			else{
				$size		= UnitFormater::formatBytes( $entry->size, 1 );
				$link	= $label;
			}

			$cells	= array(
				HtmlTag::create( 'td', $link ),
				HtmlTag::create( 'td', $size ),
				HtmlTag::create( 'td', $entry->day.' '.$entry->month.' '.$entry->year ),
				HtmlTag::create( 'td', $entry->permissions ),
			);
			$entry->html	= HtmlTag::create( 'tr', $cells );
			if( $entry->isdir ){
				$folders[$entry->name]	= $entry->html;
			}
			else{
				$files[$entry->name]	= $entry->html;
			}
		}
		ksort( $folders );
		ksort( $files );
		$list	= $folders + $files;
		$colgroup	= HtmlElements::ColumnGroup( ["50%", "15%", "15%", "15%"] );
		$heads		= HtmlElements::TableHeads( ['Name', 'Size', 'Date', 'Permissions'] );
		$thead	= HtmlTag::create( 'thead', $heads );
		$tbody	= HtmlTag::create( 'tbody', HtmlTag::create( 'tr', $list ) );
		return HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed table-striped'] );
	}
}
