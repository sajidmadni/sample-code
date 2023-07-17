<?php
use Navio\HospitalBundle\Entity\Physician;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: nandayemparala
 * Date: 6/22/16
 * Time: 12:13 PM
 */

class PhysicianRandomPinTest extends TestCase {

    public function testRandomPin(){
        $this->assertEquals(4, Physician::randPin(1, array(1,2,3,5,6,7,8,9)));
    }

    public function testRandomPinWithStringExclusions(){
        $this->assertEquals(4, Physician::randPin(1, array('1','2','3','5','6','7','8','9')));
    }

}
