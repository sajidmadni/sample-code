<?php


/**
 * Created by PhpStorm.
 * User: Nanda Yemparala
 * Date: 8/26/19
 * Time: 12:03 PM
 */

use Navio\ConsultBundle\Entity\Consult;
use Navio\HospitalBundle\Entity\HospitalSetting;
use Navio\HospitalBundle\Tests\Controller\TestConfig;

class PushNotificationServiceTest extends TestConfig {
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


    public function testNewConsultNotificationMessage() {
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();

        $this->doSql('DELETE from push_notification;');
        $this->doSql("INSERT INTO device (physician_id, app_client, created_at,updated_at,app_id,uid) VALUES (1001,'Android',now(),now(), 'com.uniphy.test.app2','uidtestapp2');");
        $this->doSql("INSERT INTO device (physician_id, app_client, created_at,updated_at,app_id,uid) VALUES (1002,'Android',now(),now(), 'com.uniphy.test.app2','uidtestapp1');");
        $em = $this->_container->get('doctrine')->getManager();
        $pushService = $this->_container->get('navio.push.notification');
        $phyRepo = $em->getRepository('HospitalBundle:Physician');
        $pushRepo = $em->getRepository('MessageBundle:PushNotification');

        $sender = $phyRepo->find(1001);
        $receiver = $phyRepo->find(1002);

        $referral = new Consult();
        $referral->setConsultType(Consult::CONSULT_TYPE_REFERRAL);
        $pushService->sendNotificationForNewConsult($referral, $sender, $receiver, Navio\Utils\Utils::getCurrentUTC(), false, false);
        $queuedNotifications = $pushRepo->findAll();
        $this->assertGreaterThan(0, count($queuedNotifications));
        $this->assertStringContainsString($sender->getHospital()->getReferralComponentTitle(), $queuedNotifications[0]->getText(), 'Referral title should be set on notifications');

        // changing the referral title setting
        $referralTitleSettingName = HospitalSetting::$SETTING_HOSPITAL_REFERRAL_COMPONENT_TITLE;
        $newReferalTitle = 'referral title'.__LINE__;
        $this->doSql("delete from hospital_setting where name like '$referralTitleSettingName'");
        $this->doSql("INSERT into hospital_setting (hospital_id, name, val) values (5, '$referralTitleSettingName', '$newReferalTitle')");
        
        $pushService->sendNotificationForNewConsult($referral, $sender, $receiver, Navio\Utils\Utils::getCurrentUTC(), false, false);
        $queuedNotifications = $pushRepo->findAll();
        $this->assertGreaterThan(0, count($queuedNotifications));
        $this->assertStringContainsString($sender->getHospital()->getReferralComponentTitle(), $queuedNotifications[0]->getText(), 'Referral title should be set on notifications');


        // test consult notification text
        $this->doSql('delete from push_notification;');
        $pushService->sendNotificationForNewConsult(new Consult(), $sender, $receiver, Navio\Utils\Utils::getCurrentUTC(), false, false);
        $queuedNotifications = $pushRepo->findAll();
        $this->assertGreaterThan(0, count($queuedNotifications));
        $this->assertStringContainsString($sender->getHospital()->getConsultComponentTitle(), $queuedNotifications[0]->getText(), 'Referral title should be set on notifications');
    }

    public function testConsultFollowupNotificationTest()
    {
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();

        $this->doSql('DELETE from push_notification;');
        $this->doSql("INSERT INTO device (physician_id, app_client, created_at,updated_at,app_id,uid) VALUES (1001,'Android',now(),now(), 'com.uniphy.test.app2','uidtestapp2');");
        $this->doSql("INSERT INTO device (physician_id, app_client, created_at,updated_at,app_id,uid) VALUES (1002,'Android',now(),now(), 'com.uniphy.test.app2','uidtestapp1');");
        $em = $this->_container->get('doctrine')->getManager();
        $pushService = $this->_container->get('navio.push.notification');
        $phyRepo = $em->getRepository('HospitalBundle:Physician');
        $pushRepo = $em->getRepository('MessageBundle:PushNotification');
        $sender = $phyRepo->find(1001);
        $receiver = $phyRepo->find(1002);

        $testReferral = new Consult();
        $testReferral->setConsultType(Consult::CONSULT_TYPE_REFERRAL);

        $pushService->sendFollowupNotificationForConsult($testReferral, $sender, $receiver, Navio\Utils\Utils::getCurrentUTC());
        $queuedNotifications = $pushRepo->findAll();
        $this->assertGreaterThan(0, count($queuedNotifications));
        $this->assertStringContainsString($sender->getHospital()->getReferralComponentTitle(), $queuedNotifications[0]->getText(), 'Referral title should be set on notifications');

        // check notification message for consult type
        $this->doSql("delete from push_notification;");
        $pushService->sendFollowupNotificationForConsult(new Consult(), $sender, $receiver, Navio\Utils\Utils::getCurrentUTC());
        $queuedNotifications = $pushRepo->findAll();
        $this->assertGreaterThan(0, count($queuedNotifications));
        $this->assertStringContainsString($sender->getHospital()->getConsultComponentTitle(), $queuedNotifications[0]->getText(), 'Referral title should be set on notifications');
    }

}