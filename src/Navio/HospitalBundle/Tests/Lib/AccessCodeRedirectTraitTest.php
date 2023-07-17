<?php
/**
 * User: Nanda Yemparala
 * Date: 2019-06-18
 * Time: 04:22
 */

namespace Navio\HospitalBundle\Tests\Lib;

use Navio\HospitalBundle\Tests\Controller\TestConfig;
use Navio\SAMLBundle\Security\Core\SAML;
use Navio\Utils\AccessCodeRedirectTrait;

class AccessCodeRedirectTraitTest extends TestConfig
{
    protected static $db;

    use AccessCodeRedirectTrait;

    public static function setUpBeforeClass():void {
        if  (strpos(DB_DSN, "pu_test") !== FALSE){
            $fixture_sql_file = __DIR__ . '/../_files/'."Fixture.sql";
            if (file_exists($fixture_sql_file)){
                self::$db=parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        }
        else{
            echo 'Cannot run phpunit tests on non test/ dev databases',PHP_EOL;
        }
    }

    protected function setUp():void
    {
        if($this->_container == null)
        {
            $this->setUpContainer();
        }
    }


    public function testWithNoRedirectScheme()
    {
        $session = $this->get('session');
        $this->assertNotNull($session, 'session is not null');
        $ac = 'bvc'.__LINE__;
        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals('uniphyhealth://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');

        $session->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION, '');
        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals('uniphyhealth://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');

        $session->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION, '  ');
        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals('uniphyhealth://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');
    }

    public function testWithRedirectSchemeInSession()
    {
        $session = $this->get('session');
        $this->assertNotNull($session, 'session is not null');
        $scheme = 'asdf';
        $ac = 'bvc'.__LINE__;
        $session->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION, $scheme);

        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals($scheme.'://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');

        $scheme = ' mnbv ';
        $session->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION, $scheme);
        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals(trim($scheme).'://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');

        $scheme = ' one two ';
        $session->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION, $scheme);
        $redirectUri = $this->getRedirectUriWithAccessCode($this->_container, $ac);
        $this->assertNotNull($redirectUri, 'Expected redirect uri');
        $this->assertEquals(trim($scheme).'://auth-response?accessCode='.$ac, $redirectUri, 'expected scheme to match');
    }

}