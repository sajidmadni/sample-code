<?php

namespace Navio\EngagementBundle\Tests\Lib;


use Navio\EngagementBundle\Entity\Content;
use Navio\EngagementBundle\Tests\PrU\baseWebDB;
use Navio\HospitalBundle\Entity\Physician;
use Navio\HospitalBundle\Entity\PhysicianGroup;
use Navio\HospitalBundle\Service\MessageLib;

class ExploderControllerTest extends baseWebDB
{
    private static $CODE_U1H1 = "VMA5DB9Z";
    private static $CODE_U2H2 = "5U7CPWYT";
    private static $CODE_U3H2 = "W5N87YR2"; //user #3, hospital #2
    
    protected function useDevice1($code= "W5N87YR2"){
        return $this->useDevice(
           "445e6e664f6276c2488dfe2ee4ae6349d15f89a9",
           $code);
    }
    
    //Load DB before each test.
    public function setUp():void {
        parent::setUp();     
        $fixtures = array(__DIR__ . '/../../../EngagementBundle/Tests/_files/v2phy.sql');
        $this->loadFixtures($fixtures);        
    }
    
    /*
     * test non existent message.
     */
    public function testExploderBadMessage()
    {
        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);

        $j = array(
            'event' => 'PM.3',
           'message_id'=>'0',
           'receiver_id'=>'0',
        );
        $j=  json_decode(json_encode($j));  //EMULATE WHAT EVENT SYSTEM WILL DO

        $rv = $msgLib->doExplode($j);

        $this->assertEquals(-1,$rv,"Should reject this explosion");

    }

    public function testMsgBlastWODocs(){

        $msgLib = new MessageLib($this->em, $this->_container);
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $all = $pmRepo->findAll();
        $countAtStart = count($all);

        $j1 = array(
            'msg_blast'=>1,
            'ceo_connect'=>0,
            'physician'=>2,
            'hid'=>"2",
            "txt"=>"This is your hospital admin",
            "to_dept"=>"ABCD",
            "to_degree"=>"",
            "to_group"=>"",
            "secure_file"=>""
        );

        $j=  json_decode(json_encode($j1));
        $rv = $msgLib->doExplode($j);
        $this->assertEquals(-1,$rv,"Should reject this explosion");

        $all2 = $pmRepo->findAll();
        $this->assertEquals($countAtStart, count($all2), 'No PMs should have been sent');
    }

    public function testCeoMsgBlastToAll(){

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $sender = $phyRepo->find(2);

        // Set sender as ceo connect admin
        $hospital = $sender->getHospital();
        $hospital->setCeoConnectAdministratorId(2);
        $this->em->persist($hospital);
        $this->em->flush();

        $filter = array(
            'hospital'  =>  $sender->getHospital(),
            'deletedAt' => NULL,
            'contactType'=>Physician::$CONTACT_REGULAR,
            'isActive'=>1
        );
        $phyCount = count($phyRepo->findBy($filter)) - 1; // all other phys in the hospital
        $all = $pmRepo->findAll();
        $countAtStart = count($all);

        $j1 = array(
            'msg_blast'=>1,
            'ceo_connect'=>1,
            'physician'=>2,
            'hid'=>"2",
            "txt"=>"This is your ceo admin",
            "to_dept"=>"",
            "to_degree"=>"",
            "to_group"=> "",
            "secure_file" => ""
        );

        $j=  json_decode(json_encode($j1));
        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all2 = $pmRepo->findAll();
        $this->assertEquals($countAtStart + $phyCount,count($all2), 'expected number of PMs not broadcasted');
    }

    public function testCeoMsgBlastToAGroupWODocs(){

        $msgLib = new MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $sender = $phyRepo->find(2);

        // Set sender as ceo connect admin
        $hospital = $sender->getHospital();
        $hospital->setCeoConnectAdministratorId(2);
        $this->em->persist($hospital);
        $this->em->flush();

        $all = $pmRepo->findAll();
        $countAtStart = count($all);

        $j1 = array(
            'msg_blast'=>1,
            'ceo_connect'=>1,
            'physician'=>2,
            'hid'=>"2",
            "txt"=>"This is your ceo admin",
            "to_dept"=>"",
            "to_degree"=>"",
            "to_group"=> "5",
            "secure_file" => ""
        );

        $j=  json_decode(json_encode($j1));
        $rv = $msgLib->doExplode($j);
        $this->assertEquals(-1,$rv,"Should accept this explosion");

        $all2 = $pmRepo->findAll();
        $this->assertEquals($countAtStart ,count($all2), 'expected number of PMs not broadcasted');
    }
    /*
     * test message group .
     */
    public function testExploderAgencyWODocs()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("No User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Kildare');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, "A test Message Group", null, false);

        $this->assertGreaterThan(0,$pmId," -- pilot message id must be > 0");

        $all = $pmRepo->findAll();
        $countAtStart = count($all);


        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pmId,
           'receiver_id'=>$pgroup->getPhysician()->getId(),
        );

        $j=  json_decode(json_encode($j1));

        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all2 = $pmRepo->findAll();
        $countAtEnd = count($all2);
        $this->assertEquals($countAtStart,$countAtEnd,"Should have same number of messages when done");

    }

    /*
     * test message group .
     */
    public function testExploderAgencyW1DocSameAsSender()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("One User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Kildare');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, "A test Message Group", null, false);

        $this->assertGreaterThan(0,$pmId," -- pilot message id must be > 0");

        $all1 = $pmRepo->findAll();
        $countAtStart = count($all1);


        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pmId,
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));

        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all = $pmRepo->findAll();
        $countAtEnd = count($all);

        $this->assertEquals($countAtStart, $countAtEnd,"Should have same number of messages when done");
    }


    /*
     * test message group .
     */
    public function testExploderAgencyW1Doc()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("One User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Welby');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "A test Group Message ".time();
        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, $text, null, false);

        $this->assertGreaterThan(0,$pmId," -- pilot message id must be > 0");


        $all1 = $pmRepo->findAll();
        $countAtStart = count($all1);

        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pmId,
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));

        //s.
        $pilotPM = $pmRepo->findOneByText($text);
        $this->assertNotNull($pilotPM,"Should have one pilot PM");
        $rv = $msgLib->doExplode($j);


        $this->assertEquals(null,$rv,"Should accept this explosion");


        $all = $pmRepo->findAll();
        $countAtEnd = count($all);
        $this->assertEquals($countAtStart+1,$countAtEnd,"Should have one more number of messages when done");


        //has the recipient been tickled?

        //examine contents.
        $matching = $pmRepo->findByText($text);
        $this->assertEquals(2,count($matching),"Should have 2 messages in this test");

        foreach($matching as $one){
            if($pgPhy->getId() != $one->getReceiverPhysician()){
                $this->assertEquals($one->getSenderUser(), $sendingPhy->getUser()->getId(), "Sender expected to be the same");
                $this->assertEquals($one->getSenderPhysician(), $sendingPhy->getId(), "Sender Phy expected to be the same");
                $this->assertEquals(0, $one->getReceiptConfirmationType(), "");
//                $this->assertEquals($pilotPM->getReceiptConfirmedAt(),$one->getReceiptConfirmedAt(), "");
                $this->assertEquals($pilotPM->getIsCeoConnect(),$one->getIsCeoConnect(), " getIsCeoConnect");
                $this->assertEquals($pilotPM->getDeletedByReceiver(),$one->getDeletedByReceiver(), " getDeletedByReceiver");
//                $this->assertEquals($pilotPM->getDeletedBySender(),$one->getDeletedBySender(), " getDeletedBySender");
                $this->assertEquals($pilotPM->getPatient(),$one->getPatient(), " getPatient");
                $this->assertEquals($pilotPM->getImage(),$one->getImage(), " getImage");

            }
        }
    }
//
//    /*
//     * test message group .
//     */
    public function testExploderAgencyWNDocs()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $ppgRepo = $this->em->getRepository('HospitalBundle:PhysicianPhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');
        $pushRepo = $this->em->getRepository('MessageBundle:PushNotification');

        $pgroup = $pgRepo->findOneByName("Four User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Welby');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "A test Group Message ".time();
        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, $text, null, false);

        $this->assertGreaterThan(0,$pmId," -- pilot message id must be > 0");

        $countAtStart = count($pmRepo->findAll());

        $pushCountAtStart = count($pushRepo->findAll());


        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pmId,
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));
        $pilotPM = $pmRepo->findOneByText($text);
        $this->assertNotNull($pilotPM,"Should have one pilot PM");

        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $countAtEnd = count($pmRepo->findAll());
        $this->assertEquals(($countAtStart + 3), $countAtEnd,"Should have one more number of messages when done");

        $group_message_started_count = 2;


        $pushCountAtEnd = count($pushRepo->findAll());
        $this->assertEquals(($pushCountAtStart + 9), $pushCountAtEnd, "expecting 9 more entries for reminders");

        //examine contents.
        $matching = $pmRepo->findByText($text);
        $this->assertEquals(4,count($matching),"Should have 2 messages in this test");


        $muc = null;
        foreach($matching as $one){
            if($pgPhy->getId() != $one->getReceiverPhysician()){
                $this->assertEquals($one->getSenderUser(), $sendingPhy->getUser()->getId(), "Sender expected to be the same");
                $this->assertEquals($one->getSenderPhysician(), $sendingPhy->getId(), "Sender Phy expected to be the same");
                $this->assertEquals(0, $one->getReceiptConfirmationType(), "");

//                $this->assertEquals($pilotPM->getReceiptConfirmedAt(),$one->getReceiptConfirmedAt(), " getReceiptConfirmedAt");
                $this->assertEquals($pilotPM->getIsCeoConnect(),$one->getIsCeoConnect(), " getIsCeoConnect");
                $this->assertEquals($pilotPM->getDeletedByReceiver(),$one->getDeletedByReceiver(), " getDeletedByReceiver");
//                $this->assertEquals($pilotPM->getDeletedBySender(),$one->getDeletedBySender(), " getDeletedBySender");

                $this->assertEquals($pilotPM->getPatient(),$one->getPatient(), " getPatient");
                $this->assertEquals($pilotPM->getImage(),$one->getImage(), " getImage");


                //has the recipient been tickled?
                $phy= $phyRepo->find($one->getReceiverPhysician());
                $this->assertNotNull($phy, " receiver must exist");
                $this->assertNotNull($phy->getTickledAt(), " receiver must be tickled.");

                if(!$muc){
                    $muc = $one->getPhysicianGroup();
                }

                $this->assertNotNull($muc,"MUC must be set");
                $this->assertEquals($muc,$one->getPhysicianGroup(), " getPhysicianGroup must be MUC");
            }
        }
        // now check all members of muc
        $members = $ppgRepo->findByPhysicianGroup($muc);
        $this->assertEquals(4,count($members), " number of entries in MUC shold be four (add sender) ");


        // NOW add Zee group to  current Message

        $pgroup2 = $pgRepo->findOneByName("Zee");
        $this->assertNotNull($pgroup2," -- Zee PhysicianGroup not found");

        $pgPhy2 = $pgroup2->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy2," -- Zee Physician (of Group) not found");

        $pm2 = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy2, NULL, $text, null, false, false, "",false);
        $pm2->setPhysicianGroup($muc);
        $this->assertNotNull($pm2,"Should have one pilot PM");

        $j2 = array(
           'message_id'=>$pm2->getId(),
           'receiver_id'=>$pgroup2->getPhysician()->getId(),//
        );
        $j3=  json_decode(json_encode($j2));
        $rv2 = $msgLib->doExplode($j3);

        $this->assertEquals(null,$rv2,"Should accept this explosion");
        $matching2 = $pmRepo->findByText($text);
        $this->assertEquals(7,count($matching2),"Should have 2 messages in this test");
    }
//
//    /*
//     * test message group .
//     */
    public function testExploderBroadcastWODocs()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("No User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Kildare');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

//        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, "A test Message Group", null, false);
        $pm = $msgLib->sendMessage($sendingPhy->getUser(), $pgPhy->getId(), NULL, "A test Message Broadcast", null, $pgroup);

        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertTrue($pm->getIsBroadcasted()," -- pilot message id must getIsBroadcasted > 0");

        $all = $pmRepo->findAll();
        $countAtStart = count($all);

        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pm->getId(),
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));

        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all2 = $pmRepo->findAll();
        $countAtEnd = count($all2);
        $this->assertEquals($countAtStart,$countAtEnd,"Should have same number of messages when done");

    }
//
//    /*
//     * test message group .
//     */
    public function testExploderBroadcastW1DocSameAsSender()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("One User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Kildare');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

//        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, "A test Message Group", null, false);
        $pm = $msgLib->sendMessage($sendingPhy->getUser(), $pgPhy->getId(), NULL, "A test Message Broadcast", null, $pgroup);
//        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, "A test Message Group", null, false);

        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertTrue($pm->getIsBroadcasted()," -- pilot message id must getIsBroadcasted > 0");

        $all1 = $pmRepo->findAll();
        $countAtStart = count($all1);


        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pm->getId(),
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));

        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all = $pmRepo->findAll();
        $countAtEnd = count($all);
        $this->assertEquals($countAtStart,$countAtEnd,"Should have same number of messages when done");
    }

    /*
     * test message group .
     */
    public function testExploderBroadcastW1Doctor()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');

        $pgroup = $pgRepo->findOneByName("One User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Welby');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "A test Group Message ".time();
//        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, $text, null, false);
        $pm = $msgLib->sendMessage($sendingPhy->getUser(), $pgPhy->getId(), NULL, $text, null, $pgroup);
        $this->assertTrue($pm->getIsBroadcasted()," -- pilot message id must getIsBroadcasted > 0");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");

        $all1 = $pmRepo->findAll();
        $countAtStart = count($all1);

        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pm->getId(),
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));

        //s.
        $pilotPM = $pmRepo->findOneByText($text);
        $this->assertNotNull($pilotPM,"Should have one pilot PM");


        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $all = $pmRepo->findAll();
        $countAtEnd = count($all);
        $this->assertEquals($countAtStart+1,$countAtEnd,"Should have one more number of messages when done");

        //has the recipient been tickled?

        //examine contents.
        $matching = $pmRepo->findByText($text);
        $this->assertEquals(2,count($matching),"Should have 2 messages in this test");

        foreach($matching as $one){
            if($pgPhy->getId() != $one->getReceiverPhysician()){
                $this->assertEquals($one->getSenderUser(), $sendingPhy->getUser()->getId(), "Sender expected to be the same");
                $this->assertEquals($one->getSenderPhysician(), $sendingPhy->getId(), "Sender Phy expected to be the same");
                $this->assertEquals(0, $one->getReceiptConfirmationType(), "");
//                $this->assertEquals($pilotPM->getReceiptConfirmedAt(),$one->getReceiptConfirmedAt(), "");
                $this->assertEquals($pilotPM->getIsCeoConnect(),$one->getIsCeoConnect(), " getIsCeoConnect");
                $this->assertEquals($pilotPM->getDeletedByReceiver(),$one->getDeletedByReceiver(), " getDeletedByReceiver");
//                $this->assertEquals($pilotPM->getDeletedBySender(),$one->getDeletedBySender(), " getDeletedBySender");
                $this->assertEquals($pilotPM->getPatient(),$one->getPatient(), " getPatient");
                $this->assertEquals($pilotPM->getImage(),$one->getImage(), " getImage");

            }
        }
    }

    /*
     * test message group .
     */
    public function testExploderBroadcastWNDocs()
    {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $ppgRepo = $this->em->getRepository('HospitalBundle:PhysicianPhysicianGroup');
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');
        $pushRepo = $this->em->getRepository('MessageBundle:PushNotification');

        $pgroup = $pgRepo->findOneByName("Four User AG");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $pgPhy = $pgroup->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy," -- Physician (of Group) not found");

        $sendingPhy = $phyRepo->findOneByLastName('Welby');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "A test Broadcast Group Message ".time();
//        $pmId = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy, NULL, $text, null, false);
        $pm = $msgLib->sendMessage($sendingPhy->getUser(), $pgPhy->getId(), NULL, $text, null, $pgroup);
        $this->assertTrue($pm->getIsBroadcasted()," -- pilot message id must getIsBroadcasted > 0");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");

        $countAtStart = count($pmRepo->findAll());

        $pushCountAtStart = count($pushRepo->findAll());


        $j1 = array(
            'event' => 'PM.3',
           'message_id'=>$pm->getId(),
           'receiver_id'=>$pgPhy->getId(),
        );
        $j=  json_decode(json_encode($j1));
        $pilotPM = $pmRepo->findOneByText($text);
        $this->assertNotNull($pilotPM,"Should have one pilot PM");


        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $countAtEnd = count($pmRepo->findAll());
        $this->assertEquals($countAtStart+3,$countAtEnd,"Should have one more number of messages when done");

        $pushCountAtEnd = count($pushRepo->findAll());
        $push_started_count = 2;
        $this->assertEquals(($pushCountAtStart + 9), $pushCountAtEnd, "expecting 9 more entries for reminders");

        //examine contents.
        $matching = $pmRepo->findByText($text);
        $this->assertEquals(4,count($matching),"Should have 2 messages in this test");

        $muc = null;
        foreach($matching as $one){
            if($pgPhy->getId() != $one->getReceiverPhysician()){
                $this->assertEquals($one->getSenderUser(), $sendingPhy->getUser()->getId(), "Sender expected to be the same");
                $this->assertEquals($one->getSenderPhysician(), $sendingPhy->getId(), "Sender Phy expected to be the same");
                $this->assertEquals(0, $one->getReceiptConfirmationType(), "");
//                $this->assertEquals($pilotPM->getReceiptConfirmedAt(),$one->getReceiptConfirmedAt(), " getReceiptConfirmedAt");
                $this->assertEquals($pilotPM->getIsCeoConnect(),$one->getIsCeoConnect(), " getIsCeoConnect");
                $this->assertEquals($pilotPM->getDeletedByReceiver(),$one->getDeletedByReceiver(), " getDeletedByReceiver");
//                $this->assertEquals($pilotPM->getDeletedBySender(),$one->getDeletedBySender(), " getDeletedBySender");
                $this->assertEquals($pilotPM->getPatient(),$one->getPatient(), " getPatient");
                $this->assertEquals($pilotPM->getImage(),$one->getImage(), " getImage");

                //has the recipient been tickled?
                $phy= $phyRepo->find($one->getReceiverPhysician());
                $this->assertNotNull($phy, " receiver must exist");
                $this->assertNotNull($phy->getTickledAt(), " receiver must be tickled.");

                if(!$muc){
                    $muc = $one->getPhysicianGroup();
                }

                //Will there be a MUC for broadcast message?
//                $this->assertNotNull($muc,"MUC must be set");
//                $this->assertEquals($muc,$one->getPhysicianGroup(), " getPhysicianGroup must be MUC");
            }
        }

        // now check all members of muc
//        $members = $ppgRepo->findByPhysicianGroup($muc);
//        $this->assertEquals(4,count($members), " number of entries in MUC should be four (add sender) ");


        // NOW add Zee group to  current Message

        $pgroup2 = $pgRepo->findOneByName("Zee");
        $this->assertNotNull($pgroup2," -- Zee PhysicianGroup not found");

        $pgPhy2 = $pgroup2->getPhysician();   // find mapped contact
        $this->assertNotNull($pgPhy2," -- Zee Physician (of Group) not found");

        $pm2 = $msgLib->sendMessage2($sendingPhy->getUser(), $sendingPhy, $pgPhy2, NULL, $text, null, false, false, "",false);
        $pm2->setPhysicianGroup($muc);
        $this->assertNotNull($pm2,"Should have one pilot PM");

        $j2 = array(
           'message_id'=>$pm2->getId(),
           'receiver_id'=>$pgroup2->getPhysician()->getId(),//
        );
        $j3=  json_decode(json_encode($j2));
        $rv2 = $msgLib->doExplode($j3);

        $this->assertEquals(null,$rv2,"Should accept this explosion");
        $matching2 = $pmRepo->findByText($text);
        $this->assertEquals(8,count($matching2),"Should have 8 messages in this test");
    }

    /**
     * This phpunit test tests if the follow-unfollow functionality works for a given set of physicians
     */
    public function testFollowAndUnFollow()
    {
        $PHY_1 = 6;
        $PHY_2 = 26;
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $phys = $phyRepo->find($PHY_1);
        $to_follow = $phyRepo->find($PHY_2);

        if (($phys != null) && ($to_follow != null)){
            $pgRepo = $this->em->getRepository("HospitalBundle:PhysicianGroup");
            $action = $pgRepo->findAndThenFollowOrUnFollow($phys, $to_follow);
            if ($action == PhysicianGroup::$ACTION_FOLLOW)
            {
                $this->assertTrue(true, "Unable to follow");
            }
            $action = $pgRepo->findAndThenFollowOrUnFollow($phys, $to_follow);
            if ($action == PhysicianGroup::$ACTION_UNFOLLOW)
            {
                $this->assertTrue(true, "Unable to unfollow");
            }
        }else{
            $this->assertFalse(true, "Unable to lookup physicians by their ids");
        }

    }

    /**
     * This test validates creation of new engagement content
     */
    public function testNewContent()
    {
        $PHY_1 = 6;
        $PHY_2 = 26;
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $groupRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');

        $phys = $phyRepo->find($PHY_1);
        $to_follow = $phyRepo->find($PHY_2);

        if (($phys != null) && ($to_follow != null)){

            $action = $groupRepo->findAndThenFollowOrUnFollow($to_follow, $phys);
            if ($action == PhysicianGroup::$ACTION_FOLLOW)
            {
                $this->assertTrue(true, "Unable to follow");
            }
            $wall = $groupRepo->findUserWallBoard($phys);
            if ($wall == null)
            {
                $this->assertFalse(true, "Unable to find user wall, exiting");
                return;
            }
            $content_post = new Content();
            $content_post->setAuthor($phys);
            $content_post->setContentGroup($wall);
            $content_post->setTitle("This is automated test");
            $content_post->setTitle("This is automated test body");
            $content_post->setHospital($phys->getHospital());
            $this->em->persist($content_post);
            $this->em->flush();
            $this->assertTrue(($content_post->getId()>0), 'Unable to create content');

            //Now explode the content
            $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);

            $j = array(
                'post_content'=>'1',
                'content_id'=>$content_post->getId(),
                'hid'=>$phys->getHospital()->getId()
            );
            $j=  json_decode(json_encode($j));  //EMULATE WHAT EVENT SYSTEM WILL DO

            $msgLib->doExplode($j);
            $contentUserRepo = $this->em->getRepository('NavioEngagementBundle:ContentUser');
            $existing_content = $contentUserRepo->findOneBy(array('content'=> $content_post, "physician"=>$to_follow));
            $this->assertTrue(($existing_content != null), "Unable to validate content explosion");
        }
    }

    public function testExploderWithDuplicateDocs() {

        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $pgRepo = $this->em->getRepository('HospitalBundle:PhysicianGroup');
        $ppgRepo = $this->em->getRepository('HospitalBundle:PhysicianPhysicianGroup');
        $pmRepo = $this->em->getRepository('MessageBundle:PrivateMessage');
        $fosRepo = $this->em->getRepository('HospitalBundle:User');

        $sendingPhy = $fosRepo->findOneById(3);
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $pgroup = $pgRepo->findOneByName("Muc exploder with dups");
        $this->assertNotNull($pgroup," -- PhysicianGroup not found");

        $exploderUserId = $sendingPhy->getHospital()->getSetting('message_exploder_user');

        $pm = $msgLib->sendMessage($sendingPhy, $exploderUserId, null, "test msg", null, $pgroup, 0, false);

        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertNotNull($pm->getPhysicianGroup()," -- pilot message id must have pg > 0");
        $this->assertNotNull($pm->getMessageGroup()," -- pilot message id must have pm_group_id > 0");

        $j1 = array(
            'event' => 'MUC-PM',
            'message_id'=>$pm->getId(),
            'receiver_id'=>$exploderUserId,
            'sender_id'=>$sendingPhy->getId()
        );

        $countAtStart = count($pmRepo->findAll());

        $j=  json_decode(json_encode($j1));
        $rv = $msgLib->doExplode($j);
        $this->assertEquals(null,$rv,"Should accept this explosion");

        $countAtEnd = count($pmRepo->findAll());
        $this->assertEquals($countAtStart+2,$countAtEnd,"Should have one more number of messages when done");

        //examine contents.
        $matching = $pmRepo->findByMessageGroup($pm->getMessageGroup());
        $this->assertEquals(3,count($matching),"Should have 3 messages in this test");

        // now check all members of muc
        $members = $ppgRepo->findByPhysicianGroup($pgroup);
        $this->assertEquals(4,count($members), " number of entries in MUC should be four (add sender) ");

        // check if pilot pm is marked as deleted by sender
        $this->assertTrue($pm->getDeletedBySender(), " pilot message need to marked as deleted by sender");
    }
    
}

