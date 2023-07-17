<?php
/**
 * Created by PhpStorm.
 * User: Nanda Yemparala
 * Date: 10/30/17
 * Time: 2:18 PM
 */

namespace Navio\HospitalBundle\Controller;


use Navio\HospitalBundle\Entity\Physician;
use Navio\HospitalBundle\Entity\PhysicianGroup;
use Navio\HospitalBundle\Entity\PhysicianPhysicianGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Navio\HospitalBundle\Service\EventQLib;
use Navio\HospitalBundle\Service\MessageLib;

class GroupsApiController extends NHController
{

    /**
     *
     * @Route("/{param}api.php/group/participants", defaults={"_format" : "json"},  requirements={"param": ".+"})
     * @Method({"GET","POST"})
     *
     */
    public function physicianMobileSearchAltAction(Request $request)
    {
        return self::getMembersOfGroup($request);
    }

    /**
     *
     * @Route("/api.php/group/participants", defaults={"_format" : "json"},  requirements={"param": ".+"})
     * @Method({"GET","POST"})
     *
     */
    public function getMembersOfGroup(Request $request)
    {
        $currentUser = $this->computePhysician($request);
        $groupMembersProvider = $this->get('group.members');
        $physicianId = $request->get('physician_id');
        var_dump($physicianId);
        $groupId = $request->get('group_id');

        if($physicianId <= 0 && $groupId <= 0){
            return $this->sendJsonError("Required information missing");
        }

        $em = $this->get('doctrine')->getManager();
        $pgRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
        $physicianRepo = $em->getRepository('HospitalBundle:Physician');
        $ppgRepo = $em->getRepository('HospitalBundle:PhysicianPhysicianGroup');

        $physicianGroup = null;

        if($groupId > 0)
        {
            $physicianGroup = $pgRepo->find($groupId);
        } else {
            $physician = $physicianRepo->find($physicianId);

            if($physician == null || $physician->getDeletedAt() != null) {
                return $this->sendJsonError("Invalid request (!p)");
            }

            if($physician->getContactType() == Physician::$CONTACT_ADMIN_GROUP){
                $groupType = PhysicianGroup::$GROUP_TYPE_ADMIN_GROUP;
            }
            else if($physician->getContactType() == Physician::$CONTACT_AUTO_GROUP){
                $groupType = PhysicianGroup::$GROUP_TYPE_AUTO_GROUP;
            }
            else if($physician->getContactType() == Physician::$CONTACT_AUTO_OUGROUP){
                $groupType = PhysicianGroup::$GROUP_TYPE_LDAP_GROUP;
            }
            else {
                return $this->sendJsonError("Invalid request (!t)");
            }

            $physicianGroup = $pgRepo->findOneBy(['physician' => $physician, 'groupType' => $groupType]);
        }

        if($physicianGroup == null || $physicianGroup->getHospital() != $currentUser->getHospital()){
            return $this->sendJsonError("Invalid request (!g)");
        }

        $ids = [];
        $members = $groupMembersProvider->getMembersOfGroup($physicianGroup, $currentUser);

        foreach ($members as $member){
            $ids[] = intval($member['id']);
        }
        //add group owner info
        $ownerId = -1;
        $ownerUser = $ppgRepo->findOneBy(array(
            'physicianGroup' => $physicianGroup->getId(),
            'privs' => PhysicianPhysicianGroup::$MUC_ADMIN_PRIVS,
        ));
        if($ownerUser){
            $ownerId = $ownerUser->getPhysician()->getId();
        }
        return $this->sendJsonResponse(['participants' => $ids, 'owner' => $ownerId]);
    }

    /**
     *
     * @Route("/api.php/group/mute", defaults={"_format" : "json"},  requirements={"param": ".+"}, methods={"GET", "POST"})
     */
    public function muteGroupAction(Request $request)
    {
        try {
            $currentUser = $this->computePhysician($request);
            $groupId = $request->get('group_id');
            $mute = $request->get('mute');
            $groupMembersProvider = $this->get('group.members');
            // Check group id parameter value is passed or not
            if($groupId == null || !$groupId > 0){
                syslog(LOG_ERR, 'Mute Group API: Invalid group id parameter');
                return $this->sendJsonError("Invalid group id");
            }
            // Get the Physician Physician Group Repo and check with physician id and group id
            $em = $this->get('doctrine')->getManager();
            $ppgRepo = $em->getRepository('HospitalBundle:PhysicianPhysicianGroup');
            $ppGroup = $ppgRepo->findOneBy(['physician' => $currentUser->getId(), 'physicianGroup' => $groupId]);

            if(!$ppGroup){
                syslog(LOG_ERR, 'Mute Group API: The Physician group is not found for physician id: '.$currentUser->getId()." and group id: ".$groupId);
                return $this->sendJsonError("Not member of the group or group does not exist.");
            }
            if(in_array($mute, array(1,0))){
                if($ppGroup->getConfig()){
                    $config = json_decode($ppGroup->getConfig());
                    $config->silent = $mute;
                }else{
                    $config = array();
                    $config['silent'] = $mute;
                }
                $ppGroup->setConfig(json_encode($config));
                $em->persist($ppGroup);
                $em->flush();
            }
            $phyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
            $phyGroup = $phyGroupRepo->find($groupId);
            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));

        }catch(\Exception $e){
            syslog(LOG_ERR, 'Mute Group API: Unable to mute the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to mute the group. '.$e->getMessage());
        }
    }

    /**
     *
     * @Route("/api.php/group/saveGroup", defaults={"_format" : "json"},  requirements={"param": ".+"})
     * @Method({"POST"})
     *
     */
    public function saveGroupAction(Request $request)
    {
        try {
            $currentUser = $this->computePhysician($request);
            $groupId = $request->get('group_id');
            $name = $request->get('name');
            // Check group id parameter value is passed or not
            if($groupId == null || !$groupId > 0){
                syslog(LOG_ERR, 'Save Group API: Invalid group id parameter');
                return $this->sendJsonError("Invalid group id");
            }
            $em = $this->get('doctrine')->getManager();
            $phyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
            $phyGroup = $phyGroupRepo->find($groupId);
            // Find the user whether the member of group or not
            $ppgRepo = $em->getRepository('HospitalBundle:PhysicianPhysicianGroup');
            $ppGroup = $ppgRepo->findOneBy(['physician' => $currentUser->getId(), 'physicianGroup' => $groupId]);
            if($phyGroup && $ppGroup){
                if($name) $phyGroup->setName($name);
                $phyGroup->setSaved(true);
                $em->persist($phyGroup);
                $em->flush();
                return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));
            }else{
                syslog(LOG_ERR, 'Save Group API: Invalid action. User is not member of group or invalid group');
                return $this->sendJsonError('Invalid action');
            }
            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));
        }catch(\Exception $e){
            syslog(LOG_ERR, 'Save Group API: Unable to save the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to save the group. '.$e->getMessage());
        }

    }

    /**
     * @Route("/api.php/group/createGroup{param}", defaults={"_format" : "json"},  requirements={"param": ".+"}, methods={"POST"})
     */
    public function createGroupAltAction(Request $request)
    {
        return self::createGroupAction($request);
    }


    /**
     *
     * @Route("/api.php/group/createGroup{trailingSlash}", defaults={"_format" : "json"},  requirements={"trailingSlash": "[/]{0,1}"})
     * @Method({"POST"})
     *
     */
    public function createGroupAction(Request $request)
    {
        try {
            $currentUser = $this->computePhysician($request);
            $members = json_decode($request->get("ids"), true);
            $isH911 = $request->get("is_h911_allow", false);
            $em = $this->get('doctrine')->getManager();
            $phyGroup = new PhysicianGroup();
            if($isH911){
                $groupName = "H911 - ".$phyGroup->getDefaultGroupName($currentUser);
                $phyGroup->setName($groupName);
            } else {
                $phyGroup->setName($phyGroup->getDefaultGroupName($currentUser));
            }
            $phyGroup->setHospital($currentUser->getHospital());
            $phyGroup->setSaved(false);
            $em->persist($phyGroup);
            $em->flush();

            if(!in_array($currentUser->getId(), $members)){
                array_push($members, $currentUser->getId());
            }
            
            // Add members to the group
            $this->addMembersToGroup($phyGroup, $members, $currentUser);
            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));

        }catch(\Exception $e){
            syslog(LOG_ERR, 'Create Group API: Unable to create the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to create the group. '.$e->getMessage());
        }

    }

    /**
     *
     * @Route("/api.php/group/UpdateGroup{trailingSlash}", defaults={"_format" : "json"},  requirements={"trailingSlash": "[/]{0,1}"})
     * @Method({"POST"})
     *
     */
    public function UpdateGroupAction(Request $request)
    {
        try {
            $currentUser = $this->computePhysician($request);
            $members = json_decode($request->get("ids"), true);

            $groupId = $request->get("group_id");
            $prefGroupName = $request->get("name");
            $em = $this->get('doctrine')->getManager();
            $saveGroup = false;

            if($prefGroupName){
                $saveGroup = true;
            }
            syslog(LOG_INFO, 'group_id '. $groupId);
            syslog(LOG_INFO, 'name '. $prefGroupName);

            $ids = json_decode($request->get("ids"), true);
            syslog(LOG_INFO, 'no of ids '. sizeof($ids));

            if(!$ids || sizeof($ids) === 0){
                syslog(LOG_ERR, 'Update Group API: Group members invalid');
                return $this->sendJsonError("Group members invalid");
            }

            if($groupId > 0){
                // Check group id in the database
                $phyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
                $phyGroup = $phyGroupRepo->find($groupId);
                if(!$phyGroup){
                    syslog(LOG_ERR, 'Update Group API: Incorrect group id');
                    return $this->sendJsonError("Incorrect group id");
                }

                if($phyGroup->getPhysician() && $phyGroup->getPhysician()->getId() != $currentUser->getId()){
                    syslog(LOG_ERR, 'Update Group API: Incorrect action');
                    return $this->sendJsonError("Incorrect action");
                }
                // If group is not MUC group.
                if($phyGroup->getPhysician()){
                    // create a new MUC group and add members.
                    $phyGroup  = new PhysicianGroup();
                    $phyGroup ->setName($prefGroupName ? $prefGroupName : $phyGroup->getDefaultGroupName($currentUser));
                    $phyGroup ->setSaved($saveGroup);
                    $phyGroup ->setHospital($currentUser->getHospital());
                    $em->persist($phyGroup);
                    $em->flush();

                    $members = array();
                    foreach($phyGroup->getPhysicianPhysicianGroups() as $member){
                        array_push($members, $member->getId());
                    }
                    $ids = $members;
                }else{
                    // update MUC group.
                    if($prefGroupName) {
                        $phyGroup->setSaved($saveGroup);
                        if (strcmp($phyGroup->getName(), $prefGroupName) !== 0) {
                            $phyGroup->setName($prefGroupName);
                        }
                        $em->persist($phyGroup);
                        $em->flush();
                    }
                    $physicians = $phyGroup->getPhysician();
                    //if($physicians->contains($currentUser->getId())){
                    if($phyGroup->getPhysician() && $phyGroup->getPhysician()->getId() == $currentUser->getId()){
                        syslog(LOG_ERR, 'Update Group API: No access');
                        return $this->sendJsonError("No access");
                    }
                    // Remove all existing users.
                    $phyPhyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
                    $phyPhyGroup = $phyPhyGroupRepo->removeExistingGroupMembers($phyGroup);
                }
            }else{
                $phyGroup = new PhysicianGroup();
                $phyGroup->setName($prefGroupName ? $prefGroupName : $phyGroup->getDefaultGroupName($currentUser));
                $phyGroup->setHospital($currentUser->getHospital());
                $em->persist($phyGroup);
                $em->flush();
            }
            if(!in_array($currentUser->getId(), $ids)) {
                array_push($ids,(int) $currentUser->getId());
            }
            $this->addMembersToGroup($phyGroup, $ids, $currentUser);
            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));
        }catch(\Exception $e){
            syslog(LOG_ERR, 'Update Group API: Unable to update the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to update the group. '.$e->getMessage());
        }

    }

    /**
     *
     * @Route("/api.php/group/exitGroup{trailingSlash}", defaults={"_format" : "json"},  requirements={"trailingSlash": "[/]{0,1}"})
     * @Method({"POST"})
     *
     */
    public function exitGroupAction(Request $request)
    {
        try {
            $currentUser = $this->computePhysician($request);
            $em = $this->get('doctrine')->getManager();

            $groupId = $request->get("group_id");
            $phyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
            $phyGroup = $phyGroupRepo->find($groupId);
            // Check group exists or not
            if(!$phyGroup){
                syslog(LOG_ERR, 'Exit Group API: Invalid group id');
                return $this->sendJsonError("Invalid group");
            }
            // Check logged in user is already member of the group or not?
            if(!$phyGroup->isExistingMemberWithId($currentUser->getId())){
                syslog(LOG_ERR, 'Exit Group API: Invalid access !M');
                return $this->sendJsonError('Invalid access !M');
            }
            $ids = $request->get('ids');
            if(!$ids){ // That means drop current user only
                $phyPhyGroup = $phyGroup->getPhysicianPhysicianGroupMember($currentUser);
                if(!$phyPhyGroup->hasPriv(PhysicianPhysicianGroup::$PPG_LEAVE)){
                    syslog(LOG_ERR, 'Exit Group API: You do not have sufficient privileges for this action');
                    return $this->sendJsonError("You do not have sufficient privileges for this action");
                }
                $em->remove($phyPhyGroup);
                $em->flush();
                $ids = array($currentUser->getId());
            } else{
                $ids = json_decode($ids);
                if(!$ids || sizeof($ids ) == 0){
                    syslog(LOG_ERR, 'Exit Group API: Incorrect Action (406)');
                    return $this->sendJsonError("Incorrect Action (406)");
                }
                $phyPhyGroup = $phyGroup->getPhysicianPhysicianGroupMember($currentUser);
                if(sizeof($ids) == 1 && $ids[0] == $currentUser->getId()){
                    if(!$phyPhyGroup->hasPriv(PhysicianPhysicianGroup::$PPG_LEAVE)){
                        syslog(LOG_ERR, 'Exit Group API: You do not have sufficient privileges for this action. !'.PhysicianPhysicianGroup::$PPG_LEAVE);
                        return $this->sendJsonError("You do not have sufficient privileges for this action. !".PhysicianPhysicianGroup::$PPG_LEAVE);
                    }
                } else if(!$phyPhyGroup->hasPriv(PhysicianPhysicianGroup::$PPG_REMOVE_OTHERS)){
                    syslog(LOG_ERR, 'Exit Group API: You do not have sufficient privileges for this action. !'.PhysicianPhysicianGroup::$PPG_REMOVE_OTHERS);
                    return $this->sendJsonError("You do not have sufficient privileges for this action. !".PhysicianPhysicianGroup::$PPG_REMOVE_OTHERS);
                }
            }

            $text = '';
            // Prepare the messsage text for those who are removed from the group
            foreach ($ids as $id){
                $ppg = $phyGroup->getPhysicianPhysicianGroupMemberById($id);
                if($ppg){
                    if($ppg->getPhysician() && $ppg->getPhysician()->getId() != $currentUser->getId()){
                        $text .= $ppg->getPhysician()->getFirstName().' '.$ppg->getPhysician()->getLastName();
                    }
                }
            }
            // Check if message has some text then send message in the group
            if(strlen($text) > 0){
                $text .= ' removed from group.';
                $this->sendPmToGroup($currentUser, $phyGroup, $text, 0, true);
            }
            // Remove the Ids from the Physician Physician Group
            foreach ($ids as $id){
                $ppg = $phyGroup->getPhysicianPhysicianGroupMemberById($id);
                if($ppg){
                    $em->remove($ppg);
                }
            }
            // Flush em if there are ids
            if(!$ids || sizeof($ids ) > 0) {
                $em->flush();
            }

            syslog(LOG_INFO,"Exit Group API: ".$currentUser->getId()." from:".$groupId);
            // Update the Physician Group timestamp
            $phyGroup->setUpdatedAt(new DateTime('now', $currentUser->getHospital()->DateTimeZone()));
            $em->persist($phyGroup);
            $em->flush();

            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));
        }catch(\Exception $e){
            syslog(LOG_ERR, 'Exit Group API: Unable to exit the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to exit the group. '.$e->getMessage());
        }

    }

    private function addMembersFunc(Request $request, $currentUser){
        try {

            $groupId = $request->get("group_id");
            $newMembers = json_decode($request->get("ids"), true);
            if(!$newMembers || sizeof($newMembers) === 0){
                syslog(LOG_ERR, 'Add Members to Group API: Group members invalid');
                return $this->sendJsonError("Group members invalid");
            }

            $em = $this->get('doctrine')->getManager();
            $phyGroupRepo = $em->getRepository('HospitalBundle:PhysicianGroup');
            $phyGroup = $phyGroupRepo->find($groupId);
            // Check group exists or not
            if(!$phyGroup){
                syslog(LOG_ERR, 'Add Members to Group API: Not found !G');
                return $this->sendJsonError("Not found !G");
            }
            // Check logged in user is the member of the group or not
            $ppg = $phyGroup->getPhysicianPhysicianGroupMember($currentUser);
            if(!$ppg){
                syslog(LOG_ERR, 'Add Members to Group API: Invalid access !M');
                return $this->sendJsonError("Invalid access !M");
            }
            // Check the logged in user has the privileges to
            if(!$ppg->hasPriv(PhysicianPhysicianGroup::$PPG_ADD)){
                syslog(LOG_ERR, 'Add Members to Group API: You do not have sufficient privileges for this action');
                return $this->sendJsonError("You do not have sufficient privileges for this action");
            }
            // Call add members to the group
            $this->addMembersToGroup($phyGroup, $newMembers, $currentUser, true);
            // Update the Physician Group Updated At timestamp
            $phyGroup->setUpdatedAt(new DateTime('now', $currentUser->getHospital()->DateTimeZone()));
            $em->persist($phyGroup);
            $em->flush();

            return $this->sendJsonResponse($phyGroup->getGroupArray($currentUser));
        }catch(\Exception $e){
            syslog(LOG_ERR, 'Add Members to Group API: Unable to add members to the group, Error: '. $e->getMessage());
            return $this->sendJsonError('Unable to add members to the group. '.$e->getMessage());
        }

    }

    /**
     *
     * @Route("/api.php/group/addMembers{trailingSlash}", defaults={"_format" : "json"},  requirements={"trailingSlash": "[/]{0,1}"})
     * @Method({"POST"})
     *
     */
    public function addMembersAction(Request $request)
    {
        $currentUser = $this->computePhysician($request);
        return $this->addMembersFunc($request, $currentUser);
    }

    /**
     *
     * @Route("/group/addMembers", defaults={"_format" : "json"},  requirements={"param": ".+"})
     * @Method({"POST"})
     *
     */
    public function addMembersDesktopAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $currentUser = $user->getEmployee();
        return $this->addMembersFunc($request, $currentUser);
    }

    private function addMembersToGroup($mucGroup, $ids, $currentUser, $sendSilentInfoPm = false){
        $newMemberIds = array();
        $em = $this->get('doctrine')->getManager();
        if(is_array($ids)) {
            foreach ($ids as $id) {
                if($mucGroup->isExistingMemberWithId($id)){
                    continue;
                }
                $physicianPhysicianGroup = new PhysicianPhysicianGroup();
                $phy = $em->getRepository('HospitalBundle:Physician')->find($id);
                if ($phy) {
                    $physicianPhysicianGroup->setPhysician($phy);
                }

                $physicianPhysicianGroup->setPhysicianGroup($mucGroup);
                if ($id == $currentUser->getId()) {
                    $physicianPhysicianGroup->setPrivs(PhysicianPhysicianGroup::$MUC_ADMIN_PRIVS);
                } else {
                    $physicianPhysicianGroup->setPrivs(PhysicianPhysicianGroup::$DEFAULT_PRIVS);
                }
                $em->persist($physicianPhysicianGroup);
                $mucGroup->addPhysicianPhysicianGroup($physicianPhysicianGroup);
                array_push($newMemberIds, $id);
            }
            $em->flush();
            // Send PM to te members
            if($sendSilentInfoPm && sizeof($newMemberIds) > 0){
                $idsParam = implode(',', $newMemberIds);
                $phyRepo = $em->getRepository('HospitalBundle:Physician');
                $phyList = $phyRepo->getGroupConcatPhysicianByIds($idsParam);
                $text = $phyList[0]['phyFullName'];
                if(strlen($text) > 0){
                    $text .= ' added to group.';
                    //$mucGroup->refreshRelated();
                    $this->sendPmToGroup($currentUser, $mucGroup, $text, 0, true);
                }else{
                    syslog(LOG_INFO, 'Skip sending silent pm to muc');
                }
            }
        }
    }

    private function sendPmToGroup($currentUser, $group, $text, $imageId = 0, $silent = false, $originalPmId = 0){
        $em = $this->get('doctrine')->getManager();
        if(!$currentUser || !$group || strlen($text) == 0){
            syslog(LOG_ERR, 'Send PM to Group API: Incorrect action (211)');
            return $this->sendJsonError('Incorrect action (211)');
        }
        syslog(LOG_INFO, "sendPmToGroup sender:".$currentUser->getId()." Group:".$group->getId());
        $pms = array();
        $ppgs = $group->getPhysicianPhysicianGroups();
        $firstPmId = 0;
        $uniqueReceivers = array();
        foreach ($ppgs as $ppg){
            $receiver = $ppg->getPhysician();

            if($receiver->getId() == $currentUser->getId() ||
                $receiver->getDeletedAt() ||
                $receiver->getCoveringPhysician() ||
                in_array($receiver->getId(), $uniqueReceivers)
            ){
                continue;
            }

            $muted = $ppg->isMuted();
            $msgSvc = new MessageLib($em, $this->container);
            $pm = $msgSvc->sendMessageWithOptionsNoFosUser($currentUser, $receiver, null, $text, null, $group, null, $muted);
            if($firstPmId > 0) {
                $pm->setMessageGroup($firstPmId);
            }else{ // Set thread id to first pm id.
                $pm->setMessageGroup($pm->getId());
            }

            //Check for automata - they have a "+" prefix on the access code.
            if(substr($receiver->getAccessCode(),0,1) === "+"){
                $eq = new EventQLib($em,$this->container);
                $eq->xmitEvent("Eliza",$currentUser,array("receiver_id"=>$receiver->getId(),"text"=>$text,"message_id"=>$pm->getId()));
            }
            if(strcasecmp($receiver->getNpiNumber(),"workflow") == 0){
                $eq = new EventQLib($em,$this->container);
                $eq->xmitEvent("escalator",$currentUser,array("receiver_id"=>$receiver->getId(),"text"=>$text,"message_id"=>$pm->getId()));
            }
            if($receiver->getContactType() > 1){
                $eq = new EventQLib($em,$this->container);
                $eq->xmitEvent("PM.".$receiver->getContactType(),$currentUser, array("receiver_id"=>$receiver->getId(),"text"=>$text,"message_id"=>$pm->getId()), "exploder");
            }
            //echo "Physician <pre>";var_dump($receiver);die;
            $receiver->setTickledAt(new DateTime('now', $currentUser->getHospital()->DateTimeZone()));
            $em->persist($receiver);

            $uniqueReceivers[] = $receiver->getId();

            if($pm){
                if($firstPmId == 0){
                    $firstPmId = $pm->getId();
                }
                array_push($pms, $pm->toArray(false));
            }
        }
        $em->flush();
        return $pms;
    }

}
