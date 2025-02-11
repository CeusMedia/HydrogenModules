<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_HtmlDiff
{
	protected ?Environment $env	= NULL;

	protected ?string $html1	= NULL;

	protected ?string $html2	= NULL;

	public function __construct( Environment $env = NULL, string $html1 = NULL, string $html2 = NULL )
	{
		if( $env )
			$this->setEnv( $env );
		if( !is_null( $html1 ) && !is_null( $html2 ) )
			$this->setContents( $html1, $html2 );
	}

/*	public function __toString(){
		return $this->render();
	}
*/
	public function render(): string
	{
		if( !$this->env )
			throw new RuntimeException( "No environment set" );
		if( is_null( $this->html1 ) || is_null( $this->html2 ) )
			throw new RuntimeException( "No contents set" );
		$diff	= new HtmlDiff( $this->html1, $this->html2 );
		$diff->build();
		return HtmlTag::create( 'div', $diff->getDifference(), ['class' => 'htmldiff'] );
	}

	public static function renderStatic( Environment $env, string $html1, string $html2 ): string
	{
		$helper	= new View_Helper_HtmlDiff( $env );
		$helper->setContents( $html1, $html2 );
		return $helper->render();
	}

	public function setContents( string $html1, string $html2 ): self
	{
		$this->html1	= $html1;
		$this->html2	= $html2;
		return $this;
	}

	public function setEnv( Environment $env ): self
	{
		$this->env	= $env;
		return $this;
	}
}

class HtmlDiff
{
	private string $content;
	private string $oldText;
	private string $newText;
	private array $oldWords = [];
	private array $newWords = [];
	private array $wordIndices = [];
	private string $encoding;
	private array $specialCaseOpeningTags = ["/<strong[^>]*/i", "/<b[^>]*/i", "/<i[^>]*/i", "/<big[^>]*/i", "/<small[^>]*/i", "/<u[^>]*/i", "/<sub[^>]*/i", "/<sup[^>]*/i", "/<strike[^>]*/i", "/<s[^>]*/i", '/<p[^>]*/i'];
	private array $specialCaseClosingTags = ["</strong>", "</b>", "</i>", "</big>", "</small>", "</u>", "</sub>", "</sup>", "</strike>", "</s>", '</p>'];

	public function __construct( string $oldText, string $newText, string $encoding = 'UTF-8' )
	{
		$this->oldText = $this->purifyHtml( trim( $oldText ) );
		$this->newText = $this->purifyHtml( trim( $newText ) );
		$this->encoding = $encoding;
		$this->content = '';
	}

	public function getOldHtml(): string
	{
		return $this->oldText;
	}

	public function getNewHtml(): string
	{
		return $this->newText;
	}

	public function getDifference(): string
	{
		return $this->content;
	}

	private function getStringBetween( string $str, string $start, string $end ): string
	{
		$expStr = explode( $start, $str, 2 );
		if( count( $expStr ) > 1 ){
			$expStr = explode( $end, $expStr[ 1 ] );
			if( count( $expStr ) > 1 ){
				array_pop( $expStr );
				return implode( $end, $expStr );
			}
		}
		return '';
	}

	private function purifyHtml( string $html, $tags = null ): string
	{
		if( class_exists( 'Tidy' ) && false ){
			$config = ['output-xhtml'   => true, 'indent' => false];
			$tidy = new tidy;
			$tidy->parseString( $html, $config, 'utf8' );
			$html = (string) $tidy;
			return $this->getStringBetween( $html, '<body>', '</body>' );
		}
		return $html;
	}

	public function build(): string
	{
		$this->splitInputsToWords();
		$this->indexNewWords();
		$operations = $this->operations();
		foreach( $operations as $item ){
			$this->performOperation( $item );
		}
		return $this->content;
	}

	private function indexNewWords(): void
	{
		$this->wordIndices = [];
		foreach( $this->newWords as $i => $word ){
			if( $this->isTag( $word ) ){
				$word = $this->stripTagAttributes( $word );
			}
			if( isset( $this->wordIndices[ $word ] ) ){
				$this->wordIndices[ $word ][] = $i;
			} else {
				$this->wordIndices[ $word ] = [$i];
			}
		}
	}

	private function splitInputsToWords(): void
	{
		$this->oldWords = $this->convertHtmlToListOfWords( $this->explode( $this->oldText ) );
		$this->newWords = $this->convertHtmlToListOfWords( $this->explode( $this->newText ) );
	}

	private function convertHtmlToListOfWords( array $characterString ): array
	{
		$mode = 'character';
		$current_word = '';
		$words = [];
		foreach( $characterString as $character ){
			switch( $mode ){
				case 'character':
					if( $this->isStartOfTag( $character ) ){
						if( $current_word != '' ){
							$words[] = $current_word;
						}
						$current_word = '<';
						$mode = 'tag';
					} else if( preg_match( "[^\s]", $character ) > 0 ){
						if( $current_word != '' ){
							$words[] = $current_word;
						}
						$current_word = $character;
						$mode = 'whitespace';
					} else {
						if( ctype_alnum( $character ) && ( strlen($current_word) == 0 || ctype_alnum( $current_word ) ) ){
							$current_word .= $character;
						} else {
							$words[] = $current_word;
							$current_word = $character;
						}
					}
					break;
				case 'tag' :
					if( $this->isEndOfTag( $character ) ){
						$current_word .= '>';
						$words[] = $current_word;
						$current_word = '';

						if( !preg_match('[^\s]', $character ) ){
							$mode = 'whitespace';
						} else {
							$mode = 'character';
						}
					} else {
						$current_word .= $character;
					}
					break;
				case 'whitespace':
					if( $this->isStartOfTag( $character ) ){
						if( '' !== $current_word ){
							$words[] = $current_word;
						}
						$current_word = '<';
						$mode = 'tag';
					} else if( preg_match( "[^\s]", $character ) ){
						$current_word .= $character;
					} else {
						if( $current_word != '' ){
							$words[] = $current_word;
						}
						$current_word = $character;
						$mode = 'character';
					}
					break;
				default:
					break;
			}
		}
		if( '' !== $current_word ){
			$words[] = $current_word;
		}
		return $words;
	}

	private function isStartOfTag( string $val ): bool
	{
		return '<' === $val;
	}

	private function isEndOfTag( string $val ): bool
	{
		return '>' === $val;
	}

	private function isWhiteSpace( string $value ): bool
	{
		return !preg_match( '[^\s]', $value );
	}

	private function explode( string $value ): array|bool
	{
		// as suggested by @onassar
		return preg_split( '//u', $value );
	}

	private function performOperation( DiffOperation $operation ): void
	{
		switch( $operation->action ){
			case 'equal' :
				$this->processEqualOperation( $operation );
				break;
			case 'delete' :
				$this->processDeleteOperation( $operation, 'diffdel' );
				break;
			case 'insert' :
				$this->processInsertOperation( $operation, 'diffins' );
				break;
			case 'replace':
				$this->processReplaceOperation( $operation );
				break;
			default:
				break;
		}
	}

	private function processReplaceOperation( DiffOperation $operation ): void
	{
		$this->processDeleteOperation( $operation, 'diffmod' );
		$this->processInsertOperation( $operation, 'diffmod' );
	}

	private function processInsertOperation( DiffOperation $operation, string $cssClass ): void
	{
		$text = [];
		foreach( $this->newWords as $pos => $s ){
			if( $pos >= $operation->startInNew && $pos < $operation->endInNew ){
				$text[] = $s;
			}
		}
		$this->insertTag( 'ins', $cssClass, $text );
	}

	private function processDeleteOperation( DiffOperation $operation, string $cssClass ): void
	{
		$text = [];
		foreach( $this->oldWords as $pos => $s ){
			if( $pos >= $operation->startInOld && $pos < $operation->endInOld ){
				$text[] = $s;
			}
		}
		$this->insertTag( 'del', $cssClass, $text );
	}

	private function processEqualOperation( DiffOperation $operation ): void
	{
		$result = [];
		foreach( $this->newWords as $pos => $s ){
			if( $pos >= $operation->startInNew && $pos < $operation->endInNew ){
				$result[] = $s;
			}
		}
		$this->content .= implode( '', $result );
	}

	private function insertTag( string $tag, string $cssClass, array &$words ): void
	{
		while( true ){
			if( 0 === count( $words ) ){
				break;
			}

			$nonTags = $this->extractConsecutiveWords( $words, 'noTag' );

			$specialCaseTagInjection = '';
			$specialCaseTagInjectionIsBefore = false;

			if( count( $nonTags ) != 0 ){
				$text = $this->wrapText( implode( '', $nonTags ), $tag, $cssClass );
				$this->content .= $text;
			} else {
				$firstOrDefault = false;
				foreach( $this->specialCaseOpeningTags as $x ){
					if( preg_match( $x, $words[ 0 ] ) ){
						$firstOrDefault = $x;
						break;
					}
				}
				if( $firstOrDefault ){
					$specialCaseTagInjection = '<ins class="mod">';
					if( 'del' === $tag ){
						unset( $words[ 0 ] );
					}
				} else if( in_array( $words[0], $this->specialCaseClosingTags ) ){
					$specialCaseTagInjection = '</ins>';
					$specialCaseTagInjectionIsBefore = true;
					if( 'del' === $tag ){
						unset( $words[ 0 ] );
					}
				}
			}
			if( 0 === count( $words ) && 0 === strlen( $specialCaseTagInjection ) ){
				break;
			}
			if( $specialCaseTagInjectionIsBefore ){
				$this->content .= $specialCaseTagInjection . implode( '', $this->extractConsecutiveWords( $words, 'tag' ) );
			} else {
				$workTag = $this->extractConsecutiveWords( $words, 'tag' );
				if( isset($workTag[0]) && $this->isOpeningTag( $workTag[ 0 ] ) && !$this->isClosingTag( $workTag[ 0 ] ) ){
					if( strpos( $workTag[ 0 ], 'class=' ) ){
						$workTag[ 0 ] = str_replace( 'class="', 'class="diffmod ', $workTag[ 0 ] );
						$workTag[ 0 ] = str_replace( "class='", 'class="diffmod ', $workTag[ 0 ] );
					} else {
						$workTag[ 0 ] = str_replace( ">", ' class="diffmod">', $workTag[ 0 ] );
					}
				}
				$this->content .= implode( "", $workTag ) . $specialCaseTagInjection;
			}
		}
	}

	private function checkCondition( string $word, string $condition ): bool
	{
		return 'tag' === $condition ? $this->isTag( $word ) : !$this->isTag( $word );
	}

	private function wrapText( string $text, string $tagName, string $cssClass ): string
	{
		return sprintf( '<%1$s class="%2$s">%3$s</%1$s>', $tagName, $cssClass, $text );
	}

	private function extractConsecutiveWords( array &$words, string $condition ): array
	{
		$indexOfFirstTag = null;
		foreach( $words as $i => $word ){
			if( !$this->checkCondition( $word, $condition ) ){
				$indexOfFirstTag = $i;
				break;
			}
		}
		if( NULL !== $indexOfFirstTag ){
			$items = [];
			foreach( $words as $pos => $s ){
				if( $pos >= 0 && $pos < $indexOfFirstTag ){
					$items[] = $s;
				}
			}
			if( $indexOfFirstTag > 0 ){
				array_splice( $words, 0, $indexOfFirstTag );
			}
			return $items;
		} else {
			$items = [];
			foreach( $words as $pos => $s ){
				if( $pos >= 0 && $pos <= count( $words ) ){
					$items[] = $s;
				}
			}
			array_splice( $words, 0, count( $words ) );
			return $items;
		}
	}

	private function isTag( string $item ): bool
	{
		return $this->isOpeningTag( $item ) || $this->isClosingTag( $item );
	}

	private function isOpeningTag( string $item ): bool|int
	{
		return preg_match( "#<[^>]+>\\s*#iU", $item );
	}

	private function isClosingTag( string $item ): bool|int
	{
		return preg_match( "#</[^>]+>\\s*#iU", $item );
	}

	/**
	 * @return DiffOperation[]
	 */
	private function operations(): array
	{
		$positionInOld = 0;
		$positionInNew = 0;
		$operations = [];
		$matches = $this->matchingBlocks();
		$matches[] = new DiffMatch( count( $this->oldWords ), count( $this->newWords ), 0 );
		foreach( $matches as $match ){
			$matchStartsAtCurrentPositionInOld = ( $positionInOld == $match->startInOld );
			$matchStartsAtCurrentPositionInNew = ( $positionInNew == $match->startInNew );
			$action = 'none';

			if( !$matchStartsAtCurrentPositionInOld && !$matchStartsAtCurrentPositionInNew ){
				$action = 'replace';
			} else if( $matchStartsAtCurrentPositionInOld && !$matchStartsAtCurrentPositionInNew ){
				$action = 'insert';
			} else if( !$matchStartsAtCurrentPositionInOld && $matchStartsAtCurrentPositionInNew ){
				$action = 'delete';
			} else { // This occurs if the first few words are the same in both versions
				$action = 'none';
			}
			if( $action != 'none' ){
				$operations[] = new DiffOperation( $action, $positionInOld, $match->startInOld, $positionInNew, $match->startInNew );
			}
			if( 0 !== count( $match ) ){
				$operations[] = new DiffOperation( 'equal', $match->startInOld, $match->endInOld(), $match->startInNew, $match->endInNew() );
			}
			$positionInOld = $match->endInOld();
			$positionInNew = $match->endInNew();
		}
		return $operations;
	}

	private function matchingBlocks(): array
	{
		$matchingBlocks = [];
		$this->findMatchingBlocks( 0, count( $this->oldWords ), 0, count( $this->newWords ), $matchingBlocks );
		return $matchingBlocks;
	}

	private function findMatchingBlocks( int $startInOld, int $endInOld, int $startInNew, int $endInNew, array &$matchingBlocks ): void
	{
		$match = $this->findMatch( $startInOld, $endInOld, $startInNew, $endInNew );
		if( $match !== null ){
			if( $startInOld < $match->startInOld && $startInNew < $match->startInNew ){
				$this->findMatchingBlocks( $startInOld, $match->startInOld, $startInNew, $match->startInNew, $matchingBlocks );
			}
			$matchingBlocks[] = $match;
			if( $match->endInOld() < $endInOld && $match->endInNew() < $endInNew ){
				$this->findMatchingBlocks( $match->endInOld(), $endInOld, $match->endInNew(), $endInNew, $matchingBlocks );
			}
		}
	}

	private function stripTagAttributes( string $word ): string
	{
		$word = explode( ' ', trim( $word, '<>' ) );
		return '<' . $word[ 0 ] . '>';
	}

	private function findMatch( int $startInOld, int $endInOld, int $startInNew, int $endInNew ): ?DiffMatch
	{
		$bestMatchInOld = $startInOld;
		$bestMatchInNew = $startInNew;
		$bestMatchSize = 0;
		$matchLengthAt = [];
		for( $indexInOld = $startInOld; $indexInOld < $endInOld; $indexInOld++ ){
			$newMatchLengthAt = [];
			$index = $this->oldWords[ $indexInOld ];
			if( $this->isTag( $index ) ){
				$index = $this->stripTagAttributes( $index );
			}
			if( !isset( $this->wordIndices[ $index ] ) ){
				$matchLengthAt = $newMatchLengthAt;
				continue;
			}
			foreach( $this->wordIndices[ $index ] as $indexInNew ){
				if( $indexInNew < $startInNew ){
					continue;
				}
				if( $indexInNew >= $endInNew ){
					break;
				}
				$newMatchLength = ( $matchLengthAt[ $indexInNew - 1 ] ?? 0 ) + 1;
				$newMatchLengthAt[ $indexInNew ] = $newMatchLength;
				if( $newMatchLength > $bestMatchSize ){
					$bestMatchInOld = $indexInOld - $newMatchLength + 1;
					$bestMatchInNew = $indexInNew - $newMatchLength + 1;
					$bestMatchSize = $newMatchLength;
				}
			}
			$matchLengthAt = $newMatchLengthAt;
		}
		return $bestMatchSize != 0 ? new DiffMatch( $bestMatchInOld, $bestMatchInNew, $bestMatchSize ) : null;
	}
}

class DiffMatch
{
	public int $startInOld;
	public int $startInNew;
	public int $size;

	public function __construct( int $startInOld, int $startInNew, int $size )
	{
		$this->startInOld = $startInOld;
		$this->startInNew = $startInNew;
		$this->size = $size;
	}

	public function endInOld(): int
	{
		return $this->startInOld + $this->size;
	}

	public function endInNew(): int
	{
		return $this->startInNew + $this->size;
	}
}

class DiffOperation
{
	public string $action;
	public int $startInOld;
	public int $endInOld;
	public int $startInNew;
	public int $endInNew;

	public function __construct( string $action, int $startInOld, int $endInOld, int $startInNew, int $endInNew )
	{
		$this->action		= $action;
		$this->startInOld	= $startInOld;
		$this->endInOld		= $endInOld;
		$this->startInNew	= $startInNew;
		$this->endInNew		= $endInNew;
	}
}
