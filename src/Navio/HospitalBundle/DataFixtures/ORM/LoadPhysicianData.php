<?php
/**
 * User: robertfaulkner
 * Date: 22/01/15
 * Time: 13:42
 */

namespace Navio\HospitalBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Navio\HospitalBundle\Entity\Physician;
use Navio\HospitalBundle\Entity\User;

class LoadPhysicianData implements FixtureInterface{

    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        // Find a test hospitals
        $hospitalRepo = $manager->getRepository('HospitalBundle:Hospital');
        $hospitalList = $hospitalRepo->findAll();
        foreach($hospitalList as $hospital){
            for($t = 0; $t < 1000; $t++){
                $phy = new Physician();

                $first = substr(md5(uniqid(rand(), true)), 0, 8);
                $last = substr(md5(uniqid(rand(), true)), 0, 8);
                $email = "$first.$last@not_a_real_person.com";

                $phy->setFirstName($first);
                $phy->setLastName($last);
                $phy->setSpecialty('Random');
                $phy->setSubSpecialty('Test');
                $phy->setDepartment('Test Dept.');
                $phy->setCity('Sometown');
                $phy->setZip('123456789');
                $phy->setEmail($email);
                $phy->setOfficePhone((string)rand(10000, 99999));
                $phy->setCellPhone((string)rand(10000, 99999));
                $phy->setCellPhoneHidden(false);
                $phy->setOfficeAddressLine1("No. $t");
                $phy->setOfficeAddressLine2('Testville');

                $phy->setHospital($hospital);



                // Create the corresponding User
                $user=new User();

                $user->setLastnameFirstname("{$phy->getLastName()}, {$phy->getFirstName()}");
                $user->setType("Employee");
                $user->setEmployee($phy);
                $user->setHospital($hospital);
                $user->setUsername($email);
                $user->setEmail($phy->getEmail());
                $user->setEnabled(true);
                $user->setPassword($user->randstring(10));

                $phy->setUser($user);

                $manager->persist($phy);
                $manager->persist($user);
            }

        }
        $manager->flush();
    }
}