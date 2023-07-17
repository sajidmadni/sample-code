<?php

namespace Navio\HospitalBundle\Entity;
//use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Collections\Criteria;
use Navio\ConsultBundle\Entity\Consult;
use Navio\HospitalBundle\Entity\Physician;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Util\Debug;
use Navio\Utils\Utils;
use PracticeUnite\CoreBundle\Entity\NHBaseDeletable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/*

original table
+------------------------------+--------------+------+-----+-----------------------------------+----------------+
| Field                        | Type         | Null | Key | Default                           | Extra          |
+------------------------------+--------------+------+-----+-----------------------------------+----------------+
| id                           | bigint(20)   | NO   | PRI | NULL                              | auto_increment |
| name                         | varchar(255) | NO   |     | NULL                              |                |
| city                         | varchar(255) | NO   |     | NULL                              |                |
| state                        | varchar(255) | NO   |     | NULL                              |                |
| logo                         | varchar(255) | NO   |     | NULL                              |                |
| it_phone                     | varchar(255) | NO   |     | NULL                              |                |
| it_email                     | varchar(255) | NO   |     | NULL                              |                |
| emr_phone                    | varchar(255) | NO   |     | NULL                              |                |
| deletion_time                | bigint(20)   | NO   |     | 720                               |                |
| password_timeout             | bigint(20)   | NO   |     | 30                                |                |
| ceo_id                       | bigint(20)   | YES  |     | NULL                              |                |
| ceo_connect_administrator_id | bigint(20)   | YES  |     | NULL                              |                |
| hospital_admin_user_id       | bigint(20)   | YES  |     | NULL                              |                |
| schedule_notification_email  | varchar(255) | YES  |     | NULL                              |                |
| access_code_email_title      | varchar(255) | NO   |     | Practice Unite - Your access code |                |
| access_code_email_body       | text         | NO   |     | NULL                              |                |
| created_at                   | datetime     | NO   |     | NULL                              |                |
| updated_at                   | datetime     | NO   |     | NULL                              |                |
| deleted_at                   | datetime     | YES  |     | NULL                              |                |
+------------------------------+--------------+------+-----+-----------------------------------+----------------+

*/


/**
 * Hospital
 *
 * @ORM\Table(name="hospital")
 * @ORM\Entity(repositoryClass="Navio\HospitalBundle\Entity\HospitalRepository")
 */
class Hospital extends NHBaseDeletable
{
    const DEFAULT_SUPPORT_EMAIL = "support@test.com";
    const DEFAULT_SEARCH_RADIUS = "25"; // in miles

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=255, nullable=false)
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="it_phone", type="string", length=255, nullable=false)
     */
    private $itPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="it_email", type="string", length=255, nullable=false)
     */
    private $itEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="emr_phone", type="string", length=255, nullable=false)
     */
    private $emrPhone;

    /**
     * @var integer
     *
     * @ORM\Column(name="deletion_time", type="bigint", nullable=false, options={"default" = 720})
     */
    private $deletionTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="password_timeout", type="bigint", nullable=false, options={"default" = 30})
     */
    private $passwordTimeout;

    /**
     * @var integer
     *
     * @ORM\Column(name="ceo_id", type="bigint", nullable=true)
     */
    private $ceoId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ceo_connect_administrator_id", type="bigint", nullable=true)
     */
    private $ceoConnectAdministratorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="hospital_admin_user_id", type="bigint", nullable=true)
     */
    private $hospitalAdminUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule_notification_email", type="string", length=255, nullable=true)
     */
    private $scheduleNotificationEmail;
    
    /**
     * @var string
     *
     * @ORM\Column(name="api_url", type="string", length=255, nullable=true)
     */
    private $apiUrl;

    /**
     * @var string Authentication Methods,  e.g., "AD" "AD,AC" "AC" - for Access code and Active Directory
     *
     * @ORM\Column(name="access", type="string", length=255, nullable=false, options={"default" = "AC"})
     */
    private $access;

    /**
     * @var string
     *
     * @ORM\Column(name="access_code_email_title", type="string", length=255, nullable=false,options={"default" = "Practice Unite - Your access code"})
     */
    private $accessCodeEmailTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="access_code_email_body", type="text", nullable=false)
     */
    private $accessCodeEmailBody;
    
    /**
     * @var string
     *
     * @ORM\Column(name="make_email_subject", type="string", length=255, nullable=true)
     */
    private $makeEmailSubject;

    /**
     * @var string
     *
     * @ORM\Column(name="make_email_body", type="text", nullable=true)
     */
    private $makeEmailBody;

/**
     * @var string
     *
     * @ORM\Column(name="access_request_text", type="text", nullable=true)
     */
    private $accessRequestText;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;
    
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="PatientGroup", mappedBy="hospital")
     */
    protected $patientGroups;
	
    /**
     * @ORM\OneToMany(targetEntity="PhysicianGroup", mappedBy="hospital")
     */
    protected $physicianGroups;	

    /**
     * @ORM\OneToMany(targetEntity="HospitalComponent", mappedBy="hospital")
     */
    protected $components;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="hospital")
     */
    protected $departments;
    
    /**
     * @ORM\OneToMany(targetEntity="HospitalDiagnosis", mappedBy="hospital")
     */
    protected $diagnosis;
    
    /**
     * @ORM\OneToMany(targetEntity="HospitalPresenceStatus", mappedBy="hospital")
     */
    protected $presenceStatus;

    /**
     * The base URL for this hospital.   This value is used to direct the mobile application 
     * to the most appropriate server. 
     * 
     * @var string
     *
     * @ORM\Column(name="base_url", type="string", length=255, nullable=true)
     */
    private $baseUrl;


    /**
     * @ORM\OneToMany(targetEntity="HospitalSetting", mappedBy="hospital",cascade={"persist", "remove"})
     */
    protected $settings;

    /**
     * @ORM\OneToMany(targetEntity="OnCallCategory", mappedBy="hospital")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $onCallCategories;

    /**
     * @ORM\OneToMany(targetEntity="Physician", mappedBy="hospital")
     * @ORM\OrderBy({"lastName" = "ASC"})
     */
    protected $physicians;

    /**
     * @ORM\OneToMany(targetEntity="Patient", mappedBy="hospital")
     * @ORM\OrderBy({"lastName" = "ASC"})
     */
    protected $patients;

    /**
     * @ORM\OneToMany(targetEntity="HospitalTemplate", mappedBy="hospital")
     */
    protected $templates;

    /**
     * @ORM\OneToMany(targetEntity="Navio\ReportingBundle\Entity\QuestionSet", mappedBy="hospital")
     */
    protected $questionSets;

    /**
     * @ORM\OneToMany(targetEntity="Bed", mappedBy="hospital")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $beds;

    /**
     * @ORM\OneToMany(targetEntity="BedStatus", mappedBy="hospital")
     */
    protected $bedStatus;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="hospital")
     */
    protected $locations;

    /**
     * @ORM\OneToMany(targetEntity="HospitalAgency", mappedBy="hospital")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $agencies;
    
    /**
     * @var string
     *
     * @ORM\Column(name="new_users_email_subject", type="string", length=255, nullable=true)
     */
    private $newUsersEmailSubject;

    /**
     * @var string
     *
     * @ORM\Column(name="new_users_email_body", type="text", nullable=true)
     */
    private $newUsersEmailBody;

    /**
     * @ORM\OneToMany(targetEntity="Navio\HospitalBundle\Entity\EmploymentType", mappedBy="hospital")
     */
    protected $employmentTypes;

    /**
     * @return mixed
     */
    public function getEmploymentTypes()
    {
        return $this->employmentTypes;
    }

    /**
     * @ORM\OneToMany(targetEntity="HospitalHospitals", mappedBy="hid1")
     */
    private $associatedHospitals;


    /**
     * @ORM\OneToMany(targetEntity="HospitalFloor", mappedBy="hospital")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $floors;

    /**
     * @ORM\OneToMany(targetEntity="HospitalProcedure", mappedBy="hospital")
     * @ORM\OrderBy({"type" = "ASC"})
     */
    protected $procedures;

    
    /**
     * Get floors
     *
     * @return array
     */
    public function getFloornames()
    {
        $rv = array();
        $floors = $this->getFloors();
        foreach ($floors as $f){
            $rv[$f->getPrefix()] = $f->getName();
        }
        return $rv;
    }

    public function isConsultWorkflowEnabled()
    {
        $options = $this->getSetting(HospitalSetting::$SETTING_DEFAULT_CONSULT_OPTIONS);
        return Utils::IsNullOrEmptyString($options) === false;
    }

    public function isReferralWorkflowEnabled()
    {
        $options = $this->getSetting(HospitalSetting::$SETTING_DEFAULT_REFERRAL_OPTIONS);
        return Utils::IsNullOrEmptyString($options) === false;
    }

    /**
     * @return string
     */
    public function getReferralComponentTitle()
    {
        return $this->getSetting(HospitalSetting::$SETTING_HOSPITAL_REFERRAL_COMPONENT_TITLE);
    }

    /**
     * @return string
     */
    public function getConsultComponentTitle()
    {
        return $this->getSetting(HospitalSetting::$SETTING_HOSPITAL_CONSULT_COMPONENT_TITLE);
    }

    /**
     * Get setting
     *
     * @return string
     */
    public function getSetting($name,$def = NULL)
    {
        $settings = $this->getSettings();
        foreach($settings as $item){
            if($item->getName()==$name){
                if(!$item->getVal()){
                    return $def;
                }else{
                    return $item->getVal();
                }
            }
        }
        return $def;
    }

    /**
     * Set setting
     *
     * @return \Navio\HospitalBundle\Entity\HospitalSetting
     */
    public function setSetting($name,$value)
    {
        $settings = $this->getSettings();
        foreach($settings as $item){
            if($item->getName()==$name){
                $item->setVal($value);
                return $item;
            }
        }
        $item = new HospitalSetting();
        $item->setHospital($this);
        $item->setVal($value);
        $item->setName($name);
        $this->addSetting($item);
        return $item;
    }

    /**
     * Default roles for a hospital user.
     * @return array
     */
    public function getDefaultRoles(){
        $rolesConfig = $this->getSetting('default_roles');
        $defaultRoles = array(User::$ROLE_HOSPITAL_USER);
        if($rolesConfig){
            try {
                $roles  = @unserialize($rolesConfig->getVal());
                if($roles  === null or $roles  === false){
                    $roles = $defaultRoles;
                }
            }catch(\Exception $e) {
                $roles = $defaultRoles;
            }
        }else {
            $roles = $defaultRoles;
        }
        return $roles;
    }

    /**
     * Get support email address
     *
     * @return string
     */
    public function getSupportEmail()
    {
        $supportEmail = $this->getSetting(HospitalSetting::$SETTING_SUPPORT_EMAIL, self::DEFAULT_SUPPORT_EMAIL);
        if (filter_var($supportEmail, FILTER_VALIDATE_EMAIL))
        {
            return $supportEmail;
        }
        syslog(LOG_ERR, "Support email '$supportEmail' for hospital {$this->getName()} is not valid. Using default ".self::DEFAULT_SUPPORT_EMAIL);
        return self::DEFAULT_SUPPORT_EMAIL;
    }

    /**
     * get DateTimeZone object for hospital - this is reused.
     */
    public function DateTimeZone() {
        if(!isset($this->dateTimeZone)){
            $this->dateTimeZone = new \DateTimeZone($this->getTimeZone());
        }
        return $this->dateTimeZone;
    }
    
    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->getSetting("timezone","America/New_York");
    }
    
    /**
     * Set timezone
     *
     * @return void
     */
    public function setTimeZone($tz)
    {
        $this->setSetting("timezone",$tz);
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getSetting("locale","en_US");
    }

    /**
     * Set timezone
     *
     * @return string
     */
    public function setLocale($locale)
    {
        $this->setSetting("locale",$locale);
    }

    /**
     * TODO  check policy field  policy('showCell')
     */
    public function policy($pname) {
        return false;
    }
    

    /**
     * get localTimestamp
     */
    public function localTimestamp() {
            $now = new \DateTime("NOW",new \DateTimeZone($this->getTimeZone()));
            return $now->format("d M Y H:i") ;
    }
    /**
     * get localDateTime
     */
    public function localNow() {
            $now = new \DateTime("NOW",new \DateTimeZone($this->getTimeZone()));
            return $now->format("Y-m-d H:i:00") ;
    }

    public function localDateTime(){
        return new \DateTime("NOW",new \DateTimeZone($this->getTimeZone()));
    }


    public function __construct()
    {
        $this->questionSets = new ArrayCollection();
        $this->agencies = new ArrayCollection();
        $this->patientGroups = new ArrayCollection();
        $this->physicianGroups = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->diagnosis = new ArrayCollection();
        $this->settings = new ArrayCollection();

        $this->onCallCategories = new ArrayCollection();
        $this->physicians = new ArrayCollection();
        $this->patients = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->procedures = new ArrayCollection();

        $this->createdAt=$this->updatedAt=new \DateTime("now",new \DateTimeZone("UTC"));
        $this->access = "AC";
    }
	
    public function toArray() {
        $ans = array();
        $ans['id'] = $this->getId();
        $ans['name'] = $this->getName();
        $ans['city'] = $this->getCity();
        $ans['state'] = $this->getState();
        $ans['logo'] = $this->getLogo();
        $ans['it_phone'] = $this->getItPhone();
        $ans['it_email'] = $this->getItEmail();
        $ans['emr_phone'] = $this->getEmrPhone();
        $ans['deletion_time'] = $this->getDeletionTime();
        $ans['password_timeout'] = $this->getPasswordTimeout();
        $ans['ceo_id'] = $this->getCeoId();
        $ans['ceo_connect_administrator_id'] = $this->getCeoConnectAdministratorId();
        $ans['hospital_admin_user_id'] = $this->getHospitalAdminUserId();
        $ans['api_url'] = $this->getApiUrl();
        $ans['access'] = $this->getAccess();
        $ans['access_code_email_title'] = $this->getAccessCodeEmailTitle();
        $ans['access_code_email_body'] = $this->getAccessCodeEmailBody();
        $ans = $ans + parent::toArray();

        return $ans;
    }

    public function getPMsFetchIntervalInMins(){
        $hospitalIntervalMins = $this->getDeletionTime();
        $hospitalIntervalMins = $hospitalIntervalMins && $hospitalIntervalMins > 0 ? $hospitalIntervalMins : 1440;
        return $hospitalIntervalMins;
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Get physicians
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhysiciansOrderByFullName()
    {
        $iterator = $this->physicians->getIterator();

        $iterator->uasort(function ($first, $second) {
            if ($first === $second) {
                return 0;
            }

            return strcasecmp($first->getFullName(), $second->getFullName());
        });
        return $iterator;
    }

    
    
    /**
     * Get patients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPatientsOrderByFullName()
    {
        $iterator = $this->patients->getIterator();

        $iterator->uasort(function ($first, $second) {
            if ($first === $second) {
                return 0;
            }

            return strcmp($first->getFullName(), $second->getFullName());
        });
        return $iterator;
    }

    public function getFollowupQuestionSet()
    {
        // hard coded to look for name 'FOLLOWUP'
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("name", 'FOLLOWUP'));

        return $this->questionSets->matching($criteria)->get(0);
    }

    public function getPatientDegreesFilter(){
        $filter = $this->getSetting('agency_patient_team_degree_filter');
        return $filter ? json_decode($filter) : explode(",",'MD,DO,DMD,DDS,DPM,PA,MBBS');
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Hospital
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Hospital
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Hospital
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Hospital
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    public function uploadLogo(UploadedFile $file)
    {
//        $this->logo = $file->getClientOriginalName();
//        $file->move(
//            $this->getUploadRootDir(),
//            $this->logo
//        );
//
//        $this->icon=null;
        $filename = sha1(uniqid(mt_rand(), true));
        $filename = $filename.'.'.$file->guessExtension();
        $file->move(
            $this->getUploadRootDir(),
            $filename);
        $this->setLogo($filename);
    }

    public  function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/hospitals';
    }

    /**
     * Set itPhone
     *
     * @param string $itPhone
     * @return Hospital
     */
    public function setItPhone($itPhone)
    {
        $this->itPhone = $itPhone;

        return $this;
    }

    /**
     * Get itPhone
     *
     * @return string 
     */
    public function getItPhone()
    {
        return $this->itPhone;
    }

    /**
     * Set itEmail
     *
     * @param string $itEmail
     * @return Hospital
     */
    public function setItEmail($itEmail)
    {
        $this->itEmail = $itEmail;

        return $this;
    }

    /**
     * Get itEmail
     *
     * @return string 
     */
    public function getItEmail()
    {
        return $this->itEmail;
    }

    /**
     * Set emrPhone
     *
     * @param string $emrPhone
     * @return Hospital
     */
    public function setEmrPhone($emrPhone)
    {
        $this->emrPhone = $emrPhone;

        return $this;
    }

    /**
     * Get emrPhone
     *
     * @return string 
     */
    public function getEmrPhone()
    {
        return $this->emrPhone;
    }

    /**
     * Set deletionTime
     *
     * @param integer $deletionTime
     * @return Hospital
     */
    public function setDeletionTime($deletionTime)
    {
        $this->deletionTime = $deletionTime;

        return $this;
    }

    /**
     * Get deletionTime
     *
     * @return integer 
     */
    public function getDeletionTime()
    {
        return $this->deletionTime;
    }

    /**
     * Set passwordTimeout
     *
     * @param integer $passwordTimeout
     * @return Hospital
     */
    public function setPasswordTimeout($passwordTimeout)
    {
        $this->passwordTimeout = $passwordTimeout;

        return $this;
    }

    /**
     * Get passwordTimeout
     *
     * @return integer 
     */
    public function getPasswordTimeout()
    {
        return $this->passwordTimeout;
    }

    /**
     * Set ceoId
     *
     * @param integer $ceoId
     * @return Hospital
     */
    public function setCeoId($ceoId)
    {
        $this->ceoId = $ceoId;

        return $this;
    }

    /**
     * Get ceoId
     *
     * @return integer 
     */
    public function getCeoId()
    {
        return $this->ceoId;
    }

    /**
     * Set ceoConnectAdministratorId
     *
     * @param integer $ceoConnectAdministratorId
     * @return Hospital
     */
    public function setCeoConnectAdministratorId($ceoConnectAdministratorId)
    {
        $this->ceoConnectAdministratorId = $ceoConnectAdministratorId;

        return $this;
    }

    /**
     * Get ceoConnectAdministratorId
     *
     * @return integer 
     */
    public function getCeoConnectAdministratorId()
    {
        return $this->ceoConnectAdministratorId;
    }

    /**
     * Set hospitalAdminUserId
     *
     * @param integer $hospitalAdminUserId
     * @return Hospital
     */
    public function setHospitalAdminUserId($hospitalAdminUserId)
    {
        $this->hospitalAdminUserId = $hospitalAdminUserId;

        return $this;
    }

    /**
     * Get hospitalAdminUserId
     *
     * @return integer 
     */
    public function getHospitalAdminUserId()
    {
        return $this->hospitalAdminUserId;
    }

    /**
     * Set scheduleNotificationEmail
     *
     * @param string $scheduleNotificationEmail
     * @return Hospital
     */
    public function setScheduleNotificationEmail($scheduleNotificationEmail)
    {
        $this->scheduleNotificationEmail = $scheduleNotificationEmail;

        return $this;
    }

    /**
     * Get scheduleNotificationEmail
     *
     * @return string 
     */
    public function getScheduleNotificationEmail()
    {
        return $this->scheduleNotificationEmail;
    }

    /**
     * Set accessCodeEmailTitle
     *
     * @param string $accessCodeEmailTitle
     * @return Hospital
     */
    public function setAccessCodeEmailTitle($accessCodeEmailTitle)
    {
        $this->accessCodeEmailTitle = $accessCodeEmailTitle;

        return $this;
    }

    /**
     * Get accessCodeEmailTitle
     *
     * @return string 
     */
    public function getAccessCodeEmailTitle()
    {
        return $this->accessCodeEmailTitle;
    }

    /**
     * Set accessCodeEmailBody
     *
     * @param string $accessCodeEmailBody
     * @return Hospital
     */
    public function setAccessCodeEmailBody($accessCodeEmailBody)
    {
        $this->accessCodeEmailBody = $accessCodeEmailBody;

        return $this;
    }

    /**
     * Get accessCodeEmailBody
     *
     * @return string 
     */
    public function getAccessCodeEmailBody()
    {
        return $this->accessCodeEmailBody;
    }
    
    
    
    /**
     * Set makeEmailBody
     *
     * @param string $makeEmailBody
     * @return Hospital
     */
    public function setMakeEmailBody($makeEmailBody)
    {
        $this->makeEmailBody = $makeEmailBody;

        return $this;
    }

    /**
     * Get makeEmailBody
     *
     * @return string 
     */
    public function getMakeEmailBody()
    {
        return $this->makeEmailBody;
    }

    /**
     * Set makeEmailSubject
     *
     * @param string $makeEmailSubject
     * @return Hospital
     */
    public function setMakeEmailSubject($makeEmailSubject)
    {
        $this->makeEmailSubject = $makeEmailSubject;

        return $this;
    }

    /**
     * Get makeEmailSubject
     *
     * @return string 
     */
    public function getMakeEmailSubject()
    {
        return $this->makeEmailSubject;
    }
    

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Hospital
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Hospital
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    
    /**
     * Set deteledAt
     *
     * @param \DateTime $deletedAt
     * @return Hospital
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
    
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add patientGroups
     *
     * @param \Navio\HospitalBundle\Entity\PatientGroup $patientGroups
     * @return Hospital
     */
    public function addPatientGroup(\Navio\HospitalBundle\Entity\PatientGroup $patientGroups)
    {
        $this->patientGroups[] = $patientGroups;

        return $this;
    }

    /**
     * Remove patientGroups
     *
     * @param \Navio\HospitalBundle\Entity\PatientGroup $patientGroups
     */
    public function removePatientGroup(\Navio\HospitalBundle\Entity\PatientGroup $patientGroups)
    {
        $this->patientGroups->removeElement($patientGroups);
    }

    /**
     * Get patientGroups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatientGroups()
    {
        return $this->patientGroups;
    }

    /**
     * Add physicianGroups
     *
     * @param \Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroups
     * @return Hospital
     */
    public function addPhysicianGroup(\Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroups)
    {
        $this->physicianGroups[] = $physicianGroups;

        return $this;
    }

    /**
     * Remove physicianGroups
     *
     * @param \Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroups
     */
    public function removePhysicianGroup(\Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroups)
    {
        $this->physicianGroups->removeElement($physicianGroups);
    }

    /**
     * Get physicianGroups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhysicianGroups()
    {
        return $this->physicianGroups;
    }

    /**
     * Add components
     *
     * @param \Navio\HospitalBundle\Entity\HospitalComponent $components
     * @return Hospital
     */
    public function addComponent(\Navio\HospitalBundle\Entity\HospitalComponent $components)
    {
        $this->components[] = $components;

        return $this;
    }

    /**
     * Remove components
     *
     * @param \Navio\HospitalBundle\Entity\HospitalComponent $components
     */
    public function removeComponent(\Navio\HospitalBundle\Entity\HospitalComponent $components)
    {
        $this->components->removeElement($components);
    }

    /**
     * Get components
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Add diagnosis
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis
     * @return Hospital
     */
    public function addDiagnosi(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis)
    {
        $this->diagnosis[] = $diagnosis;

        return $this;
    }

    /**
     * Remove diagnosis
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis
     */
    public function removeDiagnosi(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis)
    {
        $this->diagnosis->removeElement($diagnosis);
    }

    /**
     * Get diagnosis
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiagnosis()
    {
        return $this->diagnosis;
    }

    /**
     * Add settings
     *
     * @param \Navio\HospitalBundle\Entity\HospitalSetting $settings
     * @return Hospital
     */
    public function addSetting(\Navio\HospitalBundle\Entity\HospitalSetting $settings)
    {
        $this->settings[] = $settings;

        return $this;
    }

    /**
     * Remove settings
     *
     * @param \Navio\HospitalBundle\Entity\HospitalSetting $settings
     */
    public function removeSetting(\Navio\HospitalBundle\Entity\HospitalSetting $settings)
    {
        $this->settings->removeElement($settings);
    }

    /**
     * Get settings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Add onCallCategories
     *
     * @param \Navio\HospitalBundle\Entity\OnCallCategory $onCallCategories
     * @return Hospital
     */
    public function addOnCallCategory(\Navio\HospitalBundle\Entity\OnCallCategory $onCallCategories)
    {
        $this->onCallCategories[] = $onCallCategories;

        return $this;
    }

    /**
     * Remove onCallCategories
     *
     * @param \Navio\HospitalBundle\Entity\OnCallCategory $onCallCategories
     */
    public function removeOnCallCategory(\Navio\HospitalBundle\Entity\OnCallCategory $onCallCategories)
    {
        $this->onCallCategories->removeElement($onCallCategories);
    }

    /**
     * Get onCallCategories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOnCallCategories()
    {
        return $this->onCallCategories;
    }

    /**
     * Add physicians
     *
     * @param \Navio\HospitalBundle\Entity\Physician $physicians
     * @return Hospital
     */
    public function addPhysician(\Navio\HospitalBundle\Entity\Physician $physicians)
    {
        $this->physicians[] = $physicians;

        return $this;
    }

    /**
     * Remove physicians
     *
     * @param \Navio\HospitalBundle\Entity\Physician $physicians
     */
    public function removePhysician(\Navio\HospitalBundle\Entity\Physician $physicians)
    {
        $this->physicians->removeElement($physicians);
    }

    /**
     * Get physicians
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhysicians()
    {
        return $this->physicians;
    }

    /**
     * Add patients
     *
     * @param \Navio\HospitalBundle\Entity\Patient $patients
     * @return Hospital
     */
    public function addPatient(\Navio\HospitalBundle\Entity\Patient $patients)
    {
        $this->patients[] = $patients;

        return $this;
    }

    /**
     * Remove patients
     *
     * @param \Navio\HospitalBundle\Entity\Patient $patients
     */
    public function removePatient(\Navio\HospitalBundle\Entity\Patient $patients)
    {
        $this->patients->removeElement($patients);
    }

    /**
     * Get patients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatients()
    {
        return $this->patients;
    }

    /**
     * Add templates
     *
     * @param \Navio\HospitalBundle\Entity\HospitalTemplate $templates
     * @return Hospital
     */
    public function addTemplate(\Navio\HospitalBundle\Entity\HospitalTemplate $templates)
    {
        $this->templates[] = $templates;

        return $this;
    }

    /**
     * Remove templates
     *
     * @param \Navio\HospitalBundle\Entity\HospitalTemplate $templates
     */
    public function removeTemplate(\Navio\HospitalBundle\Entity\HospitalTemplate $templates)
    {
        $this->templates->removeElement($templates);
    }

    /**
     * Get templates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Add questionSets
     *
     * @param \Navio\ReportingBundle\Entity\QuestionSet $questionSets
     * @return Hospital
     */
    public function addQuestionSet(\Navio\ReportingBundle\Entity\QuestionSet $questionSets)
    {
        $this->questionSets[] = $questionSets;

        return $this;
    }

    /**
     * Remove questionSets
     *
     * @param \Navio\ReportingBundle\Entity\QuestionSet $questionSets
     */
    public function removeQuestionSet(\Navio\ReportingBundle\Entity\QuestionSet $questionSets)
    {
        $this->questionSets->removeElement($questionSets);
    }

    /**
     * Get questionSets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestionSets()
    {
        return $this->questionSets;
    }

    /**
     * Add agencies
     *
     * @param \Navio\HospitalBundle\Entity\HospitalAgency $agencies
     * @return Hospital
     */
    public function addAgency(\Navio\HospitalBundle\Entity\HospitalAgency $agencies)
    {
        $this->agencies[] = $agencies;

        return $this;
    }

    /**
     * Remove agencies
     *
     * @param \Navio\HospitalBundle\Entity\HospitalAgency $agencies
     */
    public function removeAgency(\Navio\HospitalBundle\Entity\HospitalAgency $agencies)
    {
        $this->agencies->removeElement($agencies);
    }

    /**
     * Get agencies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAgencies()
    {
        return $this->agencies;
    }

    /**
     * Add beds
     *
     * @param \Navio\HospitalBundle\Entity\Bed $beds
     * @return Hospital
     */
    public function addBed(\Navio\HospitalBundle\Entity\Bed $beds)
    {
        $this->beds[] = $beds;

        return $this;
    }

    /**
     * Remove beds
     *
     * @param \Navio\HospitalBundle\Entity\Bed $beds
     */
    public function removeBed(\Navio\HospitalBundle\Entity\Bed $beds)
    {
        $this->beds->removeElement($beds);
    }

    /**
     * Get beds
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBeds()
    {
        return $this->beds;
    }

    /**
     * Add bedStatus
     *
     * @param \Navio\HospitalBundle\Entity\BedStatus $bedStatus
     * @return Hospital
     */
    public function addBedStatus(\Navio\HospitalBundle\Entity\BedStatus $bedStatus)
    {
        $this->bedStatus[] = $bedStatus;

        return $this;
    }

    /**
     * Remove bedStatus
     *
     * @param \Navio\HospitalBundle\Entity\BedStatus $bedStatus
     */
    public function removeBedStatus(\Navio\HospitalBundle\Entity\BedStatus $bedStatus)
    {
        $this->bedStatus->removeElement($bedStatus);
    }

    /**
     * Get bedStatus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBedStatus()
    {
        return $this->bedStatus;
    }

    /**
     * Add presenceStatus
     *
     * @param \Navio\HospitalBundle\Entity\HospitalPresenceStatus $presenceStatus
     * @return Hospital
     */
    public function addPresenceStatus(\Navio\HospitalBundle\Entity\HospitalPresenceStatus $presenceStatus)
    {
        $this->presenceStatus[] = $presenceStatus;

        return $this;
    }

    /**
     * Remove presenceStatus
     *
     * @param \Navio\HospitalBundle\Entity\HospitalPresenceStatus $presenceStatus
     */
    public function removePresenceStatus(\Navio\HospitalBundle\Entity\HospitalPresenceStatus $presenceStatus)
    {
        $this->presenceStatus->removeElement($presenceStatus);
    }

    /**
     * Get presenceStatus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPresenceStatus()
    {
        return $this->presenceStatus;
    }

    /**
     * Set baseUrl
     *
     * @param string $baseUrl
     * @return Hospital
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get baseUrl
     *
     * @return string 
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Add floors
     *
     * @param \Navio\HospitalBundle\Entity\HospitalFloor $floors
     * @return Hospital
     */
    public function addFloor(\Navio\HospitalBundle\Entity\HospitalFloor $floors)
    {
        $this->floors[] = $floors;

        return $this;
    }

    /**
     * Remove floors
     *
     * @param \Navio\HospitalBundle\Entity\HospitalFloor $floors
     */
    public function removeFloor(\Navio\HospitalBundle\Entity\HospitalFloor $floors)
    {
        $this->floors->removeElement($floors);
    }

    /**
     * Get floors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFloors()
    {
        return $this->floors;
    }

    /**
     * @return mixed
     */
    public function getAssociatedHospitals()
    {
        return $this->associatedHospitals;
    }

    /**
     * @param mixed $associatedHospitals
     */
    public function setAssociatedHospitals($associatedHospitals)
    {
        $this->associatedHospitals = $associatedHospitals;
    }

    /**
     * Set apiUrl
     *
     * @param string $apiUrl
     * @return Hospital
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * Get apiUrl
     *
     * @return string 
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Add associatedHospitals
     *
     * @param \Navio\HospitalBundle\Entity\HospitalHospitals $associatedHospitals
     * @return Hospital
     */
    public function addAssociatedHospital(\Navio\HospitalBundle\Entity\HospitalHospitals $associatedHospitals)
    {
        $this->associatedHospitals[] = $associatedHospitals;

        return $this;
    }

    /**
     * Remove associatedHospitals
     *
     * @param \Navio\HospitalBundle\Entity\HospitalHospitals $associatedHospitals
     */
    public function removeAssociatedHospital(\Navio\HospitalBundle\Entity\HospitalHospitals $associatedHospitals)
    {
        $this->associatedHospitals->removeElement($associatedHospitals);
    }

    /**
     * Add departments
     *
     * @param \Navio\HospitalBundle\Entity\Department $departments
     * @return Hospital
     */
    public function addDepartment(\Navio\HospitalBundle\Entity\Department $departments)
    {
        $this->departments[] = $departments;

        return $this;
    }

    /**
     * Remove departments
     *
     * @param \Navio\HospitalBundle\Entity\Department $departments
     */
    public function removeDepartment(\Navio\HospitalBundle\Entity\Department $departments)
    {
        $this->departments->removeElement($departments);
    }

    /**
     * Get departments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * Add locations
     *
     * @param \Navio\HospitalBundle\Entity\Location $locations
     * @return Hospital
     */
    public function addLocation(\Navio\HospitalBundle\Entity\Location $locations)
    {
        $this->locations[] = $locations;

        return $this;
    }

    /**
     * Remove locations
     *
     * @param \Navio\HospitalBundle\Entity\Location $locations
     */
    public function removeLocation(\Navio\HospitalBundle\Entity\Location $locations)
    {
        $this->locations->removeElement($locations);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Add procedures
     *
     * @param \Navio\HospitalBundle\Entity\HospitalProcedure $procedures
     * @return Hospital
     */
    public function addProcedure(\Navio\HospitalBundle\Entity\HospitalProcedure $procedures)
    {
        $this->procedures[] = $procedures;

        return $this;
    }

    /**
     * Remove procedures
     *
     * @param \Navio\HospitalBundle\Entity\HospitalProcedure $procedures
     */
    public function removeProcedure(\Navio\HospitalBundle\Entity\HospitalProcedure $procedures)
    {
        $this->procedures->removeElement($procedures);
    }

    /**
     * Get procedures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedures()
    {
        return $this->procedures;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }


    /**
     * This function checks if the property specified as parameter has been turned on
     * @param $name
     * @return bool
     */
    public function isSettingTurnedOn($name)
    {
        $return_value = false;
        $settings = $this->getSettings();
        foreach($settings as $item){
            if(strcasecmp($item->getName(),$name) === 0){
                if($item->getVal() != null){
                    $property_value = strtoupper(trim($item->getVal()));
                    if (($property_value === 'TRUE')
                        || ($property_value === "1")
                        || ($property_value === 'YES')
                    ) {
                        $return_value = true;
                    }
                }
            }
        }
        return $return_value;
    }

    /**
     * Set activityLog
     *
     * @param string $activityLog
     *
     * @return Hospital
     */
    public function setActivityLog($activityLog)
    {
        $this->activityLog = $activityLog;

        return $this;
    }

    /**
     * Get activityLog
     *
     * @return string
     */
    public function getActivityLog()
    {
        return $this->activityLog;
    }

    /**
     * Set srcHost
     *
     * @param string $srcHost
     *
     * @return Hospital
     */
    public function setSrcHost($srcHost)
    {
        $this->srcHost = $srcHost;

        return $this;
    }

    /**
     * Get srcHost
     *
     * @return string
     */
    public function getSrcHost()
    {
        return $this->srcHost;
    }

    /**
     * Get the value of accessRequestText
     *
     * @return  string
     */ 
    public function getAccessRequestText()
    {
        return $this->accessRequestText;
    }

    /**
     * Set the value of accessRequestText
     *
     * @param  string  $accessRequestText
     *
     * @return  self
     */ 
    public function setAccessRequestText(string $accessRequestText = null)
    {
        $this->accessRequestText = $accessRequestText;

        return $this;
    }

    /**
     * Set newUsersEmailBody
     *
     * @param string $newUsersEmailBody
     * @return Hospital
     */
    public function setNewUsersEmailBody($newUsersEmailBody)
    {
        $this->newUsersEmailBody = $newUsersEmailBody;

        return $this;
    }

    /**
     * Get newUsersEmailBody
     *
     * @return string 
     */
    public function getNewUsersEmailBody()
    {
        return $this->newUsersEmailBody;
    }

    /**
     * Set newUsersEmailSubject
     *
     * @param string $newUsersEmailSubject
     * @return Hospital
     */
    public function setNewUsersEmailSubject($newUsersEmailSubject)
    {
        $this->newUsersEmailSubject = $newUsersEmailSubject;

        return $this;
    }

    /**
     * Get newUsersEmailSubject
     *
     * @return string 
     */
    public function getNewUsersEmailSubject()
    {
        return $this->newUsersEmailSubject;
    }
}
