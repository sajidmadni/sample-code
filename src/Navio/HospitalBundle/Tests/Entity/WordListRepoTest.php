<?php
namespace Navio\HospitalBundle\Tests\Entity;

use Navio\HospitalBundle\Tests\Controller\TestConfig;
use Navio\Utils\FilterBuilder;

class WordListRepoTest extends TestConfig {

    /**
     * @var \Doctrine\ORM\EntityManager
     */

    protected static $db;

    public static function setUpBeforeClass() : void
    {
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

    public function setUp():void {
        $this->setUpContainer();
        $this->repo = $this->_container->get('doctrine')->getManager()->getRepository('HospitalBundle:UpdateMessage');
    }

    public function testGroupsFilter(){
        $hospital_id = 27;
        $this->em = $this->_container->get('doctrine')->getManager();
        $hosp_repo = $this->em->getRepository('HospitalBundle:Hospital');
        $hospital = $hosp_repo->findOneBy(array('id'=>$hospital_id));
        $wordListRepo = $this->em->getRepository('HospitalBundle:WordList');

        $filter = new FilterBuilder($this->getContainer()->get('doctrine'),  $hospital);
        $filter->setGroups([11]);
        $results = $wordListRepo->getFilterResults($filter);
        self::assertCount(4, $results, 'Expected 4 physicians in the result');

        // multiple groups
        $filter = new FilterBuilder($this->getContainer()->get('doctrine'),  $hospital);
        $filter->setGroups([11,16]);
        $results = $wordListRepo->getFilterResults($filter);
        self::assertCount(6, $results, 'Expected 6 physicians in the result');

        // testing for duplicate physicians
        $this->doSql("INSERT INTO physician_physician_group (physician_id, physician_group_id,created_at, updated_at) VALUES (32,12, now(), now());");
        $filter->setGroups([11,12]);
        $results = $wordListRepo->getFilterResults($filter);
        self::assertCount(6, $results, 'Expected 6 physicians in the result');
    }

    public function testGroupsOnlyFilter()
    {
        $hospital_id = 27;
        $this->em = $this->_container->get('doctrine')->getManager();
        $hosp_repo = $this->em->getRepository('HospitalBundle:Hospital');
        $hospital = $hosp_repo->findOneBy(array('id'=>$hospital_id));
        $wordListRepo = $this->em->getRepository('HospitalBundle:WordList');

        $filter = new FilterBuilder($this->getContainer()->get('doctrine'),  $hospital);
        $filter->setGroupsOnly(true);
        $results = $wordListRepo->getFilterResults($filter);
        self::assertCount(16, $results, 'Expected 16 physicians in the result');
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
