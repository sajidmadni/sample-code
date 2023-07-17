<?php

/**
 * Created by PhpStorm.
 * User: Nanda Yemparala
 * Date: 3/19/18
 * Time: 11:55 AM
 */


use Navio\HospitalBundle\Tests\Controller\TestConfig;

class MessageLibTest extends TestConfig {
    protected static $db;

    public static function setUpBeforeClass():void {
        if  (strpos(DB_DSN, "pu_test") !== FALSE){
            $fixture_sql_file = __DIR__ . '/../../../SyncBundle/Tests/_files/'."Fixture".".sql";
            if (file_exists($fixture_sql_file)){
                self::$db=parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        }
        else{
            echo 'Cannot run phpunit tests on non test/ dev databases',PHP_EOL;
        }
        
        
    }
    
    public function setUp():void {
                global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->em = $this->_container->get('doctrine')->getManager();

         parent::doSql(
            "UPDATE physician set attributes='{\"mgr_email\": \"mgr@example.com\"}', cell_phone='q123456789', is_active=0"
            . " where id = 1101;"
            ."UPDATE physician set email='mgr@example.com',attributes='{\"mgr1_email\": \"mgr11@example.com\"}'"
            . " where id = 3;"
            ."UPDATE physician set is_active=1, cell_phone='q123456789' "
            . " where id = 4;"
            ."DELETE FROM sms_notification WHERE 1;"
            ."INSERT INTO pu_test.hospital_setting (hospital_id,name,val) values (2,'inactive-sms','This is a message if sent to inactive users');");
    }

    public function testNegativeCasesToSendSMSForMessageWithOptions() {
        
        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $smsRepo = $this->em->getRepository('MessageBundle:SMSNotification');
        

        $sendingPhy = $phyRepo->findOneByAccessCode('5U7CPWYT');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "Inactive SMS Test";
        
        //No Mobile Number
        $pm = $msgLib->sendMessageWithOptions($sendingPhy->getUser(),3, NULL, $text, null);
        $sms=$smsRepo->findOneByText("This is a message if sent to inactive users");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertNull($sms," --sms must not be sent to users without mobile number");
        
        //Active user
        $pm = $msgLib->sendMessageWithOptions($sendingPhy->getUser(),4, NULL, $text, null);
        $sms=$smsRepo->findOneByText("This is a message if sent to inactive users");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertNull($sms," --sms must not be sent to active users");
        
        //Not Configured SMS Message in Settings
        parent::doSql("DELETE FROM hospital_setting WHERE name='inactive-sms'");
        $pm = $msgLib->sendMessageWithOptions($sendingPhy->getUser(),1101, NULL, $text, null);
        $sms=$smsRepo->findOneByText("This is a message if sent to inactive users");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertNull($sms," --sms must not be sent to active users");
        
        
        
        
    }
    
    public function testPositiveCaseToSendSMSForMessageWithOptions() {
        $msgLib = new \Navio\HospitalBundle\Service\MessageLib($this->em, $this->_container);
        $phyRepo = $this->em->getRepository('HospitalBundle:Physician');
        $smsRepo = $this->em->getRepository('MessageBundle:SMSNotification');
        

        $sendingPhy = $phyRepo->findOneByAccessCode('5U7CPWYT');
        $this->assertNotNull($sendingPhy," -- Physician (sender) not found");

        $text = "Inactive SMS Test";
        
        $pm = $msgLib->sendMessageWithOptions($sendingPhy->getUser(),1101, NULL, $text, null);
        $sms=$smsRepo->findOneByText("This is a message if sent to inactive users");
        $this->assertGreaterThan(0,$pm->getId()," -- pilot message id must be > 0");
        $this->assertGreaterThan(0,$sms->getId()," --sms message id must be > 0");
    }

}
