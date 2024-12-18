<?php

namespace CeusMedia\HydrogenModulesIntegrationTest\Manage\Projects;

use CeusMedia\Common\Net\HTTP\Header\Field as HttpHeaderField;
use CeusMedia\HydrogenModulesIntegrationTest\BaseTestCase;
use PHPHtmlParser\Dom as HtmlDomParser;

class DefaultTest extends BaseTestCase
{
	public function testA()
	{
		$response	= $this->get( '/manage/project/' );

		self::assertEquals( '200 OK', $response->getStatus() );

//		remark( 'Headers: ' );
//		array_map( function( HttpHeaderField $field ){
//			remark( ' - '.$field->toString() );
//		}, $response->getHeaders());

		$parser	= new HtmlDomParser();
		$parser->loadStr( $response->getBody() );
//		print_m( $response->getBody() );
		/** @var \PHPHtmlParser\Dom\Node\HtmlNode $body */
		$body = $parser->getElementsByTag( 'body' )->toArray()[0];
//		print_m( $body->innerHtml() );

		$firstProjectLabel	= $body->find('table > tbody > tr > td.cell-title > a')->toArray()[0]->innerHtml();
		self::assertEquals( 'Test&nbsp;<i class="fa fa-star"></i>', $firstProjectLabel );
	}
}