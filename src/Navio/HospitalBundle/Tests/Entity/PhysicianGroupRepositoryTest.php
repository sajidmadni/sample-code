<?php
use Navio\HospitalBundle\Tests\Controller\TestConfig;

/**
 * User: Nanda Yemparala
 * Date: 06/14/19
 */

class PhysicianGroupRepositoryTest extends TestConfig {
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */

    protected static $db;
    
    public static function setUpBeforeClass():void {        
        if  (strpos(DB_DSN, "pu_test") !== FALSE){                        
            $fixture_sql_file = __DIR__ . '/../_files/'."Fixture-Handoff.sql";
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
        $this->setUpContainer();
    }

    public function testGetPhysicianSecGroups(){
        $hospital_id = 27;
        $em = $this->_container->get('doctrine')->getManager();
        $hosp_repo = $em->getRepository('HospitalBundle:Hospital');
        $phyRepo = $em->getRepository("HospitalBundle:Physician");
        $hospital = $hosp_repo->findOneBy(array('id'=>$hospital_id));

        $physician = $em->getRepository('HospitalBundle:Physician')->find(32);
        $grpRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
        $secGroups = $grpRepo->getSecurityGroupsForPhysician($physician);
        $this->assertCount(1, $secGroups, 'One sec group expected');

        $physician = $em->getRepository('HospitalBundle:Physician')->find(34);
        $grpRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
        $secGroups = $grpRepo->getSecurityGroupsForPhysician($physician);
        $this->assertCount(2, $secGroups, 'two sec groups expected');

        $this->doSql("INSERT INTO physician (hospital_id,first_name, last_name,created_at,updated_at,access_code)
VALUES (27,'don','bosco1',now(),now(),'gfd345')");

        $newPhy=$phyRepo->findOneBy(['accessCode'=> 'gfd345']);
        $secGroups = $grpRepo->getSecurityGroupsForPhysician($newPhy);
        $this->assertCount(0, $secGroups, '0 sec groups expected');

        // add this new physician to admin group that is part of a security group
        $this->doSql("insert into physician_physician_group (physician_group_id, physician_id, created_at, updated_at) values (11, {$newPhy->getId()}, now(), now());");
        $secGroups = $grpRepo->getSecurityGroupsForPhysician($newPhy);
        $this->assertCount(1, $secGroups, '1 sec group expected');
    }

    public function testSecGroupDirectlyAssignedToPhysicians()
    {
        $ac = 'testAc'.__LINE__;
        $hospital_id = 27;
        $em = $this->_container->get('doctrine')->getManager();
        $phyRepo = $em->getRepository("HospitalBundle:Physician");
        $grpRepo = $em->getRepository('HospitalBundle:PhysicianGroup');

        $this->doSql("INSERT INTO physician (hospital_id,first_name, last_name,created_at,updated_at,access_code)
VALUES ('$hospital_id','sadf','bvxds',now(),now(),'$ac')");

        $physician = $phyRepo->findOneBy(['accessCode' => $ac]);
        $this->assertCount(0, $grpRepo->getSecurityGroupsForPhysician($physician), 'No sec group expected');

        $secGrpName = 'sec_grp_'.__LINE__;
        $this->doSql("INSERT INTO physician_group (hospital_id, name, group_type, created_at, updated_at) VALUES ('$hospital_id', '$secGrpName', 'security_group', now(), now());");
        $secGrp = $grpRepo->findOneBy(['name'=>$secGrpName]);
        $this->assertNotNull($secGrp, 'secGroup should have been created');
        $this->doSql("INSERT INTO physician_physician_group (physician_id, physician_group_id, created_at, updated_at) values ('{$physician->getId()}','{$secGrp->getId()}', now(), now())");
        $this->assertCount(1, $grpRepo->getSecurityGroupsForPhysician($physician), '1 sec group expected');


        // add the user to the admin group and test for duplicate sec group assignments
        $adminGrpName = 'admin_grp_test'.__LINE__;
        $this->doSql("INSERT INTO physician_group (hospital_id, name, group_type, created_at, updated_at) VALUES ('$hospital_id', '$adminGrpName', 'admin_group', now(), now());");
        // add phy to admin group
        $this->doSql("INSERT INTO physician_physician_group (physician_group_id,created_at,updated_at,physician_id) values ('{$secGrp->getId()}', now(), now(), '{$physician->getId()}');");
        $adminGrp = $grpRepo->findOneBy(['name'=>$adminGrpName]);
        $this->assertNotNull($adminGrp, 'new admin group should be created');
        // add admin group to secGrp
        $this->doSql("INSERT INTO physician_physician_group (physician_group_id,created_at,updated_at,member_physician_group_id) values ('{$secGrp->getId()}', now(), now(), '{$adminGrp->getId()}');");
        $this->assertCount(1, $grpRepo->getSecurityGroupsForPhysician($physician), '1 sec group expected');
    }


    /**
     * {@inheritDoc}
     */
    public function tearDown():void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
    
    public static function tearDownAfterClass():void
    {
        self::$db = null;
    }

}