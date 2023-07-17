<?php
use Navio\HospitalBundle\Tests\Controller\TestConfig;

/**
 * Created by PhpStorm.
 * User: nandayemparala
 * Date: 6/22/16
 * Time: 12:13 PM
 */

class UpdateMessageTest extends TestConfig {
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
    
    public function setUp() :void{
        $this->setUpContainer();
        $this->repo = $this->_container->get('doctrine')->getManager()->getRepository('HospitalBundle:UpdateMessage');
        $this->doSql("TRUNCATE update_message;");        
        $this->doSql("INSERT INTO update_message (hospital_id, device_name, version,msg) VALUES (27,'iPhone','v1.2.3','yo ho yo ho a pirate''s life for me');");
        $this->doSql("INSERT INTO update_message (hospital_id, device_name, version,msg) VALUES (NULL,'iPhone','v1.2.3','aye matey');");
        $this->doSql("INSERT INTO update_message (hospital_id, device_name, version,msg) VALUES (NULL,'Android','v1.2.3','Wednesday, September 19 is talk like a pirate day');");
    }
    
    public function testUpdateMessage4iPHosp(){
        $this->assertEquals("yo ho yo ho a pirate's life for me", $this->repo->search4Msg(27,'iPhone','v1.2.3'));
    }
    
    public function testUpdateMessage4iPHospNull(){
        $this->assertEquals("aye matey", $this->repo->search4Msg(31,'iPhone','v1.2.3'));
    }
    public function testUpdateMessage4AndroidHospNull(){
        $this->assertEquals('Wednesday, September 19 is talk like a pirate day', $this->repo->search4Msg(27,'Android','v1.2.3'));
    }
    public function testUpdateMessage4NotVersion(){
        $this->assertEquals(null, $this->repo->search4Msg(27,'iPhone','v1.2'));
    }
    public function testUpdateMessage4NotVersionSameHosp(){
        $this->assertEquals(null, $this->repo->search4Msg(27,'iPhone','v1.2'));
    }
    public function testUpdateMessage4OtherHosp(){
        $this->assertEquals(null, $this->repo->search4Msg(27,'iPhone','v1.2'));
    }
}
