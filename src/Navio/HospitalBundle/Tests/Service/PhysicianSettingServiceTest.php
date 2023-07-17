<?php

/**
 * Created by PhpStorm.
 * User: Nanda Yemparala
 * Date: 3/19/18
 * Time: 11:55 AM
 */


use Navio\HospitalBundle\Tests\Controller\TestConfig;

class PhysicianSettingServiceTest extends TestConfig {
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


    public function testOldSoundSettingsInSync() {

        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();

        $settingsService = $this->_container->get('physician.settings');
        $em = $this->_container->get('doctrine')->getManager();

        $physician = $em->getRepository("HospitalBundle:Physician")->find(1001);
        // old sound settings
        $settings = '{"New Consult_sound":"Chime","Consult Receipt Confirmation_sound":"Telegraph","Consult Reminder_sound":"Bell","New Secure Text_sound":"DoubleBell","Secure Text Receipt Confirmation_sound":"Telegraph","Secure Text Reminder_sound":"Bell","New Alerts_sound":"FireBell","New Labs_sound":"No Sounds & No Vibrate"}';

        $settingsService->saveFromJson($physician, $settings);

        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);

        $this->assertTrue(count($savedSettings) > 0, 'settings saved');

        foreach ($savedSettings as $setting) {

            if(strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');

                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
                $this->assertTrue(property_exists($soundConfig, 'sound'), 'Sound config does not contain sound');
            }

        }

        // mix old and new settings
        // old sound settings
        $settings = '{"New Consult_sound":"{"sound":"Chime","vibrate":true,"reminders":[30,60,90]}","Consult Receipt Confirmation_sound":"Telegraph","Consult Reminder_sound":"Bell","New Secure Text_sound":"DoubleBell","Secure Text Receipt Confirmation_sound":"Telegraph","Secure Text Reminder_sound":"Bell","New Alerts_sound":"FireBell","New Labs_sound":"No Sounds & No Vibrate"}';
        $settingsService->saveFromJson($physician, $settings);
        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);
        $this->assertTrue(count($savedSettings) > 0, 'settings saved');

        foreach ($savedSettings as $setting) {

            if(strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');

                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
                $this->assertTrue(property_exists($soundConfig, 'sound'), 'Sound config does not contain sound');
            }
        }



    }


    public function testSyncNewSoundSettingsWithConfig() {

        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();

        $settingsService = $this->_container->get('physician.settings');
        $em = $this->_container->get('doctrine')->getManager();

        $physician = $em->getRepository("HospitalBundle:Physician")->find(1001);


        // New sound settings
        $settings = '{"New Consult_sound":{"sound":"Chime","vibrate":true,"reminders":[30,60,90]},"Consult Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Consult Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Secure Text_sound":{"sound":"DoubleBell","vibrate":true,"reminders":[60,120,180]},"Secure Text Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Secure Text Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Alerts_sound":{"sound":"FireBell","vibrate":true,"reminders":[]},"New Labs_sound":{"sound":"No Sounds & No Vibrate","vibrate":false,"reminders":[60,120,180]}}';
        $settingsService->saveFromJson($physician, $settings);
        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);
        $this->assertTrue(count($savedSettings) > 0, 'settings saved');
        foreach ($savedSettings as $setting) {

            if (strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');
                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
                $this->assertTrue(property_exists($soundConfig, 'sound'), 'Sound config does not contain sound');
            }
        }


        // sound config without sound
        $settings = '{"New Consult_sound":{"vibrate":true,"reminders":[30,60,90]},"Consult Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Consult Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Secure Text_sound":{"sound":"DoubleBell","vibrate":true,"reminders":[60,120,180]},"Secure Text Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Secure Text Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Alerts_sound":{"sound":"FireBell","vibrate":true,"reminders":[]},"New Labs_sound":{"sound":"No Sounds & No Vibrate","vibrate":false,"reminders":[60,120,180]}}';
        $settingsService->saveFromJson($physician, $settings);
        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);
        $this->assertTrue(count($savedSettings) > 0, 'settings saved');
        foreach ($savedSettings as $setting) {

            if (strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');
                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
            }
        }

        // sound config with missing reminder
        $settings = '{"New Consult_sound":{"sound":"Chime","vibrate":true},"Consult Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Consult Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Secure Text_sound":{"sound":"DoubleBell","vibrate":true,"reminders":[60,120,180]},"Secure Text Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Secure Text Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Alerts_sound":{"sound":"FireBell","vibrate":true,"reminders":[]},"New Labs_sound":{"sound":"No Sounds & No Vibrate","vibrate":false,"reminders":[60,120,180]}}';
        $settingsService->saveFromJson($physician, $settings);
        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);
        $this->assertTrue(count($savedSettings) > 0, 'settings saved');
        foreach ($savedSettings as $setting) {

            if (strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');
                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
            }
        }

        // sound config with invalid json format.
        $settings = '{"New Consult_sound":{"sound":"SS,"vibrate":true},"Consult Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Consult Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Secure Text_sound":{"sound":"DoubleBell","vibrate":true,"reminders":[60,120,180]},"Secure Text Receipt Confirmation_sound":{"sound":"Telegraph","vibrate":true,"reminders":[]},"Secure Text Reminder_sound":{"sound":"Bell","vibrate":true,"reminders":[]},"New Alerts_sound":{"sound":"FireBell","vibrate":true,"reminders":[]},"New Labs_sound":{"sound":"No Sounds & No Vibrate","vibrate":false,"reminders":[60,120,180]}}';
        $settingsService->saveFromJson($physician, $settings);
        $savedSettings = $em->getRepository("HospitalBundle:PhysicianSetting")->findBy(['physician' => $physician]);
        $this->assertTrue(count($savedSettings) > 0, 'settings saved');
        foreach ($savedSettings as $setting) {

            if (strpos($setting->getName(), '_sound') !== false) {
                // this is a physician sound setting
                $this->assertTrue($setting->getConfig() != null, 'Config cannot be null');
                $soundConfig = json_decode($setting->getConfig());
                $this->assertTrue(is_object($soundConfig), 'Sound Config is not valid');
            }
        }

    }

}
