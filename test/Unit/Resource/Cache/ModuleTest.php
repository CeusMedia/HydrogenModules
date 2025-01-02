<?php
namespace CeusMedia\HydrogenModulesUnitTest\Resource\Cache;

use CeusMedia\Cache\Adapter\AbstractAdapter;
use CeusMedia\Cache\Adapter\Folder as FolderAdapter;
use CeusMedia\Cache\Adapter\Noop as NoopAdapter;
use CeusMedia\Cache\Encoder\Igbinary as IgbinaryEncoder;
use CeusMedia\Cache\Encoder\JSON as JsonEncoder;
use CeusMedia\Cache\Encoder\Msgpack as MsgpackEncoder;
use CeusMedia\Cache\SimpleCacheFactory;
use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;

class ModuleTest extends BaseTestCase
{
	/** @noinspection PhpUnhandledExceptionInspection */
	public function testHookEnvCacheResource(): void
	{
		$this->installModule( 'Resource:Cache' );
		$env		= $this->createEnvironment();

		$cache		= $env->get( 'cache' );
		self::assertInstanceOf( SimpleCacheInterface::class, $cache );
		self::assertInstanceOf( AbstractAdapter::class, $cache );
		self::assertInstanceOf( NoopAdapter::class, $cache );
		self::assertTrue( $cache->set( 'key', 'value' ) );
		self::assertNull( $cache->get( 'key' ) );

		$this->installModule( 'Resource:Cache', ['type' => 'Folder', 'resource' => $this->pathApp.'.test'] );
		$env		= $this->createEnvironment();

		$cache		= $env->get( 'cache' );
		self::assertInstanceOf( SimpleCacheInterface::class, $cache );
		self::assertInstanceOf( AbstractAdapter::class, $cache );
		self::assertInstanceOf( FolderAdapter::class, $cache );
		self::assertTrue( $cache->set( 'key', 'value' ) );
		self::assertEquals( 'value', $cache->get( 'key' ) );
		$cache->clear();
		$this->uninstallModule( 'Resource:Cache' );
		rmdir( $this->pathApp.'.test' );
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testNoopCache(): void
	{
		$env		= $this->createEnvironment();
		$cache	= SimpleCacheFactory::createStorage( 'Noop' );
		self::assertInstanceOf( SimpleCacheInterface::class, $cache );
		self::assertInstanceOf( AbstractAdapter::class, $cache );
		self::assertEquals( $cache, $env->get( 'cache' ) );
		self::assertTrue( $cache->set( 'key', 'value' ) );
		self::assertNull( $cache->get( 'key' ) );
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testFileCache(): void
	{
		$cache	= SimpleCacheFactory::createStorage( 'Folder', $this->pathApp.'.cache' );
		self::assertInstanceOf( SimpleCacheInterface::class, $cache );
		self::assertInstanceOf( AbstractAdapter::class, $cache );
		self::assertInstanceOf( FolderAdapter::class, $cache );
		self::assertTrue( $cache->set( 'key', 'value' ) );
		self::assertEquals( 'value', $cache->get( 'key' ) );
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testFileCacheWithIgbinaryEncoder(): void
	{
		if( !extension_loaded( 'msgpack' ) )
			$this->markTestSkipped( 'Extension "msgpack" not installed' );

		$text	= file_get_contents( __FILE__ );
		$cache	= SimpleCacheFactory::createStorage( 'Folder', $this->pathApp.'.cache' );
		$cache->setEncoder(IgbinaryEncoder::class );
		self::assertTrue( $cache->set( 'key', $text ) );
		self::assertEquals( $text, $cache->get( 'key' ) );
		$cache->clear();
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testFileCacheWithJsonEncoder()
	{
		if( !extension_loaded( 'msgpack' ) )
			$this->markTestSkipped( 'Extension "msgpack" not installed' );

		$text	= file_get_contents( __FILE__ );
		$cache	= SimpleCacheFactory::createStorage( 'Folder', $this->pathApp.'.cache' );
		$cache->setEncoder(JsonEncoder::class );
		self::assertTrue( $cache->set( 'key', $text ) );
		self::assertEquals( $text, $cache->get( 'key' ) );
		$cache->clear();
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testFileCacheWithMsgpackEncoder(): void
	{
		if( !extension_loaded( 'msgpack' ) )
			$this->markTestSkipped( 'Extension "msgpack" not installed' );

		$text	= file_get_contents( __FILE__ );
		$cache	= SimpleCacheFactory::createStorage( 'Folder', $this->pathApp.'.cache' );
		$cache->setEncoder(MsgpackEncoder::class );
		self::assertTrue( $cache->set( 'key', $text ) );
		self::assertEquals( $text, $cache->get( 'key' ) );
		$cache->clear();
	}

/*	protected function setUp(): void
	{
		parent::setUp();
	}

	protected function tearDown(): void
	{
		parent::setUp();
	}*/
}