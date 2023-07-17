<?php

namespace Navio\HospitalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PracticeUnite\CoreBundle\Entity\NHBaseDeletable;


/*

clean up FK to hospital.
ALTER TABLE patient DROP FOREIGN KEY patient_hospital_id_hospital_id;
ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB63DBB69;
ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB63DBB69 FOREIGN KEY (hospital_id) REFERENCES hospital (id) ON DELETE CASCADE;

CREATE TABLE `patient` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hospital_id` bigint(20) NOT NULL,
  `physician_group_id` bigint(20) DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` text COLLATE utf8_unicode_ci,
  `issues` text COLLATE utf8_unicode_ci,
  `lab` text COLLATE utf8_unicode_ci,
  `hpi` text COLLATE utf8_unicode_ci,
  `meds` text COLLATE utf8_unicode_ci,
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `questionset_id` int(11) DEFAULT NULL,
  `language_id` bigint(20) DEFAULT NULL,
  `patient_group_id` bigint(20) DEFAULT NULL,
  `admit_reason_id` bigint(20) DEFAULT NULL,
  `physician_id` bigint(20) DEFAULT NULL,
  `agency_id` bigint(20) DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `patient_id` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addr1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addr2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contactphone` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `physicianPatients_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patid_idx` (`hospital_id`,`patient_id`),
  KEY `hospital_id_idx` (`hospital_id`),
  KEY `physician_group_id_idx` (`physician_group_id`),
  KEY `IDX_1ADAD7EBAA28F080` (`questionset_id`),
  KEY `IDX_1ADAD7EB82F1BAF4` (`language_id`),
  KEY `IDX_1ADAD7EBC9D181E2` (`patient_group_id`),
  KEY `IDX_1ADAD7EBFC158CEF` (`admit_reason_id`),
  KEY `IDX_1ADAD7EBCA501031` (`physician_id`),
  KEY `IDX_1ADAD7EB53FD2D` (`physicianPatients_id`),
  KEY `IDX_1ADAD7EBCDEADB2A` (`agency_id`),
  CONSTRAINT `FK_1ADAD7EB53FD2D` FOREIGN KEY (`physicianPatients_id`) REFERENCES `physician_patient` (`id`),
  CONSTRAINT `FK_1ADAD7EB63DBB69` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`id`),
  CONSTRAINT `FK_1ADAD7EB82F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `hospital_language` (`id`),
  CONSTRAINT `FK_1ADAD7EBAA28F080` FOREIGN KEY (`questionset_id`) REFERENCES `QuestionSet` (`id`),
  CONSTRAINT `FK_1ADAD7EBC9D181E2` FOREIGN KEY (`patient_group_id`) REFERENCES `patient_group` (`id`),
  CONSTRAINT `FK_1ADAD7EBCA501031` FOREIGN KEY (`physician_id`) REFERENCES `physician` (`id`),
  CONSTRAINT `FK_1ADAD7EBCDEADB2A` FOREIGN KEY (`agency_id`) REFERENCES `hospital_agency` (`id`),
  CONSTRAINT `FK_1ADAD7EBFC158CEF` FOREIGN KEY (`admit_reason_id`) REFERENCES `hospital_reason` (`id`),
  CONSTRAINT `patient_hospital_id_hospital_id` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_physician_group_id_physician_group_id` FOREIGN KEY (`physician_group_id`) REFERENCES `physician_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3065 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci |

+----------------------+--------------+------+-----+---------+----------------+
| Field                | Type         | Null | Key | Default | Extra          |
+----------------------+--------------+------+-----+---------+----------------+
| id                   | bigint(20)   | NO   | PRI | NULL    | auto_increment |
| hospital_id          | bigint(20)   | NO   | MUL | NULL    |                |
| physician_group_id   | bigint(20)   | YES  | MUL | NULL    |                |
| first_name           | varchar(255) | NO   |     | NULL    |                |
| last_name            | varchar(255) | NO   |     | NULL    |                |
| birthdate            | date         | YES  |     | NULL    |                |
| location             | varchar(255) | NO   |     |         |                |
| notes                | text         | YES  |     | NULL    |                |
| issues               | text         | YES  |     | NULL    |                |
| lab                  | text         | YES  |     | NULL    |                |
| hpi                  | text         | YES  |     | NULL    |                |
| meds                 | text         | YES  |     | NULL    |                |
| tag                  | varchar(255) | YES  |     | NULL    |                |
| created_at           | datetime     | NO   |     | NULL    |                |
| updated_at           | datetime     | NO   |     | NULL    |                |
| deleted_at           | datetime     | YES  |     | NULL    |                |
| questionset_id       | int(11)      | YES  | MUL | NULL    |                |
| language_id          | bigint(20)   | YES  | MUL | NULL    |                |
| patient_group_id     | bigint(20)   | YES  | MUL | NULL    |                |
| admit_reason_id      | bigint(20)   | YES  | MUL | NULL    |                |
| physician_id         | bigint(20)   | YES  | MUL | NULL    |                |
| agency_id            | bigint(20)   | YES  | MUL | NULL    |                |
| gender               | varchar(1)   | YES  |     | NULL    |                |
| patient_id           | varchar(16)  | YES  |     | NULL    |                |
| addr1                | varchar(255) | YES  |     | NULL    |                |
| addr2                | varchar(255) | YES  |     | NULL    |                |
| city                 | varchar(255) | YES  |     | NULL    |                |
| state                | varchar(2)   | YES  |     | NULL    |                |
| zip                  | varchar(10)  | YES  |     | NULL    |                |
| phone                | varchar(32)  | YES  |     | NULL    |                |
| email                | varchar(256) | YES  |     | NULL    |                |
| contact              | varchar(255) | YES  |     | NULL    |                |
| contactphone         | varchar(32)  | YES  |     | NULL    |                |
| physicianPatients_id | bigint(20)   | YES  | MUL | NULL    |                |


 */

/**
 * Patient
 *
 * @ORM\Table(name="patient", 
 	indexes={@ORM\Index(name="hospital_id_idx", columns={"hospital_id"}),
		 @ORM\Index(name="physician_id_idx", columns={"physician_id"}),
		 @ORM\Index(name="physician_group_id_idx", columns={"physician_group_id"}),
		 @ORM\Index(name="patient_updated_idx", columns={"updated_at"}),
		 @ORM\Index(name="location_idx", columns={"location"}),
		 @ORM\Index(name="mrn_idx", columns={"mrn"}),
         @ORM\Index(name="pat_update_idx", columns={"deleted_at","mrn","hospital_id"})
		},
        uniqueConstraints={
 		@ORM\UniqueConstraint(name="patid_idx", columns={"hospital_id", "patient_id"})
	}
 )
 * @ORM\Entity(repositoryClass="Navio\HospitalBundle\Entity\PatientRepository")
 **/
class Patient extends \PracticeUnite\CoreBundle\Entity\NHBaseDeletable
{

    public static $ATTR_PATIENT_INFO     =  'patientInfo';
    public static $HANDOFF_ACTION_SWITCH = "Switch";
    public static $HANDOFF_ACTION_ADD    = "Add";
    public static $HANDOFF_ACTION_REMOVE = "Remove";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
	
    /**
     * @var \Navio\HospitalBundle\Entity\Hospital
     *
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\Hospital", inversedBy="patients" )
     * @ORM\JoinColumn(name="hospital_id", referencedColumnName="id", nullable=false,onDelete="CASCADE")
     */
    private $hospital;

   /**
     * @var \Navio\ReportingBundle\Entity\QuestionSet
     *
     * @ORM\ManyToOne(targetEntity="Navio\ReportingBundle\Entity\QuestionSet")
     * @ORM\JoinColumn(name="questionset_id", referencedColumnName="id")
     */
    private $questionset;

    /**
     * @ORM\OneToMany(targetEntity="Navio\MessageBundle\Entity\PrivateMessage", mappedBy="patient")
     */
    private $messages;
	
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    private $lastName;

     /**
     * @var \Date - Birth date - NOTE was Datetime in prior relase.  This could wreak havoc on timezones - assume people dont change their birthdate when moving to another timezone.
     *
     * @ORM\Column(name="birthdate", type="date", nullable=true, options={"default":null})
     */
    private $birthdate;

     /**
     * @var \text
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     */

    private $gender;
     /**
     * @var \text
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=false, options={"default":""})
     */
    private $location;
	
    /**
     * @var \text
     *
     * @ORM\Column(name="patient_id", type="string", length=32, nullable=true)
     */
    private $patient_id;
	
    /**
     * Medical Record Number ( different than PID)
     * @var \text
     *
     * @ORM\Column(name="mrn", type="string", length=32, nullable=true)
     */
    private $mrn;
    
    /**
     * Patient Class ( see https://phinvads.cdc.gov/vads/ViewValueSet.action?id=5DD34BBC-617F-DD11-B38D-00188B398520)
     * @var \text
     *
     * @ORM\Column(name="patient_class", type="string", length=1, nullable=true)
     */
    private $patientClass;
    
    /**
     * @var text
     *
     * @ORM\Column(name="notes", type="text",nullable=true)
     */
    private $notes;
    /**
     * @var text
     *
     * @ORM\Column(name="issues", type="text",nullable=true)
     */
    private $issues;
     /**
     * @var text
     *
     * @ORM\Column(name="lab", type="text",nullable=true)
     */
    private $lab;
    /**
     * @var text
     *
     * @ORM\Column(name="hpi", type="text",nullable=true)
     */
    private $hpi;
     /**
     * @var text
     *
     * @ORM\Column(name="meds", type="text",nullable=true)
     */
    private $meds;
    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=255, nullable=true)
     */
    private $tag;
	
    /**
     * @var string
     *
     * @ORM\Column(name="addr1", type="string", length=255, nullable=true)
     */
    private $addr1;
    /**
     * @var string
     *
     * @ORM\Column(name="addr2", type="string", length=255, nullable=true)
     */
    private $addr2;
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;
    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=2, nullable=true)
     */
    private $state;
    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=10, nullable=true)
     */
    private $zip;
	
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=256, nullable=true)
     */
    private $email;

    
	/**
     * @var \Navio\HospitalBundle\Entity\HospitalLanguage
     *
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\HospitalLanguage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $language;

	
	/**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    private $contact;
	
	/**
     * @var string
     *
     * @ORM\Column(name="contactphone", type="string", length=32, nullable=true)
     */
    private $contactphone;
	
	/**
     * @ORM\ManyToOne(targetEntity="PatientGroup", inversedBy="patients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="patient_group_id", referencedColumnName="id", nullable=true)
     * }) 
     */
    protected $patientGroup;	
	
    /**
     * @ORM\ManyToOne(targetEntity="HospitalReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admit_reason_id", referencedColumnName="id", nullable=true)
     * }) 
     */
    protected $admitReason;	

    
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
    protected $deletedAt=null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activated_at", type="datetime", nullable=true)
     */
     protected $activatedAt=null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activated_first_at", type="datetime", nullable=true)
     */
     private $activatedFirstAt=null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deactivated_at", type="datetime", nullable=true)
     */
    private $deactivatedAt=null;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="admitted_at", type="datetime", nullable=true)
     */
    protected $admittedAt=null;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="discharged_at", type="datetime", nullable=true)
     */
    protected $dischargedAt=null;


     /**
     * @var \Navio\HospitalBundle\Entity\Physician
     *
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\Physician")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="last_updated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $lastUpdatedBy;


    /**
     * @var  \DateTime
     *doctrine:schema:validate  
     * @ORM\Column(name="last_seen_date", type="datetime", nullable=true)
     */
    private $lastSeenDate;


    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPEND = 'suspend';
    const STATUS_DEACTIVE = 'off';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=true)
     */
    private $status;

    /**
     * @var \Navio\HospitalBundle\Entity\Physician
     *
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\Physician")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="physician_id", referencedColumnName="id")
     * })
     */
    private $physician;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\PhysicianGroup")
     * @ORM\JoinColumn(name="physician_group_id", nullable=true, onDelete="Set Null")
     */
    private $physician_group;

    /**
     * @ORM\OneToMany(targetEntity="PhysicianPatient", mappedBy="patient", cascade={"persist"})
     **/
    private $physicianPatients;
	
   /**
     * @ORM\ManyToOne(targetEntity="Navio\HospitalBundle\Entity\HospitalAgency", inversedBy="patients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agency_id", referencedColumnName="id", nullable=true)
     * })
     **/
    private $agency;	
	
    /**
     * @ORM\ManyToMany(targetEntity="HospitalDiagnosis")
     * @ORM\JoinTable(name="patient_diagnosis1",
     *      joinColumns={@ORM\JoinColumn(name="diagnosis_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
     *      )
     **/
    private $diagnosis1;	

	
    /**
     * @ORM\ManyToMany(targetEntity="HospitalDiagnosis")
     * @ORM\JoinTable(name="patient_diagnosis2",
     *      joinColumns={@ORM\JoinColumn(name="diagnosis_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
     *      )
     **/
    private $diagnosis2;	

    
    //Payer
    /**
     * @ORM\ManyToMany(targetEntity="InsurancePlan")
     * @ORM\JoinTable(name="patient_insurance",
     *      joinColumns={@ORM\JoinColumn(name="insurance_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
     *      )
     **/
    private $insurancePlan;
    
    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="patient_tag",
     *      joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
     *      )
     **/
    private $label;
       	
   /**
     * @var \Navio\HospitalBundle\Entity\User
     * @ORM\OneToOne(targetEntity="Navio\HospitalBundle\Entity\User", mappedBy="patient")
     */
    private $user;

    /**
     * The Source ot the account in URI format,eg. sf:ESDFTRFTG@acct, ldap:id@ip, csv, etc. 
     * Allows updates to be easily tracked and passwords to be routed properly. 
     * @var string
     *
     * @ORM\Column(name="acct_src", type="string", length=255, nullable=true )
     */
    private $acctSrc;
    
    /**
     * json field containing various attributes for use in processing.
     * @var string
     *
     * @ORM\Column(name="attributes", type="text", length=4096, nullable=true )
     */
    private $attributes;
    
    /**
     * One Agency may have multiple locations
     * @ORM\OneToMany(targetEntity="Navio\HospitalBundle\Entity\PatientTask", mappedBy="patient")
     */
    private $patientTask;


    public function mergeAttributes($newAttributes)
    {

        if (strlen($newAttributes) == 0) {
            return;
        }

        if (strlen($this->getAttributes()) > 0) {
            $attributes = json_decode($this->getAttributes(), true);
            $newAttributes = json_decode($newAttributes, true);
            $attributes = array_merge($attributes, $newAttributes);

            $this->setAttributes(json_encode($attributes));
        } else {
            $this->setAttributes($newAttributes);
        }
    }
    
    public function __toString() {
        return $this->getFullName();
        //TODO: put Age () after name.
    }

    public function getFullName() {
        if($this->birthdate){
            $now = new \DateTime();
            $interval = $now->diff($this->birthdate);
            $age = $interval->y;
        }
        else {
            $age=NULL;
        }
//        $age=$this->id;
        $add_id = ( $this->patient_id ) ? ' [' . $this->patient_id . ']' : FALSE;
        $add_age = ( $age ) ? ' (' . $age . ')' : FALSE;
        
        return $this->lastName . ',' . $this->firstName . $add_age . $add_id;
    }


    public function getAge(){
        $age = NULL;
        if ($this->birthdate) {
            $now = new \DateTime();
            $interval = $now->diff($this->birthdate);
            $age = $interval->y;
        } 
        return $age;
    }

    private $zPhysicians;

    public function setZPhysicians($p) {
        $this->zPhysicians = $p;
    }

    public function getZPhysicians() {
        return $this->zPhysicians;
    }

    /**
     * @var bigint
     *
     * @ORM\Column(name="badge_count", type="bigint", nullable=true, options={"default":"0"})
     */
    private $badgeCount;

    /**
     * @var string
     */
    protected $activityLog;
    
    /**
     * Set status indicating active and daily reports. 
     *
     * @param string $status
     * @return Patient
     */
    public function setStatusActive()    
    {
        $now = new \DateTime("now",new \DateTimeZone("UTC"));
        if($this->activatedFirstAt==null){
            $this->activatedFirstAt=$now;
        }
        $this->activatedAt=$now;     
        $this->deactivatedAt=null;        
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    public function getNonHippaIdentifier()
    {
        $name = substr($this->getFirstName(), 0, 1) . substr($this->getLastName(), 0, 1);
        if($this->getMrn() != null)
        {
            $name .= " (mrn: {$this->getMrn()})";
        }
        return $name;
    }

    /**
     * Set status - temporary or automatic suspension
     *
     * @param string $status
     * @return Patient
     */
    public function setStatusSuspend()
    {
        $this->status = self::STATUS_SUSPEND;
        return $this;
    }
    /**
     * Set status Deactivate - inactive and out of program ( for now) 
     *
     * @param string $status
     * @return Patient
     */
    public function setStatusDeactivate()
    {
        $now = new \DateTime("now",new \DateTimeZone("UTC"));
        $this->activatedAt=null;     
        $this->deactivatedAt=$now;    
        
        $this->status = self::STATUS_DEACTIVE;
        return $this;
    }
    
    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        if(!$this->status){$this->status="active";}        
        else if($this->status==""){$this->status="active";}
        return $this->status;
    }

    /**
     * Columns to be sent in JSON
     * @param $alias
     * @return string
     */
    public static function getSyncColumns($alias){
        return sprintf('%1$s.id,%1$s.hospital_id,%1$s.first_name,%1$s.last_name,%1$s.birthdate,%1$s.location,%1$s.notes,%1$s.issues,%1$s.lab,%1$s.hpi,
        %1$s.meds,%1$s.tag,%1$s.gender,%1$s.addr1,%1$s.addr2,%1$s.created_at,%1$s.updated_at,%1$s.deleted_at', $alias);
    }

    public static function getSyncColumnsForPatientHandOff($alias){
        return sprintf('%1$s.id,%1$s.hospital_id,%1$s.first_name,%1$s.last_name,%1$s.birthdate,%1$s.location,%1$s.notes,%1$s.issues,%1$s.lab,%1$s.hpi,
        %1$s.meds,%1$s.tag,%1$s.gender,%1$s.addr1,%1$s.addr2,%1$s.created_at,%1$s.updated_at,%1$s.deleted_at,%1$s.last_updated_by,%1$s.last_seen_date', $alias);
    }

    public function updatedNow(){
        $this->updatedAt=new \DateTime("now",new \DateTimeZone("UTC"));
    }    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt=$this->updatedAt=new \DateTime("now",new \DateTimeZone("UTC"));
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->diagnosis1 = new \Doctrine\Common\Collections\ArrayCollection();
        $this->diagnosis2 = new \Doctrine\Common\Collections\ArrayCollection();
        $this->physicianPatients = new \Doctrine\Common\Collections\ArrayCollection();
    }




     public function toFullJson(){
        $json['id']             = $this->getId();
        $json['hospital_id']    = $this->getHospital()->getId();
        $json['birthdate']      = $this->getBirthdate() ? $this->getBirthdate()->format(NHBaseDeletable::$DATETIME_FORMAT_FOR_JSON) : null;
        $json['created_at']     = $this->getCreatedAt() ? $this->getCreatedAt()->format(NHBaseDeletable::$DATETIME_FORMAT_FOR_JSON) : null;
        $json['updated_at']     = $this->getUpdatedAt() ? $this->getUpdatedAt()->format(NHBaseDeletable::$DATETIME_FORMAT_FOR_JSON) : null;
        $json['deleted_at']     = $this->getDeletedAt() ? $this->getDeletedAt()->format(NHBaseDeletable::$DATETIME_FORMAT_FOR_JSON) : null;
        $json['first_name']     = $this->getFirstName();
        $json['last_name']      = $this->getLastName();
        $json['hpi']            = $this->getHpi();
        $json['issues']         = $this->getIssues();
        $json['lab']            = $this->getLab();
        $json['meds']           = $this->getMeds();
        $json['notes']          = $this->getNotes();
        $json['gender']         = $this->getGender();
        $json['location']       = $this->getLocation();
        $json['tag']            = $this->getTag();

        $handOffEnabled = $this->getHospital()->getSetting(HospitalSetting::$SETTING_PATIENT_INFORMATION);
        if ($handOffEnabled) {
            $json['last_updated_by']= $this->getLastUpdatedBy() ? $this->getLastUpdatedBy()->getId() : null;
            $json['last_seen_date'] = $this->getLastSeenDate()  ? $this->getLastSeenDate()->format(NHBaseDeletable::$DATETIME_FORMAT_FOR_JSON) : null;
        }

        return $json;
    }
    // AUTO GENERATED FROM HERE ONWARD

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
     * Set firstName
     *
     * @param string $firstName
     * @return Patient
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Patient
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Patient
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Patient
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Patient
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set patient_id
     *
     * @param string $patientId
     * @return Patient
     */
    public function setPatientId($patientId)
    {
        $this->patient_id = $patientId;

        return $this;
    }

    /**
     * Get patient_id
     *
     * @return string 
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Patient
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set issues
     *
     * @param string $issues
     * @return Patient
     */
    public function setIssues($issues)
    {
        $this->issues = $issues;

        return $this;
    }

    /**
     * Get issues
     *
     * @return string 
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Set lab
     *
     * @param string $lab
     * @return Patient
     */
    public function setLab($lab)
    {
        $this->lab = $lab;

        return $this;
    }

    /**
     * Get lab
     *
     * @return string 
     */
    public function getLab()
    {
        return $this->lab;
    }

    /**
     * Set hpi
     *
     * @param string $hpi
     * @return Patient
     */
    public function setHpi($hpi)
    {
        $this->hpi = $hpi;

        return $this;
    }

    /**
     * Get hpi
     *
     * @return string 
     */
    public function getHpi()
    {
        return $this->hpi;
    }

    /**
     * Set meds
     *
     * @param string $meds
     * @return Patient
     */
    public function setMeds($meds)
    {
        $this->meds = $meds;

        return $this;
    }

    /**
     * Get meds
     *
     * @return string 
     */
    public function getMeds()
    {
        return $this->meds;
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return Patient
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set addr1
     *
     * @param string $addr1
     * @return Patient
     */
    public function setAddr1($addr1)
    {
        $this->addr1 = $addr1;

        return $this;
    }

    /**
     * Get addr1
     *
     * @return string 
     */
    public function getAddr1()
    {
        return $this->addr1;
    }

    /**
     * Set addr2
     *
     * @param string $addr2
     * @return Patient
     */
    public function setAddr2($addr2)
    {
        $this->addr2 = $addr2;

        return $this;
    }

    /**
     * Get addr2
     *
     * @return string 
     */
    public function getAddr2()
    {
        return $this->addr2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Patient
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
     * @return Patient
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
     * Set zip
     *
     * @param string $zip
     * @return Patient
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string 
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Patient
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Patient
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return Patient
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set contactphone
     *
     * @param string $contactphone
     * @return Patient
     */
    public function setContactphone($contactphone)
    {
        $this->contactphone = $contactphone;

        return $this;
    }

    /**
     * Get contactphone
     *
     * @return string 
     */
    public function getContactphone()
    {
        return $this->contactphone;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Patient
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
     * @return Patient
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Patient
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set hospital
     *
     * @param \Navio\HospitalBundle\Entity\Hospital $hospital
     * @return Patient
     */
    public function setHospital(\Navio\HospitalBundle\Entity\Hospital $hospital)
    {
        $this->hospital = $hospital;

        return $this;
    }

    /**
     * Get hospital
     *
     * @return \Navio\HospitalBundle\Entity\Hospital 
     */
    public function getHospital()
    {
        return $this->hospital;
    }

    /**
     * Set questionset
     *
     * @param \Navio\ReportingBundle\Entity\QuestionSet $questionset
     * @return Patient
     */
    public function setQuestionset(\Navio\ReportingBundle\Entity\QuestionSet $questionset = null)
    {
        $this->questionset = $questionset;

        return $this;
    }

    /**
     * Get questionset
     *
     * @return \Navio\ReportingBundle\Entity\QuestionSet 
     */
    public function getQuestionset()
    {
        return $this->questionset;
    }

    /**
     * Add messages
     *
     * @param \Navio\MessageBundle\Entity\PrivateMessage $messages
     * @return Patient
     */
    public function addMessage(\Navio\MessageBundle\Entity\PrivateMessage $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Navio\MessageBundle\Entity\PrivateMessage $messages
     */
    public function removeMessage(\Navio\MessageBundle\Entity\PrivateMessage $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set language
     *
     * @param \Navio\HospitalBundle\Entity\HospitalLanguage $language
     * @return Patient
     */
    public function setLanguage(\Navio\HospitalBundle\Entity\HospitalLanguage $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return \Navio\HospitalBundle\Entity\HospitalLanguage 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set patientGroup
     *
     * @param \Navio\HospitalBundle\Entity\PatientGroup $patientGroup
     * @return Patient
     */
    public function setPatientGroup(\Navio\HospitalBundle\Entity\PatientGroup $patientGroup = null)
    {
        $this->patientGroup = $patientGroup;

        return $this;
    }

    /**
     * Get patientGroup
     *
     * @return \Navio\HospitalBundle\Entity\PatientGroup 
     */
    public function getPatientGroup()
    {
        return $this->patientGroup;
    }

    /**
     * Set admitReason
     *
     * @param \Navio\HospitalBundle\Entity\HospitalReason $admitReason
     * @return Patient
     */
    public function setAdmitReason(\Navio\HospitalBundle\Entity\HospitalReason $admitReason = null)
    {
        $this->admitReason = $admitReason;

        return $this;
    }

    /**
     * Get admitReason
     *
     * @return \Navio\HospitalBundle\Entity\HospitalReason 
     */
    public function getAdmitReason()
    {
        return $this->admitReason;
    }

    /**
     * Set physician
     *
     * @param \Navio\HospitalBundle\Entity\Physician $physician
     * @return Patient
     */
    public function setPhysician(\Navio\HospitalBundle\Entity\Physician $physician = null)
    {
        $this->physician = $physician;

        return $this;
    }

    /**
     * Get physician
     *
     * @return \Navio\HospitalBundle\Entity\Physician 
     */
    public function getPhysician()
    {
        return $this->physician;
    }

    /**
     * Set physician_group
     *
     * @param \Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroup
     * @return Patient
     */
    public function setPhysicianGroup(\Navio\HospitalBundle\Entity\PhysicianGroup $physicianGroup = null)
    {
        $this->physician_group = $physicianGroup;

        return $this;
    }

    /**
     * Get physician_group
     *
     * @return \Navio\HospitalBundle\Entity\PhysicianGroup 
     */
    public function getPhysicianGroup()
    {
        return $this->physician_group;
    }

    /**
     * Add physicianPatients
     *
     * @param \Navio\HospitalBundle\Entity\PhysicianPatient $physicianPatients
     * @return Patient
     */
    public function addPhysicianPatient(\Navio\HospitalBundle\Entity\PhysicianPatient $physicianPatients)
    {
        $this->physicianPatients[] = $physicianPatients;

        return $this;
    }

    /**
     * Remove physicianPatients
     *
     * @param \Navio\HospitalBundle\Entity\PhysicianPatient $physicianPatients
     */
    public function removePhysicianPatient(\Navio\HospitalBundle\Entity\PhysicianPatient $physicianPatients)
    {
        $this->physicianPatients->removeElement($physicianPatients);
    }

    /**
     * Get physicianPatients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhysicianPatients()
    {
        return $this->physicianPatients;
    }

    /**
     * Set agency
     *
     * @param \Navio\HospitalBundle\Entity\HospitalAgency $agency
     * @return Patient
     */
    public function setAgency(\Navio\HospitalBundle\Entity\HospitalAgency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \Navio\HospitalBundle\Entity\HospitalAgency 
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Add diagnosis1
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis1
     * @return Patient
     */
    public function addDiagnosis1(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis1)
    {
        $this->diagnosis1[] = $diagnosis1;

        return $this;
    }

    /**
     * Remove diagnosis1
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis1
     */
    public function removeDiagnosis1(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis1)
    {
        $this->diagnosis1->removeElement($diagnosis1);
    }

    /**
     * Get diagnosis1
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiagnosis1()
    {
        return $this->diagnosis1;
    }

    /**
     * Add diagnosis2
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis2
     * @return Patient
     */
    public function addDiagnosis2(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis2)
    {
        $this->diagnosis2[] = $diagnosis2;

        return $this;
    }

    /**
     * Remove diagnosis2
     *
     * @param \Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis2
     */
    public function removeDiagnosis2(\Navio\HospitalBundle\Entity\HospitalDiagnosis $diagnosis2)
    {
        $this->diagnosis2->removeElement($diagnosis2);
    }

    /**
     * Get diagnosis2
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiagnosis2()
    {
        return $this->diagnosis2;
    }

    /**
     * Set user
     *
     * @param \Navio\HospitalBundle\Entity\User $user
     * @return Patient
     */
    public function setUser(\Navio\HospitalBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Navio\HospitalBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set badgeCount
     *
     * @param integer $badgeCount
     * @return Patient
     */
    public function setBadgeCount($badgeCount)
    {
        $this->badgeCount = $badgeCount;

        return $this;
    }

    /**
     * Get badgeCount
     *
     * @return integer 
     */
    public function getBadgeCount()
    {
        return $this->badgeCount;
    }

    /**
     * Set activatedAt
     *
     * @param \DateTime $activatedAt
     * @return Patient
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Get activatedAt
     *
     * @return \DateTime 
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Set activatedFirstAt
     *
     * @param \DateTime $activatedFirstAt
     * @return Patient
     */
    public function setActivatedFirstAt($activatedFirstAt)
    {
        $this->activatedFirstAt = $activatedFirstAt;

        return $this;
    }

    /**
     * Get activatedFirstAt
     *
     * @return \DateTime 
     */
    public function getActivatedFirstAt()
    {
        return $this->activatedFirstAt;
    }

    /**
     * Set deactivatedAt
     *
     * @param \DateTime $deactivatedAt
     * @return Patient
     */
    public function setDeactivatedAt($deactivatedAt)
    {
        $this->deactivatedAt = $deactivatedAt;

        return $this;
    }

    /**
     * Get deactivatedAt
     *
     * @return \DateTime 
     */
    public function getDeactivatedAt()
    {
        return $this->deactivatedAt;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Patient
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set acctSrc
     *
     * @param string $acctSrc
     * @return Patient
     */
    public function setAcctSrc($acctSrc)
    {
        $this->acctSrc = $acctSrc;

        return $this;
    }

    /**
     * Get acctSrc
     *
     * @return string 
     */
    public function getAcctSrc()
    {
        return $this->acctSrc;
    }

    /**
     * Set attributes
     *
     * @param string $attributes
     * @return Patient
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get attributes
     *
     * @return string 
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Set mrn
     *
     * @param string $mrn
     * @return Patient
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;

        return $this;
    }

    /**
     * Get mrn
     *
     * @return string 
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * Set patientClass
     *
     * @param string $patientClass
     * @return Patient
     */
    public function setPatientClass($patientClass)
    {
        $this->patientClass = $patientClass;

        return $this;
    }

    /**
     * Get patientClass
     *
     * @return string 
     */
    public function getPatientClass()
    {
        return $this->patientClass;
    }

    /**
     * Set admittedAt
     *
     * @param \DateTime $admittedAt
     * @return Patient
     */
    public function setAdmittedAt($admittedAt)
    {
        $this->admittedAt = $admittedAt;

        return $this;
    }

    /**
     * Get admittedAt
     *
     * @return \DateTime 
     */
    public function getAdmittedAt()
    {
        return $this->admittedAt;
    }

    /**
     * Set dischargedAt
     *
     * @param \DateTime $dischargedAt
     * @return Patient
     */
    public function setDischargedAt($dischargedAt)
    {
        $this->dischargedAt = $dischargedAt;

        return $this;
    }

    /**
     * Get dischargedAt
     *
     * @return \DateTime 
     */
    public function getDischargedAt()
    {
        return $this->dischargedAt;
    }

    /**
     * Set activityLog
     *
     * @param string $activityLog
     * @return Patient
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
     * Add insurancePlan
     *
     * @param \Navio\HospitalBundle\Entity\InsurancePlan $insurancePlan
     * @return Patient
     */
    public function addInsurancePlan(\Navio\HospitalBundle\Entity\InsurancePlan $insurancePlan)
    {
        $this->insurancePlan[] = $insurancePlan;

        return $this;
    }

    /**
     * Remove insurancePlan
     *
     * @param \Navio\HospitalBundle\Entity\InsurancePlan $insurancePlan
     */
    public function removeInsurancePlan(\Navio\HospitalBundle\Entity\InsurancePlan $insurancePlan)
    {
        $this->insurancePlan->removeElement($insurancePlan);
    }

    /**
     * Get insurancePlan
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInsurancePlan()
    {
        return $this->insurancePlan;
    }

    /**
     * Add label
     *
     * @param \Navio\HospitalBundle\Entity\Tag $label
     * @return Patient
     */
    public function addLabel(\Navio\HospitalBundle\Entity\Tag $label)
    {
        $this->label[] = $label;

        return $this;
    }

    /**
     * Remove label
     *
     * @param \Navio\HospitalBundle\Entity\Tag $label
     */
    public function removeLabel(\Navio\HospitalBundle\Entity\Tag $label)
    {
        $this->label->removeElement($label);
    }

    /**
     * Get label
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLabel()
    {
        return $this->label;
    }
    /**
     * @var string
     */
    protected $srcHost;


    /**
     * Set srcHost
     *
     * @param string $srcHost
     * @return Patient
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
     * Get the value of lastUpdatedBy
     *
     * @return  \Navio\HospitalBundle\Entity\Physician
     */ 
    public function getLastUpdatedBy()
    {
        return $this->lastUpdatedBy;
    }

    /**
     * Set the value of lastUpdatedBy
     *
     * @param  \Navio\HospitalBundle\Entity\Physician  $lastUpdatedBy
     *
     * @return  self
     */ 
    public function setLastUpdatedBy(\Navio\HospitalBundle\Entity\Physician $lastUpdatedBy)
    {
        $this->lastUpdatedBy = $lastUpdatedBy;

        return $this;
    }

    /**
     * Get *doctrine:schema:validate
     *
     * @return  \DateTime
     */ 
    public function getLastSeenDate()
    {
        return $this->lastSeenDate;
    }

    /**
     * Set *doctrine:schema:validate
     *
     * @param  \DateTime  $lastSeenDate  *doctrine:schema:validate
     *
     * @return  self
     */ 
    public function setLastSeenDate(\DateTime $lastSeenDate)
    {
        $this->lastSeenDate = $lastSeenDate;

        return $this;
    }
}
