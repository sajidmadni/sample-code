<?php
use Navio\HospitalBundle\Entity\Physician;
use Navio\HospitalBundle\Tests\Controller\TestConfig;
/**
 * User: nitinpanuganti
 * Date: 05/11/17
 */

class PhysicianRepoTest extends TestConfig {
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */

    protected static $db;
    
    public static function setUpBeforeClass():void {        
        if  (strpos(DB_DSN, "pu_test") !== FALSE){                        
            $fixture_sql_file = __DIR__ . '/../_files/'."Fixture".".sql";            
            if (file_exists($fixture_sql_file)){            
                self::$db=parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        }
        else{
            echo 'Cannot run phpunit tests on non test/ dev databases',PHP_EOL;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    protected function setUp():void
    {
        $this->login($this,"hospitaladmin", "password");
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        if (!$this->em->isOpen()) {
        $this->em = $this->em->create($this->em->getConnection(),$this->em->getConfiguration());
        }
    }

    public function testFindOneByEmail(){
        $hospital_id = 27;
        $hosp_repo = $this->em->getRepository('HospitalBundle:Hospital');
        $hospital = $hosp_repo->findOneBy(array('id'=>$hospital_id));

        $physician = $this->em->getRepository('HospitalBundle:Physician')->findOneByEmailInHospital("hospitaladmin", $hospital);
        $this->assertTrue($physician instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician->getFirstName());
        $this->assertEquals("Manager",$physician->getLastName());
        $physician1 = $this->em->getRepository('HospitalBundle:Physician')->findOneByEmailInHospital("hospitalAdmin", $hospital);
        $this->assertTrue($physician1 instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician1->getFirstName());
        $this->assertEquals("Manager",$physician1->getLastName());
        $physician2 = $this->em->getRepository('HospitalBundle:Physician')->findOneByEmailInHospital("HOSPITALADMIN", $hospital);
        $this->assertTrue($physician2 instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician2->getFirstName());
        $this->assertEquals("Manager",$physician2->getLastName());
        $physician3 = $this->em->getRepository('HospitalBundle:Physician')->findOneByEmailInHospital("hospi", $hospital);
        $this->assertNull($physician3);
        
    }

    public function testFindOneByDocNumber(){
        $hospital_id = 27;
        $hosp_repo = $this->em->getRepository('HospitalBundle:Hospital');
        $hospital = $hosp_repo->findOneBy(array('id'=>$hospital_id));

        $physician = $this->em->getRepository('HospitalBundle:Physician')->findOneByDocNumberInHospital("phyadm1234", $hospital);
        $this->assertTrue($physician instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician->getFirstName());
        $this->assertEquals("Manager",$physician->getLastName());
        $physician1 = $this->em->getRepository('HospitalBundle:Physician')->findOneByDocNumberInHospital("phyAdm1234", $hospital);
        $this->assertTrue($physician instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician1->getFirstName());
        $this->assertEquals("Manager",$physician1->getLastName());
        $physician2 = $this->em->getRepository('HospitalBundle:Physician')->findOneByDocNumberInHospital("PHYADM1234", $hospital);
        $this->assertTrue($physician instanceof Physician);
        $this->assertEquals("HospitalAdmin",$physician2->getFirstName());
        $this->assertEquals("Manager",$physician2->getLastName());
        $physician3 = $this->em->getRepository('HospitalBundle:Physician')->findOneByDocNumberInHospital("PHDM1234", $hospital);
        $this->assertNull($physician3);
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
