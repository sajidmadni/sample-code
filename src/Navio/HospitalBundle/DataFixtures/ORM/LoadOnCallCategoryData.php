<?php
/**
 * User: robertfaulkner
 * Date: 13/01/15
 * Time: 12:08
 */

namespace Navio\HospitalBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Navio\HospitalBundle\Entity\OnCallCategory;

class LoadOnCallCategoryData implements FixtureInterface{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // Find a test hospitals
        $hospitalRepo = $manager->getRepository('HospitalBundle:Hospital');
        $hospitalList = $hospitalRepo->findAll();
        foreach($hospitalList as $hospital){
            $onCallCategory = new OnCallCategory();
            $onCallCategory->setName('Radiology');
            $onCallCategory->setHospital($hospital);
            $manager->persist($onCallCategory);

            $onCallCategory = new OnCallCategory();
            $onCallCategory->setName('Cardiology');
            $onCallCategory->setHospital($hospital);
            $manager->persist($onCallCategory);

            $onCallCategory = new OnCallCategory();
            $onCallCategory->setName('Oby');
            $onCallCategory->setHospital($hospital);
            $manager->persist($onCallCategory);
        }
        $manager->flush();
    }
}