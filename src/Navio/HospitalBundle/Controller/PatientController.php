<?php

namespace Navio\HospitalBundle\Controller;

use Navio\HospitalBundle\Service\Document;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Navio\HospitalBundle\Entity\PhysicianPatient;
use Navio\HospitalBundle\Entity\Patient;
use Navio\HospitalBundle\Entity\User;
use Navio\HospitalBundle\Entity\HospitalDiagnosis;
use Navio\HospitalBundle\Form\Type\PatientType;
use Navio\HospitalBundle\Entity\HospitalSetting;
use Symfony\Component\HttpFoundation\Response;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/patient")
 * @Template()
 * @Security("is_granted('ROLE_PRIV_USER')")
 */
class PatientController extends NHController {

    //listing the patients in hospital
    /**
     * @Route("/")
     * @Route("/list", name="patient_list")
     * @Template("HospitalBundle:Patient:patient_list.html.twig")
     */
    public function patientAction(Request $request) {
        $ctx = $this->get('security.authorization_checker');

        $em = $this->getDoctrine()->getManager();
        $patRepo = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        $search_term = $request->get('search_term');
        $pageLimit = $request->get('page_limit', 50);
        $offset = $request->get('offset', 0);
        $agency = null;
        if (!$ctx->isGranted(User::$ROLE_PRIV_VIEW_HOSP_PATIENTS)) {
            return $this->flashBack('notice', "You do not have permissions to view patient information", 'home');
        }

        if (!$ctx->isGranted(User::$ROLE_PRIV_VIEW_HOSP_PATIENTS)) {
            $userEmp = $this->loggedInUser()->getEmployee();
            if ($userEmp) {
                $agency = $userEmp->getAgency();
            }
        }

        if ($search_term) {
            $patients = $patRepo->findAllMatching($search_term, $this->loggedInUser()->getHospital(), $agency);
            $total_no_records = $patRepo->findAllMatching($search_term, $this->loggedInUser()->getHospital(), $agency, 0, false, true);
        } elseif (!$userHosp) {
            return $this->flashBack('notice', "Admins must login into hospital to make changes.", 'user_list');
        } elseif ($ctx->isGranted(User::$ROLE_PRIV_VIEW_HOSP_PATIENTS)) {
            $patients = $patRepo->findBy(array(
                'hospital' => $userHosp,
                'deletedAt' => NULL
                    ), array('lastName' => 'ASC')
                    , $pageLimit, $offset);
            $total_no_records = $patRepo->findAllMatching(null, $this->loggedInUser()->getHospital(), null, 0, false, true);
        } else {
            $patients = $patRepo->findBy(array(
                'agency' => $agency,
                'hospital' => $userHosp,
                'deletedAt' => NULL
                    ), array('lastName' => 'ASC')
                    , $pageLimit, $offset);
            $total_no_records = $patRepo->findAllMatching(null, $this->loggedInUser()->getHospital(), $agency, 0, false, true);
        }

        if ($patients === null) {
            return $this->flashBack('notice', 'could not find patients!');
        }

        return array(
            'record' => $patients,
            'search_term' => $search_term,
            'offset' => $offset,
            'pagelimit' => $pageLimit,
            'count' => count($patients),
            'total_no_records' => $total_no_records
        );
    }
    
    //listing all patients by agency
    /**
     * @Route("/")
     * @Route("/all", name="patient_all")
     * @Template("HospitalBundle:Patient:patient_all.html.twig")
     */
    public function patientAgencyUserAction(Request $request) {
        $ctx = $this->get('security.authorization_checker');

        $em = $this->getDoctrine()->getManager();
        $patRepo = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        if (!$userHosp) {
            return $this->flashBack('notice', "Admins must login into hospital to make changes.", 'user_list');
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $search_term = $request->get('search_term');
        $pageLimit = $request->get('page_limit', 50);
        $pcc = $userHosp->getSetting('allow_patient_centric_chat');
        if(!isset($pcc) || empty($pcc)){
            $pcc = 0;
        }
        $offset = $request->get('offset', 0);
        $agency = null;
        if (!$ctx->isGranted(User::$ROLE_HOSPITAL_USER)) {
            return $this->flashBack('notice', "You do not have permissions to view patient information", 'home');
        }

       // if (!$ctx->AuthorizationCheckerInterface->isGranted(User::$ROLE_HOSPITAL_USER)) {
            $userEmp = $this->loggedInUser()->getEmployee();
            if ($userEmp) {
                $agency = $userEmp->getAgency();
            }
        //}

        if ($search_term) {
            $patients = $patRepo->findAllAgPatMatching($search_term, $this->loggedInUser()->getHospital(), $agency, $this->loggedInUser()->getEmployee()->getId());
        } elseif ($ctx->isGranted(User::$ROLE_HOSPITAL_USER)) {
            if ($agency) {
            $q = $em->createQueryBuilder()
                ->from('HospitalBundle:Patient', 'pat')
                ->select('pat')
                ->join("HospitalBundle:PhysicianPatient", "physPat", "WITH", "( physPat.patient = pat )")
                ->join("HospitalBundle:Physician", "phys", "WITH", "( physPat.physician = phys )")
                ->andWhere('pat.hospital = :hosp')
                ->andWhere('pat.deletedAt IS NULL')
                ->andWhere('phys.agency = :ag')
                ->orderBy('pat.lastName')
                ->setParameter('hosp', $userHosp)
                ->setParameter('ag', $agency)
                ->getQuery()
            ;
            } else {
                $q = $em->createQueryBuilder()
                    ->from('HospitalBundle:Patient', 'pat')
                    ->select('pat')
                    ->join("HospitalBundle:PhysicianPatient", "physPat", "WITH", "( physPat.patient = pat )")
                    ->andWhere('pat.hospital = :hosp')
                    ->andWhere('pat.deletedAt IS NULL')
                    ->andWhere('physPat.physician = :phys')
                    ->orderBy('pat.lastName')
                    ->setParameter('hosp', $userHosp)
                    ->setParameter('phys', $user->getEmployee()->getId())
                    ->getQuery()
                ;
            }
            $patients = $q->getResult();
        } else {
            $patients = $patRepo->findBy(array(
                'agency' => $agency,
                'hospital' => $userHosp,
                'deletedAt' => NULL
            ), array('lastName' => 'ASC')
                , $pageLimit, $offset);
        }
        if (isset($agency) && ($this->loggedInUser()->getHospital()->getSetting(HospitalSetting::$SETTING_PATIENT_INFORMATION)) ) {

            $queryBuilder = $em->createQueryBuilder()
                ->select('hospPatients' )
                ->from('HospitalBundle:Patient', 'hospPatients')
                ->leftJoin('HospitalBundle:AgencyPatient', 'agPatients', 'WITH', 'hospPatients.id=agPatients.patient')
                ->where('hospPatients.hospital = :hospital')
                ->andWhere('hospPatients.deletedAt IS NULL')
                ->andWhere('agPatients.agency = :agency')
                ->orderBy('hospPatients.lastName', 'ASC')
                ->setParameter("hospital", $agency->getHospital())
                ->setParameter('agency', $agency);

            if (isset($search_term)){
                $queryBuilder->andWhere(' ( hospPatients.lastName like :search_term or hospPatients.firstName like :search_term or hospPatients.mrn like :search_term)');
                $queryBuilder->setParameter('search_term', '%'.$search_term.'%');
            }
            $query = $queryBuilder->getQuery();
            $agency_patients = $query->getResult();
            $patients = array_merge($patients, $agency_patients);
        }
        if ($patients === null) {
            return $this->flashBack('notice', 'could not find patients!');
        }
        return array(
            'record' => $patients,
            'search_term' => $search_term,
            'offset' => $offset,
            'pagelimit' => $pageLimit,
            'count' => count($patients),
            'pcc' => $pcc
        );
    }

    //listing my patients
    /**
     * @Route("/")
     * @Route("/mylist/{groupId}", name="patient_mylist")
     * @Template("HospitalBundle:Patient:patient_mylist.html.twig")
     */
    public function patientHospUserMyPatAction(Request $request,$groupId=null) {
        $ctx = $this->get('security.authorization_checker');
        date_default_timezone_set('UTC');
        $em = $this->getDoctrine()->getManager();
        $patRepo = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        if (!$userHosp) {
            return $this->flashBack('notice', "Admins must login into hospital to make changes.", 'user_list');
        }
        if (!$ctx->isGranted(User::$ROLE_HOSPITAL_USER)) {
            return $this->flashBack('notice', "You do not have permissions to view patient information", 'home');
        }

        $result = $userHosp->getSetting(HospitalSetting::$SETTING_PATIENT_INFORMATION);
        $handOffEnabled = "N";
        $patientInformation = "";
        try {
            $items = $result ? json_decode($result, true) : null;
            if ($items != null || $items != "") {
                $handOffEnabled = "Y";
                $patientInformation = $items;
            }
        } catch (Exception $e) {
            $handOffEnabled = "N";
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $search_term = $request->get('search_term');
        $pcc = $userHosp->getSetting('allow_patient_centric_chat');
        if(!isset($pcc) || $pcc == ''){
            $pcc = 0;
        }
        $physician = $user->getEmployee();
        // Check for sorting
        $sortBy = $request->get('sort_by');
        $withResultsOnly = $request->get('resultsOnly', false);
        $patients = $patRepo->loadPatientsForWeb($physician,$search_term,$groupId,$sortBy, $withResultsOnly);
        $myPatients = $request->get('my_patients');
        // Convert patient last seen date according to hospital timezone
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $hosp = $user->getHospital();
        $hospTimezone = $hosp->getTimezone();
        if($hospTimezone) {
            foreach ($patients as $key => $patient) {
                if(isset($patient['last_seen_date'])) {
                    $now = new \DateTime($patient['last_seen_date']);
                    $now->setTimezone(new \DateTimeZone("$hospTimezone"));
                    $patients[$key]['last_seen_date'] = $now->format('m/d/Y H:i A');
                }
            }
        }
        if($myPatients != null)
        {
            $filteredPatients = [];
            foreach ($patients as $patient)
            {
                if(in_array($physician->getId(), $patient['physicians']))
                {
                    $filteredPatients[] = $patient;
                }
            }
            $patients = $filteredPatients;
        }
        if ($patients === null) {
            return $this->flashBack('notice', 'could not find patients!');
        }
        $pgRepo = $this->getDoctrine()->getRepository('HospitalBundle:PhysicianGroup');
        $secGroupsOfUser = $pgRepo->getSecurityGroupsForPhysician($physician);
        $adminGroupsForPhysician = $pgRepo->getAdminGroupsForSecurityGroups($secGroupsOfUser);

        $arrayGrpIdNames = array_map(function ($ele) {
            return [
              'id' => $ele->getId(),
              'name' => $ele->getName()
            ];
        }, $adminGroupsForPhysician);

        //Adding to array and using the list view model because the twig view is used by view task and Todo list.
        $phyGrpRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
        $secGroups = $phyGrpRepo->getSecurityGroupsForPhysician($physician);
        $adminGroups = $phyGrpRepo->getAdminGroupsForSecurityGroups($secGroups,$physician);
        $teams='';
        foreach ($adminGroups as $adminGroup){
            $teams=$teams.$adminGroup->getName().", ";
        }
        $teams= substr($teams, 0,strlen($teams)-2);
        return array(
            'record' => $patients,
            'search_term' => $search_term,
            'count' => count($patients),
            'hand_off_enabled' => $handOffEnabled,
            'pcc' => $pcc,
            'title' => ($myPatients != null ? 'My Patients' : 'All Patients'),
            'myPatients' => ($myPatients != null ? 'Y':'N'),
            'admin_groups'=>$arrayGrpIdNames,
            'admin_group_selected'=>$groupId,
            'team' => ($myPatients != null ? $teams:''),
            'sortBy' => $sortBy,
            'baseURL' => $request->getSchemeAndHttpHost(),
            'patientInformation' => $patientInformation,
            'resultsOnly' => $withResultsOnly,
        );
    }

    //listing my patients
    /**
     * @Route("/info/pdf", name="patient_info_pdf")
     * @codeCoverageIgnore
     * @Template("HospitalBundle:Patient:patient_info_pdf.html.twig")
     */
    public function patientInformationPDFAction(Request $request) {
        //echo "POST Data <pre>";var_dump($request->request->all());die;
        $ctx = $this->get('security.authorization_checker');
        date_default_timezone_set('UTC');
        $em = $this->getDoctrine()->getManager();
        $patRepo = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        if (!$userHosp) {
            return $this->flashBack('notice', "Admins must login into hospital to make changes.", 'user_list');
        }
        if (!$ctx->isGranted(User::$ROLE_HOSPITAL_USER)) {
            return $this->flashBack('notice', "You do not have permissions to view patient information", 'home');
        }

        $result = $userHosp->getSetting(HospitalSetting::$SETTING_PATIENT_INFORMATION);
        $handOffEnabled = "N";
        try {
            $items = $result ? json_decode($result, true) : null;
            if ($items != null || $items != "") {
                $handOffEnabled = "Y";
            }
        } catch (Exception $e) {
            $handOffEnabled = "N";
        }
        if($handOffEnabled == "N"){
            return $this->flashBack('notice', "Patient handoff is not enabled", 'home');
        }

        // Get patients record
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $physician = $user->getEmployee();
        // Check for sorting
        $sortBy = $request->get('filtered_sort_by');
        $sortBy = ($sortBy == '') ? null : $sortBy;
        $groupId = $request->get('selected_group_id');
        $groupId = ($groupId == '') ? null : $groupId;
        $patients = $patRepo->getPhysicianPatientsForSync($physician,$groupId,$sortBy);
        $myPatients = $request->get('filtered_my_patients');
        // Convert patient last seen date according to hospital timezone
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $hosp = $user->getHospital();
        $hospTimezone = $hosp->getTimezone();
        if($hospTimezone) {
            foreach ($patients as $key => $patient) {
                if(isset($patient['last_seen_date'])) {
                    $now = new \DateTime($patient['last_seen_date']);
                    $now->setTimezone(new \DateTimeZone("$hospTimezone"));
                    $patients[$key]['last_seen_date'] = $now->format('m/d/Y H:i A');
                }
            }
        }
        if($myPatients != null && $myPatients == "Y") {
            $filteredPatients = [];
            foreach ($patients as $patient) {
                if(in_array($physician->getId(), $patient['physicians'])) {
                    $filteredPatients[] = $patient;
                }
            }
            $patients = $filteredPatients;
        }

        $filteredBy = "";
        $patientPDFData = array();
        if($groupId){
            $groupRep = $em->getRepository('HospitalBundle:PhysicianGroup')->findOneBy(array("id"=>$groupId));
            $filteredBy = $groupRep->getName();
        }
        if($patients){
            foreach ($patients as $patient){
                $patientPDFData[$patient["id"]]["id"] = $patient["id"];
                $patientPDFData[$patient["id"]]["first_name"] = $patient["first_name"];
                $patientPDFData[$patient["id"]]["last_name"] = $patient["last_name"];
                $patientPDFData[$patient["id"]]["dob"] = $patient["birthdate"] !== NULL ? date("m/d/Y", strtotime($patient["birthdate"])) : "";
                $patientPDFData[$patient["id"]]["location"] = $request->get("4") ? $patient["location"] : "";
                $patientPDFData[$patient["id"]]["mrn"] = $request->get("5") ? $patient["mrn"] : "";
                if($request->get("6")) {
                    $patientPDFData[$patient["id"]]["information"]["Patient Details"]["Visit ID"] = $request->get("6") ? $patient["hpi"] : "";
                }
                if($request->get("7")) {
                    $patientPDFData[$patient["id"]]["information"]["Patient Details"]["Admitted Date"] = $request->get("7") && $patient["admitted_at"] !== NULL ? date("m/d/Y", strtotime($patient["admitted_at"])) : "";
                }
                if($request->get("8")) {
                    $patientPDFData[$patient["id"]]["information"]["Patient Details"]["Allergies"] = $request->get("8") ? $patient["notes"] : "";
                }
                if($request->get("101")) {
                    $patientPDFData[$patient["id"]]["information"]["Patient Details"]["Last seen"] = $request->get("101") && $patient["last_seen_date"] !== NULL ? date("m/d/Y", strtotime($patient["last_seen_date"])) : "";
                }
                if($request->get("102")){
                    $updatedBy = $em->getRepository('HospitalBundle:Physician')->findOneBy(array("id"=>$patient["last_updated_by"]));
                    $patientPDFData[$patient["id"]]["information"]["Patient Details"]["Last updated by"] = $updatedBy ? $updatedBy->getLastName().", ".$updatedBy->getFirstName() : "";
                }

                // Patient stored attributes
                $patientStoredAttributes = array();
                if($patient["attributes"]){
                    $patientStoredAttributes = json_decode($patient["attributes"]);
                    $patientStoredAttributes = $patientStoredAttributes->patientInfo;
                }
                // Prepare information elements for PDF
                if($items){
                    foreach ($items as $itemKey => $itemVal){
                        if($itemKey != "PATIENT DETAILS") {
                            if ($itemVal["fields"] && count($itemVal["fields"]) > 0) {
                                foreach ($itemVal["fields"] as $fieldVal) {
                                    if($request->get($fieldVal["id"])){
                                        $patientPDFData[$patient["id"]]["information"][$itemKey][$fieldVal["label"]] = $this->searchPatientInformation($fieldVal["id"], $patientStoredAttributes);
                                    }
                                }
                            }
                        }
                    }
                }

                // Get patient tasks
                $pat = $em->getRepository('HospitalBundle:Patient')->findOneBy(array("id"=>$patient["id"]));
                $patTasks = $em->getRepository('HospitalBundle:PatientTask')->getPatientTasks($pat, $hosp, null, false, false);
                if($patTasks){
                    foreach ($patTasks as $patTask){
                        $patientPDFData[$patient["id"]]["patient_tasks"][$patTask->getId()]["name"] = $patTask->getName();
                        $patientPDFData[$patient["id"]]["patient_tasks"][$patTask->getId()]["completed"] = 0;
                        $patientPDFData[$patient["id"]]["patient_tasks"][$patTask->getId()]["importance"] = $patTask->getIsStat();
                        if($patTask->getCompleted()){
                            $patientPDFData[$patient["id"]]["patient_tasks"][$patTask->getId()]["completed"] = 1;
                            $patientPDFData[$patient["id"]]["patient_tasks"][$patTask->getId()]["completed_by"] = "(Completed by ".$patTask->getCompletedBy()->getNameFirstLast()." at ".$patTask->getCompletedAtString().")";
                        }
                    }
                }
            }
        }
        $html = $this->renderView('HospitalBundle:Patient:patient_info.pdf.twig', array(
            "filteredBy" => $filteredBy,
            'patientsPDF'   =>  $patientPDFData
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('orientation'=>'Portrait', 'default-header'=>false)),
            'Handoff Print PDF.pdf', 'application/pdf', ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    // Search patient info element in stored patient attributes
    public function searchPatientInformation($val, $array) {
        $returnVal = "";
        if($array && count($array) > 0){
            foreach ($array as $arrayVal){
                if($arrayVal->id == $val){
                    return $arrayVal->value;
                }
            }
        }
        return $returnVal;
    }

    /**
     * @Route("/careteamphys/{id}/{patId}", name="careteamphys_delete")
     * @Template()
     */
    public function removeCareTeamPhysAction($id, $patId) {
        //echo 'id='.$id;
        //echo '<pre> patId='.$patId; exit;
        $this->getDoctrine()->getManager()->createQueryBuilder()
                ->delete('HospitalBundle:PhysicianPatient', 'pp')
                ->where('pp.patient=:pat')
                ->andWhere('pp.physician=:phys')
                ->setParameter('pat', $patId )
                ->setParameter('phys', $id )
                ->getQuery()
                ->getResult();

        return $this->redirect($this->generateUrl('patient_mylist'));
    }


    //listing deleted patients
    /**
     * @Route("/")
     * @Route("/dlist", name="dpatient_list")
     * @Template("HospitalBundle:Patient:dpatient_list.html.twig")
     */
    public function dpatientAction(Request $request) {
        $ctx = $this->get('security.authorization_checker');

        $em = $this->getDoctrine()->getManager();
        $patRepo = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        $search_term = $request->get('search_term');
        $pageLimit = $request->get('pagelimit', 50);
        $offset = $request->get('offset', 0);
        $agency = null;
        if (!$ctx->isGranted(User::$ROLE_HOSPITAL_ADMIN)) {
            return $this->flashBack('notice', "You do not have permissions to view patient information", 'home');
        }
        if(is_null($userHosp))
        {
            return $this->flashBack('notice', "Must be hospital admin", 'home');
        }
        if (!$ctx->isGranted(User::$ROLE_PRIV_VIEW_HOSP_PATIENTS)) {
            $userEmp = $this->loggedInUser()->getEmployee();
            if ($userEmp) {
                $agency = $userEmp->getAgency();
            }
        }
        $patients = $patRepo->findAllDeletedMatching($search_term, $this->loggedInUser()->getHospital(), $agency, $pageLimit, $offset, false);
        $total_no_records = $patRepo->findAllDeletedMatching($search_term, $this->loggedInUser()->getHospital(), $agency, $pageLimit, $offset, true);

        if ($patients === null) {
            return $this->flashBack('notice', 'could not find patients!');
        }

        return array(
            'record' => $patients,
            'search_term' => $search_term,
            'offset' => $offset,
            'pagelimit' => $pageLimit,
            'count' => count($patients),
            'total_no_records' => $total_no_records
        );
    }

    /**
     * @Route("/delete/{id}", name="patient_delete")
     * @Template()
     */
    public function delpatAction(Request $request, $id) {
        $ctx = $this->get('security.authorization_checker');
        if (!$ctx->isGranted(User::$ROLE_HOSPITAL_ADMIN)) {
            return $this->flashBack('notice', "You do not have permissions for this operation", 'home');
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('HospitalBundle:Patient');
        $userHosp = $this->loggedInUser()->getHospital();
        $element = $repository->findOneBy(array("id"=>$id,"hospital"=>$userHosp));

        if(is_null($userHosp))
        {
            return $this->flashBack('notice', "You do not have permissions for this operation", 'home');
        }
        if (false !== is_null($element)) {
            throw $this->createNotFoundException('could not find patient ' . $id . ' !');
        }

        $element->setUpdatedAt(new \DateTime('now'));
        $element->setDeletedAt(new \DateTime('now'));
        $em->flush();


        return $this->redirect($this->generateUrl('patient_list'));

    }



}

