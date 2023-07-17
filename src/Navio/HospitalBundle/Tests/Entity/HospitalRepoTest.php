<?php

use Navio\HospitalBundle\Tests\Controller\TestConfig;

class HospitalRepoTest extends TestConfig
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */

    protected static $db;
    private $_testManager;

    public static function setUpBeforeClass(): void
    {
        if (strpos(DB_DSN, "pu_test") !== FALSE) {
            $fixture_sql_file = __DIR__ . '/../_files/' . "Fixture-Handoff.sql";
            if (file_exists($fixture_sql_file)) {
                self::$db = parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        } else {
            echo 'Cannot run phpunit tests on non test/ dev databases', PHP_EOL;
        }
    }

    protected function setUp(): void
    {
        $this->setUpContainer();
    }

    

    public function testfindAllStaffCountsForUser(){
        $hospitalRepo = $this->em->getRepository('HospitalBundle:Hospital');
        $physicianRepo = $this->em->getRepository('HospitalBundle:Physician');
        $phys = $physicianRepo->findPhy(32);//AgencyAdmin
        $client =  $this->login($this,'hospitaladmin', 'password');

        $user = $this->getInfoFromDb('hospitaladmin','User','username', 'HospitalBundle', array(), true)[0];
        $this->dosql('UPDATE physician SET dndintervalstart="0000", dndintervalend="2359", presence="dnd" where id=32');
        $staff = $hospitalRepo->findAllStaffCountsForUser($user,$client->getContainer()->get('security.authorization_checker'));
        $this->assertEquals($staff[0]['id'], 32);
        $this->assertEquals($staff[0]['presence'],'dnd');

        $this->dosql('UPDATE physician SET dndintervalstart="0000", dndintervalend="2359", presence="" where id=32');
        $staff = $hospitalRepo->findAllStaffCountsForUser($user, $client->getContainer()->get('security.authorization_checker'));
        $this->assertEquals($staff[0]['id'], 32);
        $this->assertEquals($staff[0]['presence'], 'dnd');

        $this->dosql('UPDATE physician SET dndintervalstart="0000", dndintervalend="0000", presence="active" where id=32');
        $staff = $hospitalRepo->findAllStaffCountsForUser($user, $client->getContainer()->get('security.authorization_checker'));
        $this->assertEquals($staff[0]['id'], 32);
        $this->assertEquals($staff[0]['presence'], 'active');

        $this->dosql('UPDATE physician SET dndintervalstart="0000", dndintervalend="2359", presence="active" where id=32');
        $staff = $hospitalRepo->findAllStaffCountsForUser($user, $client->getContainer()->get('security.authorization_checker'));
        $this->assertEquals($staff[0]['id'], 32);
        $this->assertEquals($staff[0]['presence'], 'active');

        $this->dosql('UPDATE physician SET dndintervalstart="0000", dndintervalend="2359", presence="available" where id=32');
        $staff = $hospitalRepo->findAllStaffCountsForUser($user, $client->getContainer()->get('security.authorization_checker'));
        $this->assertEquals($staff[0]['id'],32);
        $this->assertEquals($staff[0]['presence'], 'active');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    public static function tearDownAfterClass(): void
    {
        self::$db = null;
    }
}
