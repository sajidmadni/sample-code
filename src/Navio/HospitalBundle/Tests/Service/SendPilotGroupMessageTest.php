<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Created by PhpStorm.
 * User: Nanda Yemparala
 * Date: 3/19/18
 * Time: 11:55 AM
 */
namespace Navio\HospitalBundle\Tests\Service;

use Navio\HospitalBundle\Service\MessageLib;
use Navio\HospitalBundle\Tests\Controller\TestConfig;
use Navio\MessageBundle\Entity\SecureFile;

class SendPilotGroupMessageTest extends TestConfig {
    protected static $db;

    public static function setUpBeforeClass():void {
        if  (strpos(DB_DSN, "pu_test") !== FALSE){
            $fixture_sql_file = __DIR__ . '/../../../HospitalBundle/Tests/_files/Fixture.sql';
            if (file_exists($fixture_sql_file)){
                self::$db=parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        }
        else{
            echo 'Cannot run phpunit tests on non test/ dev databases',PHP_EOL;
        }
    }
    
    public function setUp():void {
        parent::setUpContainer();
    }


    public function testSendGroupPM()
    {
        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $hospitalId = 27;
        $exploderUserId = 78;
        $sender = $phyRepo->findOneById(90);

        $nameOfTheMuc = __FUNCTION__.':'.__LINE__;
        $group = $this->createMUC($hospitalId, $nameOfTheMuc, [90, 31,32]);

        $pilotPm = $msgLib->sendPilotMessageToMuc($group, $sender, "test msg text");
        $this->assertGreaterThan(0, $pilotPm->getId());
        $this->assertEquals($exploderUserId, $pilotPm->getReceiverPhysician(), 'pilot pm receiver phy should be exploder user');
        $this->assertEquals($sender->getId(), $pilotPm->getSenderPhysician());
        $this->assertTrue($pilotPm->getId() == $pilotPm->getMessageGroup(), 'pm_group_id of pilot should be equal to id');

    }

    public function testSendWIthImage()
    {
        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $hospitalId = 27;
        $exploderUserId = 78;
        $senderId = 90;
        $sender = $phyRepo->findOneById($senderId);
        $nameOfTheMuc = __FUNCTION__.':'.__LINE__;
        $group = $this->createMUC($hospitalId, $nameOfTheMuc, [$senderId, 31,32,33,34]);

        $secureFile = new SecureFile();
        $secureFile->setSenderPhysician($senderId);
        $secureFile->setName('test'.__LINE__);
        $this->em->persist($secureFile);
        $this->em->flush();

        $pilotPm = $msgLib->sendPilotMessageToMuc($group, $sender, "test msg text", ['image' => $secureFile]);
        $this->assertGreaterThan(0, $pilotPm->getId());
        $this->assertEquals($exploderUserId, $pilotPm->getReceiverPhysician(), 'pilot pm receiver phy should be exploder user');
        $this->assertEquals($sender->getId(), $pilotPm->getSenderPhysician());
        $this->assertTrue($pilotPm->getId() == $pilotPm->getMessageGroup(), 'pm_group_id of pilot should be equal to id');
        $this->assertNotNull($pilotPm->getImage(), 'Expected image of the file uploaded');
        $this->assertEquals($secureFile->getId(), $pilotPm->getImage()->getId(), 'Expected image id of the file uploaded');
    }

    function testNotMemberOfMuc()
    {
        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $hospitalId = 27;
        $sender = $phyRepo->findOneById(90);

        // testing muc with phy who is not a member
        $nameOfTheMuc = __FUNCTION__.':'.__LINE__;
        $group = $this->createMUC($hospitalId, $nameOfTheMuc, [31,32,34]);

        $this->expectExceptionMessage("Not a member");
        $msgLib->sendPilotMessageToMuc($group, $sender,"test msg");
    }

    public function testEmptyMuc()
    {
        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $hospitalId = 27;
        $senderId = 90;
        $sender = $phyRepo->findOneById($senderId);
        $nameOfTheMuc = __FUNCTION__.':'.__LINE__;
        // create empty muc
        $group = $this->createMUC($hospitalId, $nameOfTheMuc, [$senderId]);

        $this->expectExceptionMessage("No one else in the group");
        $pm = $msgLib->sendPilotMessageToMuc($group, $sender,"test msg");
    }

    public function testSendToSavedGroup()
    {
        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $hospitalId = 27;
        $senderId = 90;
        $sender = $phyRepo->findOneById($senderId);
        $nameOfTheMuc = __FUNCTION__.':'.__LINE__;
        $exploderUserId = 78;

        //        31,32,33,34,35,36,41,46,47,50,54,55,56,57,58,60,68,71,72,73,75,76,77,78,85,86,90

        // create empty muc
        $group = $this->createMUC($hospitalId, $nameOfTheMuc, [$senderId, 31,32,33,34]);
        $group->setPhysician($sender);
        $this->em->persist($group);
        $this->em->flush();

        $pilotPm= $msgLib->sendPilotMessageToMuc($group, $sender,"test msg");
        $this->assertGreaterThan(0, $pilotPm->getId());
        $this->assertEquals($exploderUserId, $pilotPm->getReceiverPhysician(), 'pilot pm receiver phy should be exploder user');
        $this->assertEquals($sender->getId(), $pilotPm->getSenderPhysician());
        $this->assertTrue($pilotPm->getId() == $pilotPm->getMessageGroup(), 'pm_group_id of pilot should be equal to id');
    }



    private function createMUC($hospital_id, $name, $memberids)
    {
        $this->doSql("INSERT INTO physician_group (hospital_id,name,created_at,updated_at) values ($hospital_id, '$name', now(), now())");
        $group = $this->em->getRepository('HospitalBundle:PhysicianGroup')->findOneBy(['name' => $name]);

        foreach ($memberids as $member)
        {
            $this->doSql("INSERT INTO physician_physician_group (physician_id, physician_group_id, created_at,updated_at) values ($member, {$group->getId()}, now(), now())");
        }

        return $group;
    }
}
