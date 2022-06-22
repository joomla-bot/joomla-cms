<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Task\Checkfiles\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Event\Dispatcher;
use Joomla\Filesystem\Folder;
use Joomla\Plugin\Task\Checkfiles\Extension\Checkfiles;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Requests plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Requests
 *
 * @testdox     The Checkfiles plugin
 *
 * @since       __DEPLOY_VERSION__
 */
class RequestsPluginTest extends UnitTestCase
{
	/**
	 * Setup
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setUp(): void
	{
		if (!is_dir(__DIR__ . '/tmp'))
		{
			mkdir(__DIR__ . '/tmp');
		}

		$image = imagecreate(200, 200);
		imagecolorallocate($image, 255, 255, 0);
		imagepng($image, __DIR__ . '/tmp/test.png');
		imagedestroy($image);
	}

	/**
	 * Cleanup
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function tearDown(): void
	{
		if (is_dir(__DIR__ . '/tmp'))
		{
			Folder::delete(__DIR__ . '/tmp');
		}
	}

	/**
	 * @testdox  can resize an image
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testResize()
	{
		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Checkfiles(new Dispatcher, [], __DIR__);
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'checkfiles.imagesize']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['path' => '/tmp', 'dimension' => 'width', 'limit' => 20, 'numImages' => 1]
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);

		list($width, $height) = getimagesize(__DIR__ . '/tmp/test.png');
		$this->assertEquals(20, $width);
		$this->assertEquals(20, $height);
	}

	/**
	 * @testdox  can resize a subset of images
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testResizeWithLimit()
	{
		copy(__DIR__ . '/tmp/test.png',__DIR__ . '/tmp/test1.png');

		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Checkfiles(new Dispatcher, [], __DIR__);
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'checkfiles.imagesize']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['path' => '/tmp', 'dimension' => 'width', 'limit' => 20, 'numImages' => 1]
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);

		list($width, $height) = getimagesize(__DIR__ . '/tmp/test.png');
		$this->assertEquals(20, $width);
		$this->assertEquals(20, $height);

		list($width, $height) = getimagesize(__DIR__ . '/tmp/test1.png');
		$this->assertEquals(200, $width);
		$this->assertEquals(200, $height);
	}

	/**
	 * @testdox  can resize an image
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testIgnoreResize()
	{
		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Checkfiles(new Dispatcher, [], __DIR__);
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'checkfiles.imagesize']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['path' => '/tmp', 'dimension' => 'width', 'limit' => 2000, 'numImages' => 1]
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);

		list($width, $height) = getimagesize(__DIR__ . '/tmp/test.png');
		$this->assertEquals(200, $width);
		$this->assertEquals(200, $height);
	}

	/**
	 * @testdox  can not run when invalid folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInvalidFolder()
	{
		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Checkfiles(new Dispatcher, [], __DIR__);
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'checkfiles.imagesize']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['path' => '/invalid', 'dimension' => 'width', 'limit' => 20, 'numImages' => 1]
			]
		);
		$plugin->standardRoutineHandler($event);

		list($width, $height) = getimagesize(__DIR__ . '/tmp/test.png');
		$this->assertEquals(Status::NO_RUN, $event->getResultSnapshot()['status']);
		$this->assertEquals(200, $width);
		$this->assertEquals(200, $height);
	}
}
