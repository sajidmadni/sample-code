<?php

namespace Navio\HospitalBundle\Tests\Service;

use Navio\SyncBundle\Services\RoleBasedLib;
use PracticeUnite\CoreBundle\Tests\Lib\PruTestConfig;

class RoleBasedLibTest  extends PruTestConfig {
        
//    protected function setUp():void {
//    }
//
//    public function __construct() {
//        
//    }
    
    /**
     * @internet group
     */
    public function testRolesLibByRole() {

        $o = new RoleBasedLib();
        
        // Let's check if 
        
        // Make sure empty values do not block
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                "", //allow
                "", //deny
                "" // user roles
                );
        $this->assertFalse($a,"Make sure  empty values do not block");
        
        // Make sure empty values do not block
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                "", //allow
                "", //deny
                NULL // user roles
                );
        $this->assertFalse($a,"Make sure NULL values do not block");
        
        // Make sure empty values do not block
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                NULL, //allow
                NULL, //deny
                NULL // user roles
                );
        $this->assertFalse($a,"Make sure NULL values do not block");
        
        // Make sure empty values do not block
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                NULL, //allow
                NULL, //deny
                NULL // user roles
                );
        $this->assertFalse($a,"Make sure empty values do not block");
        
        // Make sure empty values do not block
        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                NULL, //allow
                NULL, //deny
                NULL // user roles
                );
        $this->assertFalse($a,"Make sure empty values do not block - deny first should block");

        // Make sure empty values do not block
        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array()), //allow
                json_encode(array()), //deny
                json_encode(array()) // user roles
                );
        $this->assertTrue($a,"Make sure empty values do not block - deny first should block");

        // 
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array("NURSE","DOCTOR")), //allow list
                NULL, //deny
                json_encode(array("NURSE")) // user roles
                );
        $this->assertFalse($a,"If N&D allowed, N should be permitted. ");

        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array("NURSE","DOCTOR")), //allow list
                NULL, //deny
                json_encode(array("PA")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, PA should NOT be permitted. ");

        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array("NURSE","DOCTOR")), //allow list
                json_encode(array("NURSE")), //deny list
                json_encode(array("NURSE")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, but NURSE not allowed, NURSE should NOT be permitted. ");

        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array("NURSE","DOCTOR")), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");
        
        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                NULL, //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array()), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");


        $a = $o->allowDeny(
                false, // getDenyAllowOrder
                json_encode(array()), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If N&D allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array("XXX")), //allow list
                json_encode(array()), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If all allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array("CNA")), //allow list
                json_encode(array()), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertFalse($a,"If all allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        
        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array()), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If all allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array("XXX")), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertTrue($a,"If XXX allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");

        $a = $o->allowDeny(
                true, // getDenyAllowOrder
                json_encode(array("NURSE")), //allow list
                json_encode(array("CNA")), //deny list
                json_encode(array("NURSE","CNA")) // user roles
                );
        $this->assertFalse($a,"If N&D allowed, but CNA not allowed, NURSE&CNA should NOT be permitted. ");
        
        
        return;
        
    }
    
    public function testRolesLibLocation() {
        $o = new RoleBasedLib();
        
        // Let's check if 

        $a = $o->blockedLocation(
                json_encode(array("NYC")), // allowed locs
                null //my loc
                );
        $this->assertTrue($a,"No Match");
        
        $a = $o->blockedLocation(
                NULL, // allowed locs
                "NYC" //my loc
                );
        $this->assertFalse($a,"No Match");

        $a = $o->blockedLocation(
                json_encode(array("NYC")), // allowed locs
                "LIC" //my loc
                );
        $this->assertTrue($a,"No Match");
        
        $a = $o->blockedLocation(
                json_encode(array("NYC")), // allowed locs
                "NYC" //my loc
                );
        $this->assertFalse($a,"Makeone one city match");
        
        $a = $o->blockedLocation(
                json_encode(array("NYC","DC")), // allowed locs
                "NYC" //my loc
                );
        $this->assertFalse($a,"Makeone one city match");
        
        $a = $o->blockedLocation(
                json_encode(array("LIC","DC")), // allowed locs
                "NYC" //my loc
                );
        $this->assertTrue($a,"Makeone one city match");

    }
            


}
    
