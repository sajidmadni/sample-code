<?php
/**
 * User: robertfaulkner
 * Date: 20/01/15
 * Time: 12:50
 */

namespace Navio\HospitalBundle\ViewModel\User;


class EditUserRoleViewModel {

    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    private $roleAid;

    public function getRoleAid()
    {
        return $this->roleAid;
    }

    public function setRoleAid($value)
    {
        $this->roleAid = $value;
    }

    private $roleSeniorAid;

    public function getRoleSeniorAid()
    {
        return $this->roleSeniorAid;
    }

    public function setRoleSeniorAid($value)
    {
        $this->roleSeniorAid = $value;
    }

    private $roleNavigator;

    public function getRoleNavigator()
    {
        return $this->roleNavigator;
    }

    public function setRoleNavigator($value)
    {
        $this->roleNavigator = $value;
    }

    private $roleInformatics;

    public function getRoleInformatics()
    {
        return $this->roleInformatics;
    }

    public function setRoleInformatics($value)
    {
        $this->roleInformatics = $value;
    }

    private $roleOnCallAdmin;

    public function getRoleOnCallAdmin()
    {
        return $this->roleOnCallAdmin;
    }

    public function setRoleOnCallAdmin($value)
    {
        $this->roleOnCallAdmin = $value;
    }

    private $roleGroupsAdmin;

    public function getRoleGroupsAdmin()
    {
        return $this->roleGroupsAdmin;
    }

    public function setRoleGroupsAdmin($value)
    {
        $this->roleGroupsAdmin = $value;
    }

    private $roleAgencyOnCallAdmin;
    
    public function getRoleAgencyOnCallAdmin()
    {
        return $this->roleAgencyOnCallAdmin;
    }

    public function setRoleAgencyOnCallAdmin($value)
    {
        $this->roleAgencyOnCallAdmin = $value;
    }
    
    private $roleHospitalStaffAdmin;
    
    public function getRoleHospitalStaffAdmin()
    {
        return $this->roleHospitalStaffAdmin;
    }

    public function setRoleHospitalStaffAdmin($value)
    {
        $this->roleHospitalStaffAdmin = $value;
    }

    private $roleDeptUser;

    public function getRoleDeptUser()
    {
        return $this->roleDeptUser;
    }
    public function setRoleDeptUser($value)
    {
        $this->roleDeptUser = $value;
    }


    private $roleAgencyAdmin;

    public function getRoleAgencyAdmin()
    {
        return $this->roleAgencyAdmin;
    }

    public function setRoleAgencyAdmin($value)
    {
        $this->roleAgencyAdmin = $value;
    }

    private $roleHospitalOnly;

    public function getRoleAgencyOnly()
    {
        return $this->roleAgencyOnly;
    }

    public function setRoleAgencyOnly($value)
    {
        $this->roleAgencyOnly = $value;
    }

    private $roleHospitalAdmin;

    public function getRoleHospitalAdmin()
    {
        return $this->roleHospitalAdmin;
    }

    public function setRoleHospitalAdmin($value)
    {
        $this->roleHospitalAdmin = $value;
    }

    private $roleBedboardAdmin;

    public function getRoleBedboardAdmin()
    {
        return $this->roleBedboardAdmin;
    }

    public function setRoleBedboardAdmin($value)
    {
        $this->roleBedboardAdmin = $value;
    }

    private $roleHospitalUser;

    public function getRoleHospitalUser()
    {
        return $this->roleHospitalUser;
    }

    public function setRoleHospitalUser($value)
    {
        $this->roleHospitalUser = $value;
    }

    private $roleConsultConsumer;

    public function getRoleConsultConsumer()
    {
        return $this->roleConsultConsumer;
    }

    public function setRoleConsultConsumer($value)
    {
        $this->roleConsultConsumer = $value;
    }

    private $roleConsultCreator;

    public function getRoleConsultCreator()
    {
        return $this->roleConsultCreator;
    }

    public function setRoleConsultCreator($value)
    {
        $this->roleConsultCreator = $value;
    }

    private $roleReferralConsumer;

    public function getRoleReferralConsumer()
    {
        return $this->roleReferralConsumer;
    }

    public function setRoleReferralConsumer($value)
    {
        $this->roleReferralConsumer = $value;
    }

    private $roleReferralCreator;

    public function getRoleReferralCreator()
    {
        return $this->roleReferralCreator;
    }

    public function setRoleReferralCreator($value)
    {
        $this->roleReferralCreator = $value;
    }

    private $roleServiceRecoveryAdmin;

    public function getRoleServiceRecoveryAdmin()
    {
        return $this->roleServiceRecoveryAdmin;
    }

    public function setRoleServiceRecoveryAdmin($value)
    {
        $this->roleServiceRecoveryAdmin = $value;
    }
    private $roleAnalyticsManager;

    public function getRoleAnalyticsManager()
    {
        return $this->roleAnalyticsManager;
    }
    public function setRoleAnalyticsManager($value)
    {
        $this->roleAnalyticsManager = $value;
    }

    private $roleCustomerSupport;
    public function getRoleCustomerSupport()
    {
        return $this->roleCustomerSupport;
    }
    public function setRoleCustomerSupport($value)
    {
        $this->roleCustomerSupport = $value;
    }
    
    private $roleMessageBlastSender;
    public function getRoleMessageBlastSender()
    {
        return $this->roleMessageBlastSender;
    }
    public function setRoleMessageBlastSender($value)
    {
        $this->roleMessageBlastSender = $value;
    }


    private $rolePatientHandoffAdmin;
    public function getRolePatientHandoffAdmin()
    {
        return $this->rolePatientHandoffAdmin;
    }
    public function setRolePatientHandoffAdmin($value)
    {
        $this->rolePatientHandoffAdmin = $value;

    }

    private $roleOtpLogin = false;
    public function getRoleOtpLogin()
    {
        return $this->roleOtpLogin;
    }
    public function setRoleOtpLogin($value)
    {
        $this->roleOtpLogin = $value;

    }

    private $roleReadonlyMonthOncallCal = false;
    public function getRoleReadonlyMonthOncallCal()
    {
        return $this->roleReadonlyMonthOncallCal;
    }
    public function setRoleReadonlyMonthOncallCal($value)
    {
        $this->roleReadonlyMonthOncallCal = $value;

    }

}
