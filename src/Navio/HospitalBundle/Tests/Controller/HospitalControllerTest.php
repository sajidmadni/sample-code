<?php

namespace Navio\HospitalBundle\Tests\Controller;

class HospitalControllertest extends TestConfig {

    private $client;
    private $table = "Hospital";
    private $testField = "name";
    private $testManager;
    protected static $db;

    public static function setUpBeforeClass():void {
        if (strpos(DB_DSN, "pu_test") !== FALSE) {
            $fixture_sql_file = __DIR__ . '/../_files/' . "Fixture" . ".sql";
            if (file_exists($fixture_sql_file)) {
                self::$db = parent::setUpBeforeClassWithName($fixture_sql_file);
            }
        } else {
            echo 'Cannot run phpunit tests on non test/ dev databases', PHP_EOL;
        }
    }

    public function setUp():void {
        $this->testOVar = $this->config()['testData'];
        $this->testVar = 'E' . $this->config()['testData'];
        $this->testManager = new TestUtils();
    }

    public function testNew() {

//Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $path = "/hospital/new";
        $this->testManager->testForCreation($this, $path, $this->client, "hospital[save]", $this->testOVar, $this->table, $this->testField, array('hospital[logo]' => $this->uploadTestFile(), 'hospital[itPhone]' => "1234567890", 'hospital[itEmail]' => "hosptest@test.test", 'hospital[emrPhone]' => "1234569870", 'hospital[deletionTime]' => "30", 'hospital[passwordTimeout]' => " 30", 'hospital[scheduleNotificationEmail]' => "vfgc@gvc.cgdy"));
// create with empty Name
        $this->testManager->testForFailCreation($this, $path, $this->client, "hospital[save]", "abc", $this->table, $this->testField, array('hospital[name]' => " ", 'hospital[logo]' => $this->uploadTestFile()));
// create without Logo
        $this->testManager->testForFailCreation($this, $path, $this->client, "hospital[save]", "test2", $this->table, $this->testField);
        $this->client->restart();


//Hospital admin Test no Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $this->client->request('GET', $path);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();

//Hospital user Test no Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $this->client->request('GET', $path);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
    }

    public function testEdit() {
//      //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $path = '/hospital/edit/';
        $this->client->followRedirects(false);
        $crawler = $this->client->request('GET', '/hospital/edit/0');
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirects(true);
        $this->testManager->testForUpdate($this, $path, $this->client, "hospital[save]", $this->testOVar.'1', $this->table, $this->testField, $this->testField, $this->testVar, 'hospital[name]');

//uploadLogo
        $elements = $this->getInfoFromDb($this->testVar, $this->table, $this->testField);
        $this->assertNotEmpty($elements);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('GET', $path . $elements[0]['id']);
        $form = $crawler->selectButton("hospital[save]")->form();
        $this->client->submit($form, array('hospital[logo]' => $this->uploadTestGuiFile()));
        $this->testManager->testForUpdate($this, $path, $this->client, "hospital[save]", $this->testVar, $this->table, $this->testField, "itPhone", "12345098765", 'hospital[itPhone]');
        $this->client->restart();

//      //Hospital Admin Test Allow Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $el1 = $this->getInfoFromDb('testHospital', $this->table, $this->testField)[0]['id'];
        $crawler = $this->client->request('GET', '/hospital/edit/' . $el1);
        $form = $crawler->selectButton('hospital[save]')->form();
        $crawler = $this->client->submit($form, array('hospital[name]' => 'testEhospital'));
        $this->getInfoFromDb('testEhospital', $this->table, $this->testField);
//$this->testManager->testForUpdate($this, "/hospital/edit/".$el1, $this->client, "firstName[save]", $this->testVar, $this->table, $this->testField,$this->testField,"testEHospital",'firstName[name]');
        $this->client->restart();

//Hospital admin no Access to other hospital
        $this->client = $this->login($this, "hospitaladmin", "password");
        $hos = $this->getInfoFromDb($this->testVar, $this->table, $this->testField)[0]['id'];
        $this->client->followRedirects(false);
        $crawler = $this->client->request('GET', $path . $hos);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirects(true);
//$this->assertGreaterThan(0, $crawler->filter('html:contains("You dont have permission")')->count());
        $this->client->restart();

//Hospital User Test No Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $this->client->followRedirects(false);
        $crawler = $this->client->request('GET', $path . $testId);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirects(true);
//$this->assertGreaterThan(0, $crawler->filter('html:contains("You dont have permission")')->count());
        $this->client->restart();
    }

    public function testList() {
//Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $paths = array("/hospital/", "/hospital/list");
        $crawler = $this->client->request('GET', '/hospital/list');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("test")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("testHos1")')->count());
        $crawler = $this->client->request('GET', '/hospital/list',array('search_term'=>"test"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("test")')->count());
        $this->client->restart();

//Hospital Admin Test Allow Access only in same hospital
        $this->client = $this->login($this, "hospitaladmin", "password");
        $crawler = $this->client->request('GET', '/hospital/list');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("testHospital")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("testHos1")')->count());
        $this->client->restart();

//Hospital User Test No Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $this->client->followRedirects(false);
        $crawler = $this->client->request('GET', "/hospital/list");
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirects(true);
//$this->assertGreaterThan(0, $crawler->filter('html:contains("You dont have permission")')->count());
        $this->client->restart();
    }

    public function testGui() {
//Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $path = "/hospital/gui/";
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/gui/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hospital GUI")')->count());

//        $crawler=$this->client->request('POST',"/hospital/gui/" . $testId, array(
//        'save' => array('hospital_component[type]'=>"Feedback",'hospital_component[name]'=>"goodfb",'hospital_component[icon]'=>$this->uploadTestGuiFile())));
        $this->client->restart();

        //Hospital admin Allow Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/gui/" . $testId);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hospital GUI")')->count());
        $this->client->restart();

        //Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $crawler = $this->client->request('POST', "/hospital/gui/" . $testId);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
        
        // Login with HospitalB then GUI the HospitalA
        $this->client = $this->login($this, "second", "password");
        $crawler = $this->client->request('POST', "/hospital/gui/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hospital not found")')->count());
        $this->client->restart();
    }
    /** create new component usecases
        * case1: test with superadmin (allow access)
        * case2: test with hospital admin (allow access)
        * case3: test with hosptal user (No access)
    */
    public function testNewComponent() {
        //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/new_component", array('hospital_component' => array('type' => "Feedback", 'name' => "goodfb", 'position' => "1", 'id' => $testId), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("goodfb", "HospitalComponent", $this->testField);
        $this->assertEquals("Feedback", $elements[0]['type']);
        //create with type Schedule Procedure
        $crawler = $this->client->request('POST', "/hospital/new_component", array('hospital_component' => array('type' => "Schedule Procedure", 'name' => "time", 'position' => "1", 'id' => $testId), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("time", "HospitalComponent", $this->testField);
        $this->assertEquals("Schedule Procedure", $elements[0]['type']);
        $crawler = $this->client->request('POST', "/hospital/new_component", array('hospital_component' => array('type' => "Link/Phone Menu", 'name' => "Link/Phone Menu1", 'position' => "2", 'id' => $testId, "lab" => 0,"brd" => 0), 'menu_data' => array('0' => "test123", '1' => "test321"), 'menu_title' => array('0' => "test123", '1' => "test321")));
        $elements = $this->getInfoFromDb("Link/Phone Menu1", "HospitalComponent", $this->testField);
        $this->assertEquals("Link/Phone Menu", $elements[0]['type']);
        $ele = $this->getInfoFromDb($elements[0]['id'], "HospitalComponentMenuItem", 'hospitalComponent');
        $this->assertEquals("test123", $ele[0]['value']);
        $ele = $this->getInfoFromDb("boardlistajax1", "PhysicianGroup", 'name');
        $crawler = $this->client->request('POST', "/hospital/new_component", array('hospital_component' => array('type' => "Board", 'name' => "testboard", 'position' => "3", 'id' => $testId, "lab" => 0,"brd" => 1,"board"=>$ele[0]['id']), 'menu_data' => "some", 'menu_title' => "bla"));
        $elements = $this->getInfoFromDb("testboard", "HospitalComponent", $this->testField);
        $this->assertEquals("Feed", $elements[0]['type']);
        $board_details=json_decode($elements[0]['config']);
        $this->assertEquals($ele[0]['id'], $board_details->{'board_id'});
        $this->client->restart();

        //Hospital Admin Test Allow Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $crawler = $this->client->request('POST', "/hospital/new_component", array('hospital_component' => array('type' => "Secure Text", 'name' => "Lab Results test123", 'position' => "3", 'id' => $testId, 'phy' => "32", "lab" => 1,"brd" => 0), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("Lab Results test123", "HospitalComponent", $this->testField);
        $this->assertEquals("Secure Text", $elements[0]['type']);
        $ele = $this->getInfoFromDb($elements[0]['id'], "HospitalComponentMenuItem", 'hospitalComponent');
        $this->assertEquals("32", $ele[0]['value']);
        $this->client->restart();


        //Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $crawler = $this->client->request('POST', "/hospital/new_component");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
    }
    /** Edit component usecases
        * case1: edit with superadmin (allow access)
        * case2: edit with hospital admin (allow access)
        * case3: edit with hosptal user (No access)
    */
    public function testEditComp() {
        // Don't need this functionality anymore
        // Test with Super Admin
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("goodfb3", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Feedback", 'name' => "Badfb", 'position' => "1", 'id' => $testId), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("Badfb", "HospitalComponent", $this->testField);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Invalid action")')->count());
        $this->client->restart();
        // Test with Hospital Admin
        $this->client = $this->login($this, "hospitaladmin", "password");
        $elements = $this->getInfoFromDb("Lab Results test1233", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Lab Results", 'name' => "Lab Results test321", 'position' => "3", 'id' => $testId, 'phy' => "34", "lab" => 1,"brd" => 0), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("Lab Results test321", "HospitalComponent", $this->testField);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Invalid action")')->count());
        $this->client->restart();

        /*
        //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("goodfb3", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Feedback", 'name' => "Badfb", 'position' => "1", 'id' => $testId), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("Badfb", "HospitalComponent", $this->testField);
        $this->assertEquals("Feedback", $elements[0]['type']);

        $elements = $this->getInfoFromDb("Link/Phone Menu3", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Link/Phone Menu", 'name' => "Link/Phone Menu001", 'position' => "2", 'id' => $testId, "lab" => 0,"brd" => 0), 'menu_data' => array('0' => "Etest123", '1' => "Etest321", '2' => "newtest"), 'menu_title' => array('0' => "test123", '1' => "test321", '2' => "newtest")));
        $elements = $this->getInfoFromDb("Link/Phone Menu001", "HospitalComponent", $this->testField);
        $this->assertEquals("Link/Phone Menu", $elements[0]['type']);
        $ele = $this->getInfoFromDb($elements[0]['id'], "HospitalComponentMenuItem", 'hospitalComponent');
        $this->assertEquals("Etest321", $ele[2]['value']);
        $ele = $this->getInfoFromDb("boardlistajaxtest", "PhysicianGroup", 'name');
        $elements = $this->getInfoFromDb("testboard3", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];        
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Board", 'name' => "Etestboard", 'position' => "3", 'id' => $testId, "lab" => 0,"brd" => 1,"board"=>$ele[0]['id']), 'menu_data' => "some", 'menu_title' => "bla"));
        $elements = $this->getInfoFromDb("Etestboard", "HospitalComponent", $this->testField);
        $this->assertEquals("Feed", $elements[0]['type']);
        $board_details=json_decode($elements[0]['config']);
        $this->assertEquals($ele[0]['id'], $board_details->{'board_id'});
        $this->client->restart();

        //Hospital Admin Test Not Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $elements = $this->getInfoFromDb("Lab Results test1233", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/edit_component", array('hospital_component' => array('type' => "Lab Results", 'name' => "Lab Results test321", 'position' => "3", 'id' => $testId, 'phy' => "34", "lab" => 1,"brd" => 0), 'menu_title' => "bla", 'menu_data' => "some"));
        $elements = $this->getInfoFromDb("Lab Results test321", "HospitalComponent", $this->testField);
        $this->assertEquals("Secure Text", $elements[0]['type']);
        $ele = $this->getInfoFromDb($elements[0]['id'], "HospitalComponentMenuItem", 'hospitalComponent');
        $this->assertEquals("34", $ele[0]['value']);
        $this->client->restart();

        //Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $crawler = $this->client->request('POST', "/hospital/edit_component");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
        */
    }

    public function testviewcomp() {
        // Don't need this functionality anymore
        $this->client = $this->login($this, "hospitaladmin", "password");
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Invalid action")')->count());
        $this->client->restart();

        $this->client = $this->login($this, "testadmin", "password");
        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Invalid action")')->count());
        $this->client->restart();
        /*
        //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("goodfb3")')->count());
        $this->client->restart();

        //Hospital Admin Test Allow Access
        $this->client = $this->login($this, "hospitaladmin", "password");

        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Lab Results")')->count());
        $this->client->restart();
        
        // Login with HospitalB then view the HospitalA components
        $this->client = $this->login($this, "second", "password");
        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hospital not found")')->count());
        $this->client->restart();

        //Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $crawler = $this->client->request('POST', "/hospital/view_comp/" . $testId);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
        */
    }
    
    // test to move the position of the component
    public function testMoveComponent(){
        //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("testHospital", $this->table, $this->testField);
        $comp = $this->getInfoFromDb("Link/Phone Menu3", "HospitalComponent", $this->testField);
        $comp2 = $this->getInfoFromDb("Lab Results test", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('GET', "/hospital/movecomp/" . $testId, array('sorted'=>'component_'.$comp[0]['id'].',component_'.$comp2[0]['id']));
        $after_comp_position = $this->getInfoFromDb("Link/Phone Menu3", "HospitalComponent", $this->testField);
        $this->assertEquals($after_comp_position[0]['position'],1);
        $this->assertNotEquals($after_comp_position[0]['position'],$comp[0]['position']);
        $this->client->restart();
        
    }

    public function testDeletecomp() {
        //Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
        $elements = $this->getInfoFromDb("Link/Phone Menu3", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/delete_component/" . $testId);
        $elements = $this->getInfoFromDb("Link/Phone Menu3", "HospitalComponent", $this->testField);
        $this->assertEmpty($elements);
        $this->client->restart();
        
        // Login with HospitalB then delete the HospitalA components
        $this->client = $this->login($this, "second", "password");
        $elements = $this->getInfoFromDb("Lab Results test", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $this->client->followRedirects(false);
        $crawler = $this->client->request('POST', "/hospital/delete_component/" . $testId);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirects(true);
//        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hospital not found")')->count());
        $this->client->restart();

        //Hospital Admin Test allow Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $elements = $this->getInfoFromDb("Lab Results test", "HospitalComponent", $this->testField);
        $testId = $elements[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/delete_component/" . $testId);
        $elements = $this->getInfoFromDb("Lab Results test", "HospitalComponent", $this->testField);
        $this->assertEmpty($elements);
        $this->client->restart();

        //Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $crawler = $this->client->request('POST', "/hospital/delete_component/" . $testId);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();
    }

    public function testDelete() {

        $hospital_name = "testHospital";

//Hospital User Test Not Access
        $this->client = $this->login($this, "hospitaluser", "password");
        $path = "/hospital/delete/";
        $hid = $this->getInfoFromDb($hospital_name, 'Hospital', 'name')[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/delete/");
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->client->restart();

//Hospital admin Test Not Access
        $this->client = $this->login($this, "hospitaladmin", "password");
        $hoid = $this->getInfoFromDb($hospital_name, 'Hospital', 'name')[0]['id'];
        $crawler = $this->client->request('POST', "/hospital/delete/" . $hoid);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->restart();


//Super Admin Test Allow Access
        $this->client = $this->login($this, "testadmin", "password");
//$this->assertNull($hid[0]['deletedAt']);
        $crawler = $this->client->request('POST', $path . $hid);
        $hos = $this->getInfoFromDb($hospital_name, 'Hospital', 'name');
        $this->assertNotEmpty($hos[0]['deletedAt']);
//$this->testManager->testForDeleteRecord($this, $path, $this->client, $this->testVar, $this->table, $this->testField);

$this->client->restart();

}

public function testProfileAccess()
{
    //Login using test credentials
    $hospital_name = "testHospital";
    $this->client = $this->login($this, "hospitaladmin", "password");
    $hoid = $this->getInfoFromDb($hospital_name, 'Hospital', 'name')[0]['id'];

    //Try to get to profile page
    $crawler = $this->client->request('GET', "/pub/profile-update/".$hoid."/31/31");
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $resp_html = $this->client->getResponse()->getContent();

    //Look for a specific string in response html
    $this->assertTrue(strpos($resp_html, 'information') !== false);
    $this->client->restart();
}

public function tearDown():void
{
	$refl = new \ReflectionObject($this);
	foreach ($refl->getProperties() as $prop) {
		if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
			$prop->setAccessible(true);
			$prop->setValue($this, null);
		}
	}
}
public static function tearDownAfterClass():void
{
	self::$db = null;

}

}
