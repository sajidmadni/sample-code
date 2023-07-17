<?php

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class CreateUser2CommandTest extends \Navio\EngagementBundle\Tests\PrU\baseWebDB {
    public static function setUpBeforeClass():void
    {
        if (strpos(DB_DSN, "pu_test") !== FALSE) {
            parent::loadFixtures([__DIR__ . '/../../../HospitalBundle/Tests/_files/Fixture.sql']);
        } else {
            echo 'Cannot run phpunit tests on non test/ dev databases', PHP_EOL;
            exit;
        }
    }

    /*
     * general setup of container
     */
    public function setUp():void
    {

        global $kernel;
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->_container = $kernel->getContainer();
    }

    /**
     * This function tests creation of a user in a test hospital
     */
    public function testPhysicianCreate(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);

        $test_hospital = 27;
        $epoch_date = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00', new \DateTimeZone("UTC"));

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"phpunit_user",
            'email'=>'phpunit_user@test.com',
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital
        ));
        $this->assertStringContainsString("Created phpunit_user", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>'phpunit_user@test.com'));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals('phpunit_user', $physician->getDocNumber(), 'Verify doc number is set as username');
    }

    /**
     * This command tests creation of user with back dated creation time
     */
    public function testCreationOfBackDatedUser(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = 'phpunit_back_user@test.com';
        $epoch_date = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00', new \DateTimeZone("UTC"));

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"phpunit_back_user",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--epoch_create_date'=>1,
            '--skip_update_date'=>1,
            '--hospitalnumber'=>$test_hospital
        ));
        $this->assertStringContainsString("Created phpunit_back_user", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($epoch_date, $physician->getCreatedAt(), 'Verify creation date is set to epoch date');
        $this->assertEquals($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is  set to epoch date');
    }

    /**
     * This function tests updation of user without flagging any special created_at, updated_at date manipulations
     */
    public function testUpdationOfExistingUser(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = 'madeup@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"Alice",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Alice',
            'lastname'=>'Johnsons',
            '--hospitalnumber'=>$test_hospital
        ));
        $this->assertStringContainsString("Updated Alice", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date Remains unchanged');
        $this->assertLessThan($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date changes');
    }


    /**
     * Verify that user's created at and updated at does not change when appropriate flags are passed to it
     */
    public function testUpdationOfExistingUserSuppressDateChanges(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = '021531@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"David",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'David',
            'lastname'=>'Thornton',
            '--epoch_create_date'=>1,
            '--skip_update_date'=>1,
            '--hospitalnumber'=>$test_hospital
        ));
        $this->assertStringContainsString("Updated David", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date remains unchanged');
        $this->assertEquals($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date remains unchanged');
    }



    /**
     * Create another user with roles. make sure fos_user updated. 
     */
    public function testNewUserWithRoles(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = '021261@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository    =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);

        // $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        // $existing_created_at = $existing_physician->getCreatedAt();
        // $existing_updated_at = $existing_physician->getUpdatedAt();

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"David",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'David',
            'lastname'=>'Thornton',
            '--hospitalnumber'=>$test_hospital,
            '--roles' => 'AAA,BBB,CCC'
        ));
        $this->assertStringContainsString("Created David David Thornton", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $fosUser = $physician->getUser();
        $roles = $fosUser->getRoles();

        $this->assertIsArray($roles,' Verify Roles is array');
        $this->assertIsArray($roles,' Verify Roles is array');
        foreach (['AAA','BBB','CCC','ROLE_USER'] as $role) {
            $this->assertTrue(in_array($role,$roles)," Verify Roles has {$role} value");
        }
        
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
    }

    /**
     * Update another existing user with roles. make sure fos_user updated. 
     */
    public function testUpdateUserWithRoles(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = '021261@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository    =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);

        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        // put in OLD time.
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-01-01 12:07:00', new \DateTimeZone("UTC"));
        $existing_physician->setCreatedAt($time);
        $existing_physician->setUpdatedAt($time);
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();
        $em->persist($existing_physician);
        $em->flush();

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"David",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Paul',
            'lastname'=>'Gleason',
            '--hospitalnumber'=>$test_hospital,
            '--roles' => 'AAA,BBB,CCC,DDD'
        ));
        $this->assertStringContainsString("Updated David", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $fosUser = $physician->getUser();
        $roles = $fosUser->getRoles();

        $this->assertIsArray($roles,' Verify Roles is array');
        $this->assertIsArray($roles,' Verify Roles is array');
        foreach (['AAA','BBB','CCC','ROLE_USER'] as $role) {
            $this->assertTrue(in_array($role,$roles)," Verify Roles has {$role} value");
        }
        
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date remains unchanged');
        $this->assertNotEquals($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date remains unchanged');
    }

    // DELETE and Resurrect User. 
    /**
     * Update another existing user with roles. make sure fos_user updated. 
     */
    public function testDeletedUserWithRoles(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = '021261@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository    =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);

        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        // put in OLD time.
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-01-01 12:07:00', new \DateTimeZone("UTC"));
        $existing_physician->setCreatedAt($time);
        $existing_physician->setUpdatedAt($time);
        $existing_physician->setDeletedAt($time);
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();
        $existing_user = $existing_physician->getUser();
        $existing_user->setPassword( "*NOLogin*.deleted.");
        $existing_user->setUsername( $existing_user->getUsername().".deleted.");
        $existing_user->setEmail( $existing_user->getEmail().".deleted.");
        $em->persist($existing_physician);
        $em->persist($existing_user);
        $em->flush();

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>"David",
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Paul',
            'lastname'=>'Gleason',
            '--hospitalnumber'=>$test_hospital,
            '--roles' => 'AAA,BBB,CCC,DDD'
        ));
        $this->assertStringContainsString("Updated David", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $fosUser = $physician->getUser();
        $roles = $fosUser->getRoles();

        $this->assertIsArray($roles,' Verify Roles is array');
        $this->assertIsArray($roles,' Verify Roles is array');
        foreach (['AAA','BBB','CCC','ROLE_USER'] as $role) {
            $this->assertTrue(in_array($role,$roles)," Verify Roles has {$role} value");
        }
        
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date remains unchanged');
        $this->assertNotEquals($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date remains unchanged');
        $this->assertNull($physician->getDeletedAt(),  'Verify deleted at date is null');
    }

    // DELETE and Resurrect User. 
    /**
     * Update another existing user with roles. make sure fos_user updated. 
     */
    public function testDeletedUserWithoutRoles(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = __LINE__.'-0912@example.com';
        $test_user_username = 'Goliath'.__LINE__;

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository    =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=> $test_user_username,
            'email'=>    $test_user_email,
            'password'=>'test123$',
            'firstname'=>'Paul',
            'lastname'=>'Gleason',
            '--hospitalnumber'=>$test_hospital,
        ));
        $this->assertStringContainsString("Created $test_user_username", $commandTester->getDisplay(), 'Verify that a new physician gets created');

        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNull($existing_physician->getUser(),"New User should not have a fos user");
        $em->clear();
        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));

        //Delete it
        // put in OLD time.
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-01-01 12:07:00', new \DateTimeZone("UTC"));
        $existing_physician->setCreatedAt($time);
        $existing_physician->setUpdatedAt($time);
        $existing_physician->setDeletedAt($time);
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();
        $em->persist($existing_physician);
        $em->flush();

        //Now create again... 

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=> $test_user_username,
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Burt',
            'lastname'=>'Reynolds',
            '--hospitalnumber'=>$test_hospital
        ));
        $this->assertStringContainsString("Updated $test_user_username", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $fosUser = $physician->getUser();

        $this->assertNull($fosUser, 'Verify that physician does not have fos user');        
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date remains unchanged');
        $this->assertNotEquals($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date remains unchanged');
        $this->assertNotNull($physician->getDeletedAt(),  'Verify deleted at date is null');
    }    

    // DELETE and Resurrect User. 
    /**
     * Update another existing user with roles. make sure fos_user updated. 
     */
    public function testDeletedUserWithNewRoles(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = __LINE__.'-0912@example.com';
        $test_user_username = 'Goliath'.__LINE__;

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository    =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=> $test_user_username,
            'email'=>    $test_user_email,
            'password'=>'test123$',
            'firstname'=>'Paul',
            'lastname'=>'Gleason',
            '--hospitalnumber'=>$test_hospital,
        ));
        $this->assertStringContainsString("Created $test_user_username", $commandTester->getDisplay(), 'Verify that a new physician gets created');

        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNull($existing_physician->getUser(),"New User should not have a fos user");
        $em->clear();
        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));

        //Delete it
        // put in OLD time.
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-01-01 12:07:00', new \DateTimeZone("UTC"));
        $existing_physician->setCreatedAt($time);
        $existing_physician->setUpdatedAt($time);
        $existing_physician->setDeletedAt($time);
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();
        $em->persist($existing_physician);
        $em->flush();

        //Now create again... 

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=> $test_user_username,
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Paul',
            'lastname'=>'Gleason',
            '--hospitalnumber'=>$test_hospital,
            '--roles' => 'AAA,BBB,CCC,DDD'
        ));
        $this->assertStringContainsString("Updated $test_user_username", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $fosUser = $physician->getUser();
        $roles = $fosUser->getRoles();

        $this->assertIsArray($roles,' Verify Roles is array');
        $this->assertIsArray($roles,' Verify Roles is array');
        foreach (['AAA','BBB','CCC','ROLE_USER'] as $role) {
            $this->assertTrue(in_array($role,$roles)," Verify Roles has {$role} value");
        }
        
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date remains unchanged');
        $this->assertNotEquals($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date remains unchanged');
        $this->assertNotNull($physician->getDeletedAt(),  'Verify deleted at date is null');
    }

    /**
     * This command tests creation of user with back dated creation time
     */
    public function testSkipDocNumberForNewUser(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);

        $test_hospital = 27;
        $epoch_date = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00', new \DateTimeZone("UTC"));

        $username = 'phpunit'.__LINE__;
        $email = 'phpunit_tester@uh'.__LINE__.'.com';

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username,
            'email'=>$email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--no_docnumber_substitute'=>1
        ));
        $this->assertStringContainsString("Created $username", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals('', $physician->getDocNumber(), 'Verify doc number is not set as username');


        // test no_doc_number flag with docnumber value set
        $username2 = 'phpunit'.__LINE__;
        $email2 = 'phpunit_tester@uh'.__LINE__.'.com';
        $docNumber = 'docNumber'.__LINE__;

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username2,
            'email'=>$email2,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--docnumber' => $docNumber,
            '--no_docnumber_substitute'=>1
        ));
        $this->assertStringContainsString("Created $username2", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email2));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($docNumber, $physician->getDocNumber(), 'Verify doc number is set');


        // test without no_docnumber_substitute with docnumber value
        $username2 = 'phpunit'.__LINE__;
        $email2 = 'phpunit_tester@uh'.__LINE__.'.com';
        $docNumber = 'docNumber'.__LINE__;

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username2,
            'email'=>$email2,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--docnumber' => $docNumber,
        ));
        $this->assertStringContainsString("Created $username2", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email2));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($docNumber, $physician->getDocNumber(), 'Verify doc number is set');

        // test with flag = 0
        $username2 = 'phpunit'.__LINE__;
        $email2 = 'phpunit_tester@uh'.__LINE__.'.com';
        $docNumber = 'docNumber'.__LINE__;

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username2,
            'email'=>$email2,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--docnumber' => $docNumber,
            '--no_docnumber_substitute'=>1
        ));
        $this->assertStringContainsString("Created $username2", $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email2));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($docNumber, $physician->getDocNumber(), 'Verify doc number is set');
    }


    public function testSkipDocNumberForExistingUser(){
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        date_default_timezone_set("UTC");

        $test_hospital = 27;
        $test_user_email = 'madeup@example.com';

        $em = $this->_container->get('doctrine')->getManager('default');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $existing_physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $existing_created_at = $existing_physician->getCreatedAt();
        $existing_updated_at = $existing_physician->getUpdatedAt();
        $existing_doc_number = $existing_physician->getDocNumber();
        $username = "Alice";

        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username,
            'email'=>$test_user_email,
            'password'=>'test123$',
            'firstname'=>'Alice',
            'lastname'=>'Johnsons'.__LINE__,
            '--hospitalnumber'=>$test_hospital,
            '--no_docnumber_substitute'=>1
        ));
        $this->assertStringContainsString("Updated Alice", $commandTester->getDisplay(), 'Verify that a new physician gets updated');
        $em->clear();

        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$test_user_email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertEquals($physician->getCreatedAt(),$existing_created_at,  'Verify creation date Remains unchanged');

        $this->assertLessThan($physician->getUpdatedAt(), $existing_updated_at,  'Verify Updated date changes');
        $this->assertEquals($existing_doc_number, $physician->getDocNumber(), 'docNumber should not change');
    }


    public function testNoDocNumberSubstituteMultiValues()
    {
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $application = new Application($kernel);
        $em = $this->_container->get('doctrine')->getManager('default');

        $test_hospital = 27;
        $epoch_date = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00', new \DateTimeZone("UTC"));
        $username = "doc_num_test".__LINE__;
        $email = 'phpunit_user@test.com'.__LINE__;
        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username,
            'email'=>$email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--no_docnumber_substitute'=>" 0 "
        ));
        $this->assertStringContainsString("Created ".$username, $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($username, $physician->getDocNumber(), 'Verify doc number is set as username');

        $username = "doc_num_test".__LINE__;
        $email = 'phpunit_user@test.com'.__LINE__;
        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$username,
            'email'=>$email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--no_docnumber_substitute'=>"   asdf"
        ));
        $this->assertStringContainsString("Created ".$username, $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($username, $physician->getDocNumber(), 'Verify doc number is set as username');


        $userName = 'username-test'.__LINE__;
        $docNumber = "doc_num_test".__LINE__;
        $email = 'phpunit_user@test.com'.__LINE__;
        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$userName,
            'email'=>$email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--docnumber' => $docNumber,
            '--hospitalnumber'=>$test_hospital,
        ));
        $this->assertStringContainsString("Created ".$userName, $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($docNumber, $physician->getDocNumber(), 'Verify doc number is set as docNumber given');

        $userName = 'username-test'.__LINE__;
        $email = 'phpunit_user@test.com'.__LINE__;
        $create_user_command = $application->find('navio:user:create2');
        $commandTester = new CommandTester($create_user_command);
        $commandTester->execute(array(
            'command' => $create_user_command->getName(),
            'username'=>$userName,
            'email'=>$email,
            'password'=>'test123$',
            'firstname'=>'phpunit_userfname',
            'lastname'=>'phpunit_userlname',
            '--hospitalnumber'=>$test_hospital,
            '--no_docnumber_substitute'=>" asd xcx"
        ));
        $this->assertStringContainsString("Created ".$userName, $commandTester->getDisplay(), 'Verify that a new physician gets created');
        $hospital_repository     =   $em->getRepository('HospitalBundle:Hospital');
        $physician_repository     =   $em->getRepository('HospitalBundle:Physician');
        $hospital = $hospital_repository->findOneById($test_hospital);
        $physician = $physician_repository->findOneBy(array('hospital'=>$hospital, 'email'=>$email));
        $this->assertNotNull($physician, 'Verify that physician indeed gets created');
        $this->assertGreaterThan($epoch_date, $physician->getCreatedAt(), 'Verify creation date is greater than epoch date');
        $this->assertGreaterThan($epoch_date, $physician->getUpdatedAt(), 'Verify Updated date is greater than epoch date');
        $this->assertEquals($userName, $physician->getDocNumber(), 'Verify doc number is set as username');

    }
}
