<?php
/**
 * Services for patients controllers
 * 
 * PHP version 7
 * 
 * @category  PHP
 * @package   Patients
 * @author    Eseosa Eriamiatoe <eseosa.eriamiatoe@uniphyhealth.com>
 * @copyright 2018 Uniphy Health LLC
 * @license   check with Uniphy Health.
 * @link      https://uniphyhealth.com
 */

namespace Navio\HospitalBundle\Service;

use Doctrine\ORM\EntityManager;
use Navio\HospitalBundle\Entity\Patient;
use Navio\Utils\Utils;
use PracticeUnite\CoreBundle\Entity\NHBaseDeletable;
use Navio\HospitalBundle\Entity\Physician;
use Psr\Container\ContainerInterface;
use PDO;


/**
 * Services for patients controllers
 * 
 * PHP version 7
 * 
 * @category  PHP
 * @package   Patients
 * @author    Eseosa Eriamiatoe <eseosa.eriamiatoe@uniphyhealth.com>
 * @copyright 2018 Uniphy Health LLC
 * @license   check with Uniphy Health.
 * @link      https://uniphyhealth.com
 */
class PatientService
{

    private $_em;
    private $_container;

    /**
     * __construct
     *
     * @param EntityManager      $entityManager The EntityManager is the central access point to ORM functionality
     * @param ContainerInterface $container     Describes the interface of a container that exposes methods to read its entries.
     * 
     * @return void
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->_em        = $entityManager;
        $this->_container = $container;
    }


    /**
     * Service function to update patient as seen.
     * 
     * @param string    $patientId Id of patient to be updated
     * @param Physician $physician Physician - Actually Staff member -
     * 
     * @return array
     */
    public function updatePatientAsSeen(string $patientId, Physician $physician)
    {
        $response = array();

        $patientRepo = $this->_em->getRepository('HospitalBundle:Patient');
        $patient     = $patientRepo->find($patientId);

        if (!isset($patient)) {
            $response['error'] = 'Patient not found.';
            return $response;
        }

        if ($patient->getDeletedAt() != null) {
            $res['error'] = 'Patient has been deleted or unavailable.';
            return $res;
        }

       
        try {
            $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
            $patient->setLastUpdatedBy($physician);
            $patient->setUpdatedAt($currentDate);
            $patient->setLastSeenDate($currentDate);
            $this->_em->persist($patient);
            $this->_em->flush();
            return $patient->toFullJson();
        }
        catch (\Exception $e){
            syslog(LOG_ERR, 'Update patient as seen Status Error: '. $e->getMessage());
            $response['error'] = 'Update patient as seen Status Error: '. $e->getMessage();
            return  $response;
        }
        return $response;
    }

    

    /**
     * Service function to update all physician's patients as not seen.
     *
     * @param Physician $physician Physician - Actually Staff member -
     * 
     * @return array
     */
    public function resetAllPatientsAsNotSeen(Physician $physician)
    {
        $response = array();
        try {
            $patientRepo = $this->_em->getRepository("HospitalBundle:Patient");
            return $patientRepo->resetAllStaffPatientsAsUnseen($physician);
            
        }
        catch (\Exception $e){
            syslog(LOG_ERR, 'Update patient as seen Status Error: '. $e->getMessage());
            $response['error'] = 'Update patient as seen Status Error: ';
            return  $response;
        }
        return $response;
    }




    



    

    

}