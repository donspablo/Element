<?php
ob_start();
define('PHPUNIT_TESTING', true);

use PHPUnit\Framework\TestCase;

require '../index.php';

final class ElementTest extends TestCase
{
    private const DB_PATH = __DIR__ . '/../data_test/database_test.js';
    private const PASSWORD = 'testPass';

    /** @var Element */
    private $element;

    public function testGetDb(): void
    {
        if (file_exists(self::DB_PATH)) {
            unlink(self::DB_PATH);
        }

        $return = $this->element->getDb();

        $this->assertFileExists(self::DB_PATH);

        $this->assertTrue(property_exists($return, 'config'));
        $this->assertTrue(property_exists($return, 'sites'));
        $this->assertTrue(property_exists($return, 'widgets'));

        $this->assertSame(strlen($return->config->password), 60);
        $this->assertSame('loginURL', $return->config->login);

        $this->assertSame('Home', $return->sites->home->title);
        $this->assertTrue(property_exists($return->sites->home, 'keywords'));
        $this->assertTrue(property_exists($return->sites->home, 'description'));
        $this->assertTrue(property_exists($return->sites->home, 'content'));
    }

    /**
     * @depends testGetDb
     */
    public function testLoginAction(): void
    {
        $this->assertFalse($this->element->loggedIn);

        // Setup
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->element->currentSite = 'loginURL';

        // Test Wrong password
        $_POST['password'] = 'wrongPass';

        $this->element->loginAction();
        $this->assertFalse(isset($_SESSION['loggedIn']));
        $this->assertFalse(isset($_SESSION['rootDir']));
        $this->assertEquals($_SESSION['alert']['danger'][0]['message'], 'Wrong password.');

        // Test right password
        $hashPass = password_hash(self::PASSWORD, PASSWORD_DEFAULT);
        $this->element->set('config', 'password', $hashPass);
        $password = $this->element->get('config', 'password');

        $this->assertSame($hashPass, $password);

        $_POST['password'] = self::PASSWORD;

        $this->element->loginAction();
        $this->element->loginStatus();

        $this->assertTrue($_SESSION['loggedIn']);
        $this->assertEquals($_SESSION['rootDir'], $this->element->rootDir);
        $this->assertTrue($this->element->loggedIn);
    }

    /**
     * @depends testLoginAction
     */
    public function testLogoutAction(): void
    {
        $_REQUEST['token'] = $this->element->getToken();
        $this->element->currentSite = 'logout';

        $this->element->logoutAction();
        $this->element->loginStatus();

        $this->assertFalse(isset($_SESSION['loggedIn']));
        $this->assertFalse(isset($_SESSION['rootDir']));
        $this->assertFalse(isset($_SESSION['token']));
        $this->assertFalse($this->element->loggedIn);
    }

    public function testChangePasswordAction(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->element->currentSite = 'loginURL';

        $hashPass = password_hash(self::PASSWORD, PASSWORD_DEFAULT);
        $this->element->set('config', 'password', $hashPass);
        $_POST['password'] = self::PASSWORD;

        $this->element->loginAction();

        $_POST['token'] = $this->element->getToken();
        $_POST['old_password'] = self::PASSWORD;
        $_POST['new_password'] = 'test';

        $this->element->loginStatus();
        $this->element->changePasswordAction();

        $this->assertEquals($_SESSION['alert']['success'][0]['message'], 'Password changed.');
    }

    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = 'element.doc';
        $_SERVER['SERVER_PORT'] = '80';

        $this->element = $this->getMockBuilder(Element::class)
            ->setMethods(['redirect'])
            ->getMock();

        $this->element->setPaths('data_test', 'files_test', 'database_test.js');
    }
}
