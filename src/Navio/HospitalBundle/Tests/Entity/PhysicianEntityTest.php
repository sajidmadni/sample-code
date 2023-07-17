<?php

use Doctrine\ORM\EntityManager;
use Navio\HospitalBundle\Tests\Controller\TestConfig;

class PhysicianEntityTest extends TestConfig
{

    /**
     * @var EntityManager
     */

    protected static $db;

    public static function setUpBeforeClass(): void
    {
        if (strpos(DB_DSN, "pu_test") !== FALSE) {
            $fixture_sql_file = __DIR__ . '/../_files/' . "Fixture" . ".sql";
            if (file_exists($fixture_sql_file)) {
                self::$db = parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        } else {
            echo 'Cannot run phpunit tests on non test/ dev databases', PHP_EOL;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->login($this, "hospitaladmin", "password");
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        if (!$this->em->isOpen()) {
            $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
        }
    }

    public function testSetActive()
    {
        $accessCode1 = 'code'.__LINE__;
        $date=date('Y-d-m h:i:s');
        $this->doSql("INSERT INTO physician (hospital_id,first_name, last_name,created_at,updated_at,access_code) 
                                VALUES (27,'john','doe',now(),'$date','{$accessCode1}')");

        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $updatedAtInital = $phy1->getUpdatedAt();
        $this->assertNotNull($phy1);
        $this->assertFalse($phy1->getIsActive(), 'Physician status initially should be inactive');

        $phy1->setActive(true);
        $this->em->persist($phy1);
        $this->em->flush();

        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $this->assertTrue($phy1->getIsActive(), 'Physician status should be active');
        $this->assertGreaterThan($updatedAtInital, $phy1->getUpdatedAt(), 'Updated at should be current');

        // set active with skip update
        $updatedAtInital = $phy1->getUpdatedAt();
        $phy1->setActive(true, true);
        $this->em->persist($phy1);
        $this->em->flush();

        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $this->assertTrue($phy1->getIsActive(), 'Physician status should be active');
        $this->assertEquals($updatedAtInital, $phy1->getUpdatedAt(), 'Updated at should not be changed');

        // test setInactive
        $updatedAtInital = $phy1->getUpdatedAt();
        $phy1->setActive(false);
        $this->em->persist($phy1);
        $this->em->flush();

        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $this->assertFalse($phy1->getIsActive(), 'Physician active should be false');
        $this->assertGreaterThan($updatedAtInital, $phy1->getUpdatedAt(), 'Updated at should be current');

        // set active to false with skip update
        $updatedAtInital = $phy1->getUpdatedAt();
        $phy1->setActive(false, true);
        $this->em->persist($phy1);
        $this->em->flush();

        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $this->assertFalse($phy1->getIsActive(), 'Physician status should be false');
        $this->assertEquals($updatedAtInital, $phy1->getUpdatedAt(), 'Updated at should not be changed');


        // skip updates for same status
        $statusBefore = $phy1->getIsActive();
        $updatedAtInital = $phy1->getUpdatedAt();
        $phy1->setActive($statusBefore);
        $this->em->persist($phy1);
        $this->em->flush();

        $phy1 = $phyRepo->findOneBy(array('accessCode'=>$accessCode1));
        $this->assertEquals($statusBefore, $phy1->getIsActive(), 'Physician status should be same');
        $this->assertEquals($updatedAtInital, $phy1->getUpdatedAt(), 'Updated at should not be changed');
    }

}