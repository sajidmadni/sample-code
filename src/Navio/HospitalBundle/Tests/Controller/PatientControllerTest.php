<?php

namespace Navio\HospitalBundle\Tests\Controller;
use Navio\HospitalBundle\Controller\PatientApiController;

class PatientControllerTest extends TestConfig {

    
    private $client;
    private $table = "Patient";
    private $testField = "firstName";
    private $testManager;
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
    public function setUp():void {
        $this->testOVar=$this->config()['testData'];
        $this->testVar='E'.$this->config()['testData'];
        $this->testManager=new TestUtils();
    }
   /*
    * New patient usecases
    * 1. Test with super admin no access
    * 2. Test with hospital admin allow access
    * 3. Test with existed patient details
    * 4. Test with hospital user no access
    */
   public function testNewPatient() 
    {
       $path = "/patient/new";
       //Super Admin Not Acces
       $client = $this->login($this,"testadmin", "password");
       $client->followRedirects(false);
       $client->request('GET', $path);
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();
       
       
       
       //Hospital Admin Allow Access
       $client = $this->login($this,"hospitaladmin", "password");
       
       $diagnosis_path = "/diagnosis/new";
       $this->testManager->testForCreation($this, $diagnosis_path, $client, "diagnosis[save]", "diagnosis11", "HospitalDiagnosis", "name");
       $this->testManager->testForCreation($this, $diagnosis_path, $client, "diagnosis[save]", "diagnosis21", "HospitalDiagnosis", "name");
       $this->testManager->testForCreation($this, $diagnosis_path, $client, "diagnosis[save]", "diagnosis31", "HospitalDiagnosis", "name");
       $this->testManager->testForCreation($this, $diagnosis_path, $client, "diagnosis[save]", "diagnosis41", "HospitalDiagnosis", "name");
       $dia1=$this->getInfoFromDb("diagnosis11","HospitalDiagnosis","name")[0]['id'];
       $dia2=$this->getInfoFromDb("diagnosis21","HospitalDiagnosis","name")[0]['id'];
       $dia3=$this->getInfoFromDb("diagnosis31","HospitalDiagnosis","name")[0]['id']; 
       $dia4=$this->getInfoFromDb("diagnosis41","HospitalDiagnosis","name")[0]['id'];
       $phy1=$this->getInfoFromDb("agencyadmin","Physician","firstName")[0]['id'];
       $phy2=$this->getInfoFromDb("hospitaluser","Physician","firstName")[0]['id'];
       
       
       
       //$loc=$this->getInfoFromDb("test","Location","name")[0]['id'];
       $lans=$this->getInfoFromDb("English","HospitalLanguage","name")[0]['id'];
       $agid=$this->getInfoFromDb("testagency","HospitalAgency","name")[0]['id'];
      // $this->testManager->testForCreation($this, $path, $client, "savePatient", $this->testOVar, $this->table, $this->testField,array('patient[language]'=>$lans,"patient[zip]"=>"123456","patient[firstName]"=>"test","patient[lastName]"=>"testing","patient[patientId]"=>"1234"),true);
      
       $crawler=$client->request('GET',$path);
       $form=$crawler->selectButton("savePatient")->form();
       $testData=$form->getPhpValues();       
       $testData['patient']['firstName']='test1';
       $testData['patient']['lastName']='testing1';
       $testData['patient']['patientId']='testswe1';
       $testData['patient']['birthdate']='08-20-1993';
       $testData['agency']='testagency';
       $testData['patient']['location'] = 'Home';
       $testData['patient']['language']=$lans;
       $testData['patient']['zip']='12345';
       $testData['diagnosis1']=array($dia1,$dia2);
       $testData['diagnosis2']=array($dia3,$dia4);
       $testData['zphysicianlist']=array($phy1,$phy2);
       $client->request($form->getMethod(),$form->getUri(),$testData);
       $elements = $this->getInfoFromDb('test1', $this->table, 'firstName','HospitalBundle',array(),true);
       $this->assertEquals($elements[0]->getLastName(), 'testing1');
       $this->assertEquals($elements[0]->getAgency()->getId(), $agid);
       $phypat = $this->getInfoFromDb($elements[0]->getId(), "PhysicianPatient", 'patient','HospitalBundle',array(),true);
       $this->assertEquals($phypat[0]->getPhysician()->getId(), $phy1);
       $this->assertEquals('2', sizeof($phypat));
       $client->restart();
       
       //Unique test for first name and last name
       $client = $this->login($this,"hospitaladmin", "password");
       $crawler = $client->request('GET', $path);
       $form = $crawler->selectButton('savePatient')->form();
       $crawler=$client->submit($form,array("patient[firstName]"=>"test1","patient[lastName]"=>"testing1","patient[patientId]"=>"123",'patient[language]'=>$lans,"patient[zip]"=>"123456"));
       $this->assertGreaterThan(0, $crawler->filter('html:contains("could not save patient. Duplicate Patient id")')->count());
       $client->restart();
       
       //Hospital User No Access
       $client = $this->login($this,"hospitaluser", "password");
       $client->followRedirects(false);
       $crawler = $client->request('POST', $path);
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();

       // Test to check if patient id exists or not in the current hospital, response true means the id exists
        $client = $this->login($this,"hospitaladmin", "password");
        $path   = "/patient/search-by-patient-id";
        $crawler = $client->request('POST', $path, array('patient_id' => '456'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $arr    =  json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals($arr['response'],true);
        $client->restart();

        // Test to check if patient id exists or not in the current hospital
        $client = $this->login($this,"hospitaladmin", "password");
        $path   = "/patient/search-by-patient-id";
        $crawler = $client->request('POST', $path, array('patient_id' => '8989'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $arr    =  json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals($arr['response'],false);
        $client->restart();

        // Test to check the same patient id of different hospital and it should allow to add (response should be false)
        $client = $this->login($this,"second", "password");
        $path   = "/patient/search-by-patient-id";
        $crawler = $client->request('POST', $path, array('patient_id' => '456'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $arr    =  json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals($arr['response'],false);
        $client->restart();
    }
    
    /* Edit patient usecases
     * 1. Test with superadmin No access
     * 2. Test with hospitaladmin, to edit other hospital patients No access
     * 3. Test with hospitaladmin allow access
     * 4. Test with Hospital user no access 
     * 5. Test with false patientID
     */
    public function testEditpatAction()
    {
       //Super Admin Not Acces
       $client = $this->login($this,"testadmin", "password");
       $dia1=$this->getInfoFromDb("diagnosis1","HospitalDiagnosis","name")[0]['id'];
       $dia2=$this->getInfoFromDb("diagnosis2","HospitalDiagnosis","name")[0]['id'];
       $dia3=$this->getInfoFromDb("diagnosis3","HospitalDiagnosis","name")[0]['id']; 
       $dia4=$this->getInfoFromDb("diagnosis4","HospitalDiagnosis","name")[0]['id'];
       $phy1=$this->getInfoFromDb("agencyadmin","Physician","firstName")[0]['id'];
       $phy2=$this->getInfoFromDb("hospitaluser","Physician","firstName")[0]['id'];
       $phy3=$this->getInfoFromDb("hospitaladmin","Physician","firstName")[0]['id'];
       $path = "/patient/edit/";     
       $patid =$this->getInfoFromDb($this->testOVar,$this->table,$this->testField)[0]['id'];
       $client->followRedirects(false);
       $crawler = $client->request('POST', $path.$patid);
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();
       
       //User hospital != patient hospital not Access
       $client = $this->login($this,"second", "password");
       $crawler = $client->request('POST', $path.$patid);
       $client->followRedirects(false);
       $crawler = $client->request('POST', $path.$patid);
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();
       
       //Hospital Admin Allow Access
       $client = $this->login($this,"hospitaladmin", "password");
       $this->testManager->testForUpdate($this, $path, $client, "savePatient", $this->testOVar, $this->table, $this->testField,$this->testField,$this->testVar,'patient[firstName]');
       $this->assertNotNull($this->getInfoFromDb($this->testVar,$this->table,$this->testField));
       
       
       $patid=$this->getInfoFromDb($this->testVar,$this->table,$this->testField)[0]['id'];
       $crawler = $client->request('GET', $path.$patid);
       $form = $crawler->selectButton("savePatient")->form();
       $testData=$form->getPhpValues();
       $testData['app_PatientType']['physician']='32';
       $client->request($form->getMethod(),$form->getUri(),$testData);
       
       // test to change zphysicians and diagnosis.
       $patid=$this->getInfoFromDb($this->testVar,$this->table,$this->testField)[0]['id'];
       $crawler = $client->request('GET', $path.$patid);
       $form = $crawler->selectButton("savePatient")->form();
       $testData=$form->getPhpValues();
       $testData['diagnosis1']=array($dia3,$dia2);
       $testData['diagnosis2']=array($dia1,$dia4);
       $testData['zphysicianlist']=array($phy3);
       $client->request($form->getMethod(),$form->getUri(),$testData);
       $phypat = $this->getInfoFromDb($patid, "PhysicianPatient", 'patient','HospitalBundle',array(),true);
       $this->assertEquals($phypat[0]->getPhysician()->getId(), $phy3);
       $this->assertEquals('1', sizeof($phypat));
       
       
       // Test to remove Zphysicians
       $crawler = $client->request('GET', $path.$patid);
       $form = $crawler->selectButton("savePatient")->form();
       $testData=$form->getPhpValues();
       $testData['diagnosis1']=array();
       $testData['diagnosis2']=array();
       $testData['zphysicianlist']=array();
       $client->request($form->getMethod(),$form->getUri(),$testData);
       $phypat = $this->getInfoFromDb($patid, "PhysicianPatient", 'patient','HospitalBundle',array(),true);
       $this->assertEmpty($phypat);
       
       $client->restart();
       
       //Hospital User No Access
       $client = $this->login($this,"hospitaluser", "password");
       $client->followRedirects(false);
       $crawler = $client->request('POST', $path.$patid);
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();
       
       //With False Patient id
       $client = $this->login($this,"hospitaladmin", "password");
       $client->followRedirects(false);
       $client->request('POST', $path.'0');
       $this->assertTrue($client->getResponse()->isRedirect());
       $client->restart();
       }
    
    public function testPatientdetailAction()
    {
        //Super Admin Not Access
        $client = $this->login($this,"testadmin", "password");
        $patid=$this->getInfoFromDb("Etest1",$this->table,$this->testField)[0]['id'];
        $client->followRedirects(false);
        $path="/patient/detail/".$patid;
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //User Hospital !=Patient Hospital not Access
        $client = $this->login($this,"second", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Agency Admin Allow access (if patient agency=Agency admin Agency)
        $client = $this->login($this,"agencyadmin", "password");
        $crawler = $client->request('POST', $path);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("'.$this->testVar.'")')->count());
        $client->restart();
        
        
        //Agency Admin not Access other agency
        $client = $this->login($this,"agencyadminwithagencywithoutcategory", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Hospital Admin Allow acces
        $client = $this->login($this,"hospitaladmin", "password");
        $crawler = $client->request('POST', $path);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("'.$this->testVar.'")')->count());
        $client->restart();
        
        //Hospital User not Access
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        
    }
    
    public function testPatientAction()
    {
        $path = array('/patient/list','/patient/');
        //Super Admin Not Access
        $client = $this->login($this,"testadmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', "/patient/list");
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
    
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $this->testManager->testForListOrSearch($this, $path, $client, $this->testVar,array("search_term"=>  $this->testVar));
        $this->testManager->testForListOrSearch1($this, $path, $client, $this->testVar,array("search_term"=>  "bla"));
        $crawler = $client->request('POST', "/patient/list",array("search_term"=>  "bla12223"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("not find patients")')->count());
        $client->restart();
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', "/patient/list");
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
    }
    
    /*
     *  Test to remove the care team phy 
     */
    public function testRemoveCareTeamPhy(){
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $element=$this->getInfoFromDb("1","PhysicianPatient","patient",'HospitalBundle',array(),true);
        $phyPatId=$element[0]->getPhysician()->getId();
        $path="/patient/careteamphys/".$phyPatId."/1";
        $crawler = $client->request('GET', $path);
        $element=$this->getInfoFromDb("1","PhysicianPatient","patient");
        $this->assertEmpty($element);
        $client->restart();
    }
    
    public function testDelpatientAction() 
    {
        //Super Admin Not Access
        $client = $this->login($this,"testadmin", "password");
        $element=$this->getInfoFromDb($this->testVar."1",$this->table,$this->testField);
        $id=$element[0]['id'];
        $path="/patient/delete/".$id;
        $client->followRedirects(false);
        $this->assertNull($element[0]['deletedAt']);
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $element1=$this->getInfoFromDb($this->testVar."1",$this->table,$this->testField);
        $this->assertNull($element1[0]['deletedAt']);
        $client->restart();
        
        //False patient id
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST',"/patient/delete/0" );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("could not find patient")')->count());
        //$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $client->restart();
        
        //Hospital User no Access
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
       
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $this->assertNull($element[0]['deletedAt']);
        $crawler = $client->request('POST', $path);
        $this->assertEquals(0, $crawler->filter('html:contains("'.$this->testVar.'")')->count());
        $element=$this->getInfoFromDb($this->testVar."1",$this->table,$this->testField);
        $this->assertNotNull($element[0]['deletedAt']);
                
    }
    
    public function testDeletedPatientList()
    {
        $path = array('/patient/dlist');
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $crawler = $client->request('POST', "/patient/dlist");
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Dtest1")')->count());
        $client->restart();
        
        //Hospital User No Acces
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $client->request('POST', "/patient/dlist");
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Super Admin No Access
        $client = $this->login($this,"testadmin", "password");
        $client->followRedirects(false);
        $client->request('POST', "/patient/dlist");
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
    }
    
    public function testUndelpatAction()
    {
        //Super Admin No Access
        $client = $this->login($this,"testadmin", "password");
        $element=$this->getInfoFromDb("Dtest1",$this->table,$this->testField);
        $id=$element[0]['id'];
        $client->followRedirects(false);
        $client->request('POST', "/patient/undelete/".$id);
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertNotNull($element[0]['deletedAt']);
        $client->restart();
        
        //null id
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST',"/patient/undelete/0" );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("could not find patient")')->count());
       // $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $client->restart();
        
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $element=$this->getInfoFromDb("Dtest1",$this->table,$this->testField);
        //$this->assertNotNull($element[0]['deletedAt']);
        $crawler = $client->request('POST', "/patient/undelete/".$id);
        $element1=$this->getInfoFromDb("Dtest1",$this->table,$this->testField);
        $this->assertNull($element1[0]['deletedAt']);
        $client->restart();
        
        //Hospital User No Access
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $client->request('POST', "/patient/undelete/".$id);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        
        
    }
    
    public function testUploadpatient()
    {
        //Super Admin No Access
        $path="/patient/upload";
        $client = $this->login($this,"testadmin", "password");
        $client->followRedirects(false);
        $client->request('GET', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Hospitaluser No Access
        $client = $this->login($this,"hospitaluser", "password");
        $client->followRedirects(false);
        $client->request('GET', $path);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Hospital Admin Allow Access
        $client = $this->login($this,"hospitaladmin", "password");
        $crawler=$client->request('GET', $path);
        //print_r($crawler);
        $elements = $this->getInfoFromDb("testcsv", $this->table, $this->testField);
        $this->assertEmpty($elements);
        $form = $crawler->selectButton("form[upload]")->form();
       
        $crawler=$client->submit($form,array('form[file]'=>$this->getFile("patuploaderror_1.csv")));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("required")')->count());
            
        $crawler=$client->request('GET', $path);
        $form = $crawler->selectButton("form[upload]")->form();
        $crawler=$client->submit($form,array('form[file]'=>$this->getFile("patuploaderror_2.csv")));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("required")')->count());
            
        $crawler=$client->request('GET', $path);
        $form = $crawler->selectButton("form[upload]")->form();
        $crawler=$client->submit($form,array('form[file]'=>$this->getFile("patuploaderror_3.csv")));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("required")')->count());
            
        $crawler=$client->request('GET', $path);
        $form = $crawler->selectButton("form[upload]")->form();
        $crawler=$client->submit($form,array('form[file]'=>$this->getFile("patuploaderror_4.csv")));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("required")')->count());
            
        $crawler=$client->request('GET', $path);
        $form = $crawler->selectButton("form[upload]")->form();
        $crawler=$client->submit($form,array('form[file]'=>$this->getFile("patupload.csv")));
        $this->getInfoFromDb("testcsv",$this->table,$this->testField);
        $this->info("$path success");
        
    }
    /**
     * to test patient my list
     * test with hospital admin not access
     * test with hospital user allow access
     * test with super admin not allow access
     */
    public function testPatientHospUserMyPatAction() 
    {
        $path = array('/patient/mylist','/patient/');
        //Hospitaladmin No Access
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $client->request('GET', '/patient/mylist');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //Hospitaluser allow access
        $client = $this->login($this,"hospitaluser", "password");
        $crawler=$client->request('GET', '/patient/mylist');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("agency, patforagencytest")')->count());
        $this->testManager->testForListOrSearch($this, $path, $client, "patient",array("search_term"=> "patient"));
        $crawler = $client->request('GET', "/patient/mylist",array("search_term"=> "agency"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("agency, patforagencytest")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("pat,patient")')->count());
        $client->restart();
        
        //if user hava agency get patients of user agency
        $client = $this->login($this,"hospitaluserwithagency", "password1");
        $crawler=$client->request('GET', '/patient/mylist');
        $this->assertEquals(0, $crawler->filter('html:contains("could not find patients")')->count());
        $client->restart();
        
        //Super admin No Access
        $client = $this->login($this,"testadmin", "password");
        $client->followRedirects(false);
        $client->request('GET', '/patient/mylist');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();

        // Login with Hospitaluser, check Patient Last Seen has access due to Patient Handoff Enabled
        $client = $this->login($this,"hospitaluser", "password");
        // Check hospital don't have the timezone
        $hos = $this->getInfoFromDb("testHospital", "Hospital", "name","HospitalBundle",array(),true);
        $hospitalTimezone = $hos[0]->getSetting('timezone');
        $this->assertNotEquals('America/New_York', $hospitalTimezone);
        // Insert patient settings in the hospital settings for patient handoff & also set the hospital time zone to 'America/New_York'
        $this->doSql("insert into  hospital_setting (hospital_id,name,val) values (27,'patient-information','{\"PATIENT DETAILS\": { \"fields\": [{\"id\": \"1\",\"type\": \"text\",\"label\": \"First Name\",\"map\": \"first_name\",\"edit\": \"N\"}]}}'), (27, 'timezone', 'America/New_York')");
        $crawler=$client->request('GET', '/patient/mylist');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Mark seen")')->count());
        $client->restart();

        // Update existing patient id with last seen date(using UTC time zone)
        date_default_timezone_set('UTC');
        $getCurrentUTCTime = new \DateTime('now');
        $currentDateTime = $getCurrentUTCTime->format('Y-m-d H:i:s');;
        $this->doSql("UPDATE patient SET last_updated_by = '34', last_seen_date = '".$currentDateTime."' WHERE id = 1;");
        // Check the patient has display the last seen date according to hospital settings time zone
        $client = $this->login($this,"hospitaluser", "password");
        $hos = $this->getInfoFromDb("testHospital", "Hospital", "name","HospitalBundle",array(),true);
        $hospitalTimezone = $hos[0]->getSetting('timezone');
        $this->assertEquals('America/New_York', $hospitalTimezone);
        $crawler2 = $client->request('GET', '/patient/mylist');
        date_default_timezone_set('UTC');
        $now = new \DateTime($currentDateTime);
        $now->setTimezone(new \DateTimeZone("$hospitalTimezone"));
        $serachDate = $now->format('m/d/Y H:i A');
        $this->assertGreaterThan(0, $crawler2->filter('html:contains("'.$serachDate.'")')->count());
        $client->restart();
    }
    /**
     * Testing Patient Admin Filter
     */
    public function testPatientAdminFilter() {
        $client = $this->login($this,"testfilterPhysician@testfilterPhysician.com", "password");
        $crawler=$client->request('GET', '/patient/mylist');
        $this->assertGreaterThan(1, substr_count($crawler->html(),"testfilter, testfilter (0)"));
        $crawler=$client->request('GET', '/patient/mylist/11?my_patients=1');
        $this->assertGreaterThan(1, substr_count($crawler->html(),"testfilter, testfilter (0)"));
        // if URL is belongs to My Patients i.e. my_patients=1, then it should containts my_patients=1
        $this->assertRegExp('/\my_patients=1$/', $client->getRequest()->getUri());
        $crawler=$client->request('GET', '/patient/mylist/38');
        $this->assertLessThan(2, $crawler->filter('html:contains("test")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Task List")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("To-do")')->count());
        // if URL is belongs to All Patients it should not containts my_patients=1
        $this->assertRegExp('/\/38$/', $client->getRequest()->getUri());
        $this->assertNotRegExp('/\my_patients=1$/', $client->getRequest()->getUri());
    }
    /**
     * to test patient patient list for hospital user
     * test with hospital admin not access
     * test with hospital user with agency allow access
     * test with hospital user allow access
     * test with super admin not allow access
     */
    public function testPatientAgencyUserAction() {
        $path = array('/patient/all','/patient/');
        //Hospitaladmin No Access
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $client->request('GET', '/patient/all');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
        //if user hava agency get patients of user agency
        $client = $this->login($this,"hospitaluserwithagency", "password1");
        $crawler=$client->request('GET', '/patient/all');
        $this->assertEquals(0, $crawler->filter('html:contains("agency, patforagencytest")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("pat,patientwithagency")')->count());
        $crawler = $client->request('GET', "/patient/all",array("search_term"=> "patforagencytest"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Could not find patients!")')->count());
        $crawler = $client->request('GET', "/patient/all",array("search_term"=> "patientwithagency"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("pat,patientwithagency")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Task List")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("To-do")')->count());
        $client->restart();
        
         //Hospitaluser allow access
        $client = $this->login($this,"hospitaluser", "password");
        $crawler=$client->request('GET', '/patient/all');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("agency,patforagencytest")')->count());
        $this->testManager->testForListOrSearch($this, $path, $client, "patient",array("search_term"=> "patient"));
        $crawler = $client->request('GET', "/patient/all",array("search_term"=> "agency"));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Could not find patients!")')->count());
        $client->restart();
        
        //Super admin No Access
        $client = $this->login($this,"testadmin", "password");
        $client->followRedirects(false);
        $client->request('GET', '/patient/all');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->restart();
        
    }

    /**
     * This test checks if the dashboard screen is reachable or not
     */
    public function testHandOffDashboard(){
        $path = "/patient/patient_handoff_dashboard";
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('GET', $path);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertGreaterThan(0, $crawler->filter(0,'html:contains("Switch")')->count());
        $this->assertGreaterThan(0, $crawler->filter(0,'html:contains("Add")')->count());
        $this->assertGreaterThan(0, $crawler->filter(0,'html:contains("Remove")')->count());
        $client->restart();

        $client = $this->login($this,"hospitaladmin", "password"); 
        $hos=$this->getInfoFromDb("SETTING_PATIENT_INFORMATION", "HospitalSetting", "name","HospitalBundle",array(),true);
        if($hos){
          $handOffEnabled=$hos[0]->getHospital()->getSetting('patient-information');
          $this->assertEquals('patient-information', $handOffEnabled);
          $client->restart();
        }
        
    }

    /**
     * This test checks if the patient handoff dashboard screen is accessible or not against new Patient Handoff Role
     * Additional check: If Hospital seetings(patient-information) is enable then allow to access
    */
    public function testPatientHandoffRole()
    {      
        $client = $this->login($this,"hospitaladmin", "password"); 
        $hospitalUser =$this->getInfoFromDb("hospitaluser","user",'username')[0]['id'];
        $crawler = $client->request('GET', '/users/roles/' . $hospitalUser);
        $form = $crawler->selectButton("form_Save")->form();
        $client->submit($form, array(
            'form[RolePatientHandoffAdmin]' => 1,
        ));
        $elements =$this->getInfoFromDb("hospitaluser",'user','username');
        $editrole=$elements[0]['roles'];
        $newroles=array('0'=>'ROLE_HOSPITAL_USER','1'=>'ROLE_PATIENT_HANDOFF_ADMIN');
        $this->assertEquals($editrole,$newroles );
        $client->restart();  
        
        $client = $this->login($this,"hospitaluser", "password"); 
        $path="/patient/patient_handoff_dashboard";
        $crawler=$client->request('GET', $path);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $client->restart();

        $client = $this->login($this,"hospitaluser", "password"); 
        $hos=$this->getInfoFromDb("SETTING_PATIENT_INFORMATION", "HospitalSetting", "name","HospitalBundle",array(),true);
        if($hos){
          $handOffEnabled=$hos[0]->getHospital()->getSetting('patient-information');
          $this->assertEquals('patient-information', $handOffEnabled);
          $client->restart();
        }

        $client = $this->login($this,"hospitaladmin", "password"); 
        $hospitalUser =$this->getInfoFromDb("hospitaluser","user",'username')[0]['id'];
        $crawler = $client->request('GET', '/users/roles/' . $hospitalUser);
        $form = $crawler->selectButton("form_Save")->form();
        $form['form[RolePatientHandoffAdmin]']->untick();
        $client->submit($form, array());
        $client->restart(); 
    }

    /**
     * This method tests if we are getting expected count of patients not in this agency
     */
    //public function testGetNonAgencyPatientList(){
        // $path = "/ajax/patient/ajax_non_agency_patients/8";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertGreaterThan(0, count($patient_list));
        // $client->restart();
    //}

    /**
     *  This tests if we are getting expected list of patients for this agency
     */
    //public function testGetAgencyPatientList(){
        // $path = "/ajax/patient/ajax_agency_patients/8";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();
    //}

    /**
     * Added test to check add/switch patients from an agency
     */
    public function testAddSwitchPatientsToAgency(){

        //First make sure that agency has no patients
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();

        //Add one patient
        $path = "/patient/patient_handoff_dashboard";
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path, array('action'=>'Add', 'patient_id'=>array(1), 'phygroup'=>9));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Patient assignments for AgencyWithoutCatgory are complete.")')->count());
        $client->restart();

        //Check that patient has been added
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(1, count($patient_list));
        // $client->restart();

        //Now check that agency does not have patients
        // $path = "/ajax/patient/ajax_agency_patients/7";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();

        //Now switch patients from one agency to other
        $path = "/patient/patient_handoff_dashboard";
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path, array('action'=>'Switch', 'fromgroup'=>9, 'togroup'=>7));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertGreaterThan(0, $crawler->filter( 'html:contains("1 patient from: AgencyWithoutCatgory has been transferred to: secondagency")')->count());
        $client->restart();

        //Now check if donor agency has 0 patients
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();

        //Check if target agency has patients
        // $path = "/ajax/patient/ajax_agency_patients/8";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(1, count($patient_list));
        // $client->restart();
    }

    public function testRemovePatientsFromAgency(){
        //Ensure that there are no patients in agency
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();

        //Add a patient
        $path = "/patient/patient_handoff_dashboard";
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path, array('action'=>'Add', 'patient_id'=>array(1), 'phygroup'=>9));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertGreaterThan(0, $crawler->filter( 'html:contains("Patient assignments for AgencyWithoutCatgory are complete.")')->count());
        $client->restart();

        //Verify that agency has patients
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(1, count($patient_list));
        // $client->restart();

        //No remove patients
        $path = "/patient/patient_handoff_dashboard";
        $client = $this->login($this,"hospitaladmin", "password");
        $client->followRedirects(false);
        $crawler = $client->request('POST', $path, array('action'=>'Remove', 'remove_id'=>array(1), 'phygroup'=>9));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertGreaterThan(0, $crawler->filter( 'html:contains("Selected patients have been removed from AgencyWithoutCatgory")')->count());
        $client->restart();

        //Verify that patients have indeed gotten removed
        // $path = "/ajax/patient/ajax_agency_patients/9";
        // $client = $this->login($this,"hospitaladmin", "password");
        // $client->followRedirects(false);
        // $client->request('GET', $path);
        // $this->assertTrue($client->getResponse()->isOk());
        // $patient_list = json_decode($client->getResponse()->getContent());
        // $this->assertTrue(is_array($patient_list));
        // $this->assertEquals(0, count($patient_list));
        // $client->restart();
    }
 
    /**
      * Added test patient reset on patient Engagement without agency patient.
      */
    public function testResetPatientsEngagement(){
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->_container->get('doctrine')->getManager()->clear();
    
        $headers = [
            'HTTP_X-UDID' => '1234',
            'HTTP_X-UDID7' => '1234',
            'HTTP_X-ACCESSCODE' => '7UHX5HDY',
            'HTTP_X-APPCLIENT' => 'iPhone',
            'HTTP_X-HID' => 27,
            'HTTP_X-PUSHTOKEN' => 'dummypushtoken'
        ];
        
        
        $this->doSql('insert into physician_patient (physician_id, patient_id) values (31, 2)');
        $this->doSql('update  patient set last_seen_date = UTC_TIMESTAMP() where id = 2');
        
        $this->doSql("insert into  hospital_setting (hospital_id,name,val) values (27,'patient-information','X')");

        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/markallpatientsunseen', [], [], $headers);
        $response = $client->getResponse();

        $this->doSql('delete from physician_patient where patient_id = 2');
        $this->doSql("delete from hospital_setting where name = 'patient-information' and  val = 'X'" );

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        
        $patients = $data['response'];
        $patient = $patients[0];

        $this->assertGreaterThan(0, count($patients));
        $this->assertEquals(NULL, $patient['last_seen_date']);
        $this->assertArrayHasKey('birthdate', $patient, "Does patient data have an birthdate key?");
        $this->assertArrayHasKey('last_updated_by', $patient, "Does patient data have an last_updated_by key?");
    }

    
    /**
      * Added test patient reset on patient Engagement with agency patient.
      */
    public function testResetPatientsWithAgencyPatientEngagement(){
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->_container->get('doctrine')->getManager()->clear();

         $headers = [
            'HTTP_X-UDID' => '1234',
            'HTTP_X-UDID7' => '1234',
            'HTTP_X-ACCESSCODE' => '7UHX5HDY',
            'HTTP_X-APPCLIENT' => 'iPhone',
            'HTTP_X-HID' => 27,
            'HTTP_X-PUSHTOKEN' => 'dummypushtoken'
        ];
        

        $this->doSql("insert into  hospital_setting (hospital_id,name,val) values (27,'patient-information','X')");
        $this->doSql('update physician set agency_id = 3 where id = 31');
        $this->doSql('insert into physician_patient (physician_id, patient_id) values (31, 2)');
        $this->doSql('insert into agency_patient (agency_id, patient_id, createdAt) values (3, 3, UTC_TIMESTAMP())'); 

        $this->doSql('update  patient set last_seen_date = UTC_TIMESTAMP() where id in (2,3)');
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/markallpatientsunseen', [], [], $headers);
        $response = $client->getResponse();

        $this->doSql('update  physician set agency_id = NULL where id = 31');
        $this->doSql('delete from physician_patient where patient_id = 2');
        $this->doSql("delete from hospital_setting where name = 'patient-information' and  val = 'X'" );
        $this->doSql('delete from agency_patient where agency_id = 3 and patient_id = 3'); 

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);

        $patients = $data['response'];
        $patient  = $patients[0];
        
        $this->assertGreaterThan(0, count($patients));
        $this->assertEquals(NULL, $patient['last_seen_date']);
        $this->assertArrayHasKey('birthdate', $patient, "Does patient data have an birthdate key?");
        $this->assertArrayHasKey('last_updated_by', $patient, "Does patient data have an last_updated_by key?");   
    }

    /**
      * Added test to get the admin groups in security groups
      */
    public function testAdminGroupsInSecurityGroups(){
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->_container->get('doctrine')->getManager()->clear();

        // Test the hospital if patient handoff is not enabled
         $headers = [
            'HTTP_X-UDID' => '999',
            'HTTP_X-ACCESSCODE' => 'YVFGA9YW',
            'HTTP_X-APPCLIENT' => 'Android',
            'HTTP_X-HID' => 33,
            'HTTP_X-PUSHTOKEN' => 'TESTPUSH7'
        ];
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/admingroupsinsecuritygroups', [], [], $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data, "Response should have error key");
        $this->assertEquals("Unable to get physician group. Patient handoff not enabled", $data['error']);
        $client->restart();

        // Update hospital settings with enabling patient handoff
        $this->doSql("UPDATE hospital_setting SET val = '{\"PATIENT DETAILS\": { \"fields\": [{\"id\": \"1\",\"type\": \"text\",\"label\": \"First Name\",\"map\": \"first_name\",\"edit\": \"N\"}]}}' WHERE id = 18;");

        // Test the hospital with patient off is enabled but don't have admin groups
        $headers = [
            'HTTP_X-UDID' => '1234',
            'HTTP_X-ACCESSCODE' => '7UHX5HDY',
            'HTTP_X-APPCLIENT' => '',
            'HTTP_X-HID' => 27,
            'HTTP_X-PUSHTOKEN' => 'dummypushtoken'
        ];
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/admingroupsinsecuritygroups', [], [], $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $data, "Is there any response key?");
        $this->assertArrayHasKey('request_completed_at', $data, "Is there any request completed at key?");
        $this->assertEquals("Unable to fetch admin groups", $data['response']);
        $client->restart();

        // Add hospital user record into the devices
        $this->doSql("INSERT INTO `device` (`id`, `user`, `physician_id`, `uid`, `push_token`, `app_client`, `app_id`, `build`, `created_at`, `updated_at`, `device_name`, `pru_token`, `pru_token_expires_at`, `public_key`, `last_sync_date`, `last_login_at`) VALUES (NULL, '94', '34', '12345', '3456', NULL, NULL, NULL, '2016-10-17 00:00:00', '2016-10-17 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL);");
        
        // Test the hospital with patient offi is enabled but don't have admin groups
        $headers = [
            'HTTP_X-UDID' => '12345',
            'HTTP_X-ACCESSCODE' => '5A23V9Z7',
            'HTTP_X-APPCLIENT' => '',
            'HTTP_X-HID' => 27,
        ];
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/admingroupsinsecuritygroups', [], [], $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $data, "Is there any response key?");
        $this->assertArrayHasKey('request_completed_at', $data, "Is there any request completed at key?");
        $adminGroup = $data['response'][0];
        $this->assertEquals(11, $adminGroup['id']);
        $this->assertEquals("adminGroup", $adminGroup['name']);
        $client->restart();
    }

    /**
      * Added test to get the patients list filter by admin group
      */
    public function testPatientsByAdminGroup(){
        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->_container->get('doctrine')->getManager()->clear();

        // Test the hospital if patient handoff is not enabled
         $headers = [
            'HTTP_X-UDID' => '999',
            'HTTP_X-ACCESSCODE' => 'YVFGA9YW',
            'HTTP_X-APPCLIENT' => 'Android',
            'HTTP_X-HID' => 33,
            'HTTP_X-PUSHTOKEN' => 'TESTPUSH7'
        ];
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/patientsbyadmingroup', [], [], $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data, "Response should have error key");
        $this->assertEquals("Unable to get physician group. Patient handoff not enabled", $data['error']);
        $client->restart();

        // Update hospital settings with enabling patient handoff
        $this->doSql("UPDATE hospital_setting SET val = '{\"PATIENT DETAILS\": { \"fields\": [{\"id\": \"1\",\"type\": \"text\",\"label\": \"First Name\",\"map\": \"first_name\",\"edit\": \"N\"}]}}' WHERE id = 18;");
        
        $hospitalId = 27;

        // Test the hospital with patient off is enabled but didn't send the group id
        $headers = [
            'HTTP_X-UDID' => '1234',
            'HTTP_X-ACCESSCODE' => '7UHX5HDY',
            'HTTP_X-APPCLIENT' => '',
            'HTTP_X-HID' => $hospitalId,
            'HTTP_X-PUSHTOKEN' => 'dummypushtoken'
        ];
        
        $client = static::createClient();
        $client->request('POST', '/if-v2/patient/patientsbyadmingroup', [], [], $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data, "Response should have error key");
        $this->assertEquals("Unable to fetch patients. Invalid group id.", $data['error']);
        $client->restart();

        // Add hospital user record into the devices
        $this->doSql("INSERT INTO `device` (`id`, `user`, `physician_id`, `uid`, `push_token`, `app_client`, `app_id`, `build`, `created_at`, `updated_at`, `device_name`, `pru_token`, `pru_token_expires_at`, `public_key`, `last_sync_date`, `last_login_at`) VALUES (NULL, '94', '34', '12345', '3456', NULL, NULL, NULL, '2016-10-17 00:00:00', '2016-10-17 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL);");
        
        // Test the hospital with patient offi is enabled but don't have admin groups
        $headers = [
            'HTTP_X-UDID' => '12345',
            'HTTP_X-ACCESSCODE' => '5A23V9Z7',
            'HTTP_X-APPCLIENT' => '',
            'HTTP_X-HID' => $hospitalId,
        ];
        
        $client = static::createClient();
        $client->request('POST', 
                '/if-v2/patient/patientsbyadmingroup', 
                array(
                  'group_id' => json_encode(11)
                ), 
                [], 
                $headers);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $data   = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $data, "Is there any response key?");
        $this->assertArrayHasKey('request_completed_at', $data, "Is there any request completed at key?");
        $patientList = $data['response'][0];
        $this->assertEquals($hospitalId, $patientList['hospital_id']);
        $this->assertEquals("patient", $patientList['first_name']);
        $this->assertEquals("pat", $patientList['last_name']);
        $client->restart();
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


