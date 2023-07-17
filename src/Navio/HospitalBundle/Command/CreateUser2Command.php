<?php
namespace Navio\HospitalBundle\Command;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Util\UserManipulator;
use Navio\Utils\Utils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use FOS\UserBundle\Model\User;
use FOS\UserBundle\Command\CreateUserCommand as BaseCommand;

use Navio\HospitalBundle\Entity\Physician;
use Navio\HospitalBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateUser2Command extends BaseCommand
{   
    const CMDNAME = 'navio:user:create2';
    const NULL_VALUE_STRING = '--null--';
    private $container;

    public function __construct(UserManipulator $userManipulator, ContainerInterface $container_)
    {
        parent::__construct($userManipulator);
        $this->container = $container_;
    }


    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName(self::CMDNAME)
            ->getDefinition()->addArguments(array(
                new InputArgument('firstname', InputArgument::REQUIRED, 'The firstname'),
                new InputArgument('lastname', InputArgument::REQUIRED, 'The lastname'),
//                new InputArgument('email', InputArgument::REQUIRED, 'The Email'),
//                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                ));
        ;
        $this
            ->addOption('hospitalnumber',null,InputOption::VALUE_REQUIRED,'Hospital NUmber',null)
            ->addOption('hospitalname',null,InputOption::VALUE_REQUIRED,'Hospital Name',null)
            ->addOption('office_phone',null,InputOption::VALUE_OPTIONAL,'Directory NUmber',null)
            ->addOption('cell_phone',null,InputOption::VALUE_OPTIONAL,'Directory NUmber',null)
            ->addOption('department',null,InputOption::VALUE_OPTIONAL,'Department',null)
            ->addOption('agency',null,InputOption::VALUE_OPTIONAL,'Practice / Agency',null)
            ->addOption('location',null,InputOption::VALUE_OPTIONAL,'location Code',null)
            ->addOption('degree',null,InputOption::VALUE_OPTIONAL,'degree',null)
            ->addOption('specialty',null,InputOption::VALUE_OPTIONAL,'specialty',null)
            ->addOption('subspecialty',null,InputOption::VALUE_OPTIONAL,'subspecialty',null)
            ->addOption('officeaddress1',null,InputOption::VALUE_OPTIONAL,'Office Address',null)
            ->addOption('officeaddress2',null,InputOption::VALUE_OPTIONAL,'Office Address line 2',null)
            ->addOption('city',null,InputOption::VALUE_OPTIONAL,'City',null)
            ->addOption('state',null,InputOption::VALUE_OPTIONAL,'State Code',null)
            ->addOption('zip',null,InputOption::VALUE_OPTIONAL,'Zip Code',null)
            ->addOption('cellHidden',null,InputOption::VALUE_OPTIONAL,'Hide users cell phone Code',null)
            ->addOption('docnumber',null,InputOption::VALUE_OPTIONAL,'Identifier for Hl7 report matching',null)
            ->addOption('docnumber2',null,InputOption::VALUE_OPTIONAL,'Identifier2 for Hl7 report matching',null)
            ->addOption('npiNumber',null,InputOption::VALUE_OPTIONAL,'NPI',null)
            ->addOption('sourceuri',null,InputOption::VALUE_OPTIONAL,'Data Source and ID',null)
            ->addOption('attributes',null,InputOption::VALUE_OPTIONAL,'Jsonstring of extras',null)
            ->addOption('mood',null,InputOption::VALUE_OPTIONAL,'Mood - initial only',null)
            ->addOption('epoch_create_date',null,InputOption::VALUE_OPTIONAL,'If physician gets created, should the creation date get backdated to Epoch date time',null)
            ->addOption('skip_update_date',null,InputOption::VALUE_OPTIONAL,'If physician gets updated, should the update date not change?',null)
            ->addOption('roles',null,InputOption::VALUE_OPTIONAL,'Roles - comma separated list of fos-user roles.',null)
            ->addOption('no_docnumber_substitute',null,InputOption::VALUE_OPTIONAL,'Do not set docNumber if not provided',null)
            ->addOption('convert_from_old_src',null,InputOption::VALUE_OPTIONAL,'If external id or acct_src format get changed set true with',false)
            ->addOption('old_sourceuri',null,InputOption::VALUE_OPTIONAL,'Old Data Source and ID',null)
            ->addOption('allow_email_dup',null,InputOption::VALUE_OPTIONAL,'If true, will not find users by email',false)
            ;

        $this->setHelp(<<<EOT
            username:       sAMAccountName
            first_name:     givenName
            last_name:      sn
            department:     department
            office_phone:   telephoneNumber
            cell_phone:     mobile
            email:          mail
            #           degree:
            #
            # specialty             
            # sub_specialty         
            # office_address_line1  |
            # office_address_line2  | 
            # city                  | 
            # state                 | 
            # zip                   | 
            # npi_number            | 
            # doc_number            | 
            # agency_id (Practice)  | 
            # gender                | 
            # image                 |    
EOT
            );
    }
 
    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bExists    = false; 
        $username   = trim($input->getArgument('username'));
        $docnumber  = trim($input->getOption('docnumber'));
        $docnumber2  = trim($input->getOption('docnumber2'));
        if(empty($docnumber) && trim($input->getOption('no_docnumber_substitute')) == 0){
            $docnumber  = $username;
        }
        $email          = trim($input->getArgument('email'));
        //not used yet $password       = trim($input->getArgument('password'));
        $firstname      = trim($input->getArgument('firstname'));
        $lastname       = trim($input->getArgument('lastname'));
        
        $hospital       = trim($input->getOption('hospitalnumber')); 
        $hospitalname   = trim($input->getOption('hospitalname')); 
        
        $office_phone   = trim($input->getOption('office_phone'));
        $cell_phone     = trim($input->getOption('cell_phone'));
        $department     = trim($input->getOption('department'));
        $agency         = trim($input->getOption('agency'));
        $location       = trim($input->getOption('location'));

        $degree         = trim($input->getOption('degree'));
        $specialty      = trim($input->getOption('specialty'));
        $subspecialty   = trim($input->getOption('subspecialty'));
        $officeaddress1 = trim($input->getOption('officeaddress1'));
        $officeaddress2 = trim($input->getOption('officeaddress2'));
        $city           = trim($input->getOption('city'));
        $state          = trim($input->getOption('state'));
        $zip            = trim($input->getOption('zip'));
        
        $npiNumber      = trim($input->getOption('npiNumber'));
        $cellHidden     = trim($input->getOption('cellHidden'));
        
        $sourceuri      = trim($input->getOption('sourceuri'));
        $attributes     = trim($input->getOption('attributes'));
        $mood           = trim($input->getOption('mood'));
        $roles          = trim($input->getOption('roles'));
        $password       = trim($input->getArgument('password'));
        $allowEmailDups = $input->getOption('allow_email_dup');
        $convertFromOldSrc = $input->getOption('convert_from_old_src');
        $oldsourceuri   = trim($input->getOption('old_sourceuri'));


        $set_epoch_create_date = false;
        $skip_update_date = false;
        if (trim($input->getOption('epoch_create_date'))==1) {
            $set_epoch_create_date = true;
            $skip_update_date = true;
        }
        else if (trim($input->getOption('skip_update_date'))==1) {
            $skip_update_date = true;
        }
        
        //Setup
        $em = $this->container->get('doctrine')->getManager('default');
        $hRepo = $em->getRepository('HospitalBundle:Hospital');        
        $pRepo = $em->getRepository('HospitalBundle:Physician');        
        $lRepo = $em->getRepository('HospitalBundle:Location');        
        $aRepo = $em->getRepository('HospitalBundle:HospitalAgency');
                
        if($hospitalname!=NULL && strlen($hospitalname)>0){
            $hosp = $hRepo->findByName($hospitalname);            
        }
        else {
            $hosp = $hRepo->find($hospital);            
        }
        if(!$hosp){
            $output->writeln('Hospital not found:' . $hospitalname."/".$hospital);
        }

        $srcfield="acctSrc";
        $phy = $pRepo->findOneBy(array(
                    'hospital'=>$hospital,
                    'acctSrc'=>$sourceuri,
                    ));

        if($convertFromOldSrc && !empty($oldsourceuri)){
            $phy = $pRepo->findOneBy(array(
                'hospital'=>$hospital,
                'acctSrc'=>$oldsourceuri,
            ));
        }

        if($phy){
            $output->writeln("Found user by {$srcfield}: {$phy->getAcctSrc()}");
            $bExists = true;
        }
        if($phy==null && !$allowEmailDups){
            // does the physician exist?  with email
            $phy = $pRepo->findOneBy(array(
                        'hospital'=>$hospital,
                        'email'=>$email,
                        ));
            if($phy){
                $output->writeln( "Found user by email: {$phy->getId()}");
                $bExists = true;
            }
            else {
                // does the physician exist?  with no email and name
                $phy = $pRepo->findOneBy(array(
                            'hospital'=>$hospital,
                            'email'=>'',
                            'firstName'=>$firstname,
                            'lastName'=>$lastname,
                            ));
                if($phy){
                    $output->writeln("Found user by name: {$phy->getId()} {$phy->getAcctSrc()}");
                    $bExists = true;
                }
            }
        }
        if($phy==null){
            //If $sourceuri supplied, make sure not in use.
            $output->writeln('Creating new employee'.' \n');
            $phy = new Physician();
            if ($set_epoch_create_date){
                $epoch_date = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00', new \DateTimeZone("UTC"));
                $phy->setCreatedAt($epoch_date);
                $phy->setUpdatedAt($epoch_date);
            }
            $phy->setEmail($email);    
            $phy->setHospital($hosp);
        }
        else {
            if ($phy) {
                //if $sourceuri is supplied, make sure it matched existing record.
                if (strlen($sourceuri)>0) {
                    $origSrc = $phy->getAcctSrc();
                    if (strlen($origSrc)>1 && $origSrc !=  $sourceuri && !$convertFromOldSrc) {
                        syslog(LOG_WARNING,"{CMDNAME} REJECT SF Source not equal user by name: {$phy->getId()} {$email}  {$phy->getLastName()}");
                        $output->writeln('\n\n REJECT SF Source not equal user by name: ' . $phy->getId()." ".$email." ".$phy->getLastName()." \n\n");
                        exit(-1);
                    }
                }
            }
            $phy->setDeletedAt(null);
        }

        if($location == $this::NULL_VALUE_STRING) {
            $loc = $this::NULL_VALUE_STRING;
        } else {
            $loc = $lRepo->findOneBy(array(
                'hospital'=>$hospital,
                'abbrev'=>$location,
            ));
        }

        // does the agency exist? 
        if($agency==NULL || strlen($agency)==0){
            if(strcasecmp($this::NULL_VALUE_STRING, $department) != 0) {
                $agency = $department;
            }
        }

        if($agency!=NULL && strlen($agency)>0){
            if(strcasecmp($this::NULL_VALUE_STRING, $agency) == 0)
            {
                $phy->setAgency(null);
            } else {
                $agencyRow = $aRepo->findOneBy(array(
                    'hospital'=>$hospital,
                    'name'=>$agency,
                    'deletedAt'=>NULL,
                ));
                if($agencyRow){
                    $phy->setAgency($agencyRow);
                    $output->writeln("Practice Found by name: {$agency} ({$agencyRow->getId()})");
                }
                else {
                    $output->writeln("Practice Not Found by name: {$agency}");
                }
            }
        }

        $phy->setEmail($email);
        $phy->setFirstName($firstname);
        $phy->setLastName($lastname);
        if(strlen($docnumber) > 0)
        {
            if($docnumber == $this::NULL_VALUE_STRING) $docnumber = null;
            $phy->setDocNumber($docnumber);
        }
        if(strlen($docnumber2) > 0) {
            if($docnumber2 == $this::NULL_VALUE_STRING) $docnumber2 = null;
            $phy->setDocNumber2($docnumber2);
        }
        if(strlen($npiNumber)>0){
            if($npiNumber == $this::NULL_VALUE_STRING) $npiNumber = null;
            $phy->setNpiNumber($npiNumber);
        }
        if(strlen($office_phone)>0){
            if($office_phone == $this::NULL_VALUE_STRING) $office_phone = null;
            $phy->setOfficePhone($office_phone);
        }
        if(strlen($cell_phone)>0){
            if($cell_phone == $this::NULL_VALUE_STRING) $cell_phone = null;
            $phy->setCellPhone($cell_phone);
        }
        if(strlen($department)>0){
            if($department == $this::NULL_VALUE_STRING) $department = null;
            $phy->setDepartment($department);
        }
	    if($loc){
            if($loc == $this::NULL_VALUE_STRING) $loc = null;
            $phy->setLocation($loc);
        } else if($location == self::NULL_VALUE_STRING){
            $phy->setLocation(null);
        }

        if(strlen($degree)>0){
            if($degree == $this::NULL_VALUE_STRING) $degree = null;
            $phy->setDegree($degree);
        }
        if(strlen($specialty)>0){
            if($specialty == $this::NULL_VALUE_STRING) $specialty = null;
            $phy->setSpecialty($specialty);
        }
        if(strlen($subspecialty)>0){
            if($subspecialty == $this::NULL_VALUE_STRING) $subspecialty = null;
            $phy->setSubSpecialty($subspecialty);
        }
        if(strlen($officeaddress1)>0){
            if($officeaddress1 == $this::NULL_VALUE_STRING) $officeaddress1 = null;
            $phy->setOfficeAddressLine1($officeaddress1);
        }
        if(strlen($officeaddress2)>0){
            if($officeaddress2 == $this::NULL_VALUE_STRING) $officeaddress2 = null;
            $phy->setOfficeAddressLine2($officeaddress2);
        }
        if(strlen($city)>0){
            if($city == $this::NULL_VALUE_STRING) $city = null;
            $phy->setCity($city);
        }
        if(strlen($zip)>0){
            if($zip == $this::NULL_VALUE_STRING) $zip = null;
            $phy->setZip($zip);
        }
        if(strlen($state)>0){
            if($state == $this::NULL_VALUE_STRING) $state = null;
            if(strlen($state) > 2)
            {
                $state = Utils::StateAbbr($state);
            }
            $phy->setState($state);
        }
        if(!$bExists){
            if($cellHidden=="1"){
                $phy->setCellPhoneHidden(true);            
            }
            else {
                $phy->setCellPhoneHidden(false);
            }
        }

        if(strlen($sourceuri)>0){
            if($sourceuri == $this::NULL_VALUE_STRING) $sourceuri = null;
            $phy->setAcctSrc($sourceuri);
        }
        if(strlen($attributes)>0){
            $phy->mergeAttributes($attributes);
        }
        if(strlen($mood)>0 && strlen($phy->getMood())==0){
            $phy->setMood($mood);            
        }
        
        $em->persist($phy);

        // is there a fos_user with this id? 
        $fosUser = $phy->getUser();

        // if fosUser Exists, must make sure is not deleted. 
        if ($fosUser) {
            // you are changing a record that has a login, what needs to happen? 
            // if username contains '.deleted' must resurrect
            $fosUserName = $fosUser->getUsername();
            if (strpos($fosUserName,".deleted") !== FALSE ) {
                //resurrect! 
                $fosUser->setEmail($email);
                $fosUser->setEmployee($phy); 
                $fosUser->setEnabled(true);
                $fosUser->setHospital($hosp);
                $fosUser->setIsActive(true);
                $fosUser->setLastnameFirstname($lastname . " " . $firstname);
                $fosUser->setPassword(\Navio\HospitalBundle\Entity\User::randString(32));                            
                $fosUser->setType("Employee");
                $fosUser->setUsername($username);
                $em->persist($fosUser);
            } else {
                $fosUser->setUsername($username);
                $fosUser->setEmail($email);
                $em->persist($fosUser);
            }
        }

        if (strlen($roles)>1) {
            // create fos user if not existing. 
            if(!$fosUser) {
                $fosUser=new User();
                $fosUser->setEmail($email);
                $fosUser->setEmployee($phy); 
                $fosUser->setEnabled(true);
                $fosUser->setHospital($hosp);
                $fosUser->setIsActive(true);
                $fosUser->setLastnameFirstname($lastname . " " . $firstname);
                $fosUser->setPassword(\Navio\HospitalBundle\Entity\User::randString(32));                            
                $fosUser->setType("Employee");
                $fosUser->setUsername($username);
            } else {
                $fosUser->setUsername($username);
                $fosUser->setEmail($email);
            }
            $rolesArray = explode(',',$roles);
            foreach($rolesArray as $role){
                $fosUser->addRole($role);
            }
            $em->persist($fosUser);
        }


        $uow = $em->getUnitOfWork();//after persist - before flush
        $uow->computeChangeSets();
        $changesetP = $uow->getEntityChangeSet($phy);
        $changesetU = array();
        if ($fosUser) {
            $changesetU = $uow->getEntityChangeSet($fosUser);
        }
        
        if (count($changesetP)+count($changesetU)) {
            $em->flush();
            if (!$skip_update_date) {
                $phy->updatedNow();
            }
            $em->persist($phy);
            $em->flush();
            if(!$bExists){
                if($phy->getHospital()->getNewUsersEmailSubject() && $phy->getHospital()->getNewUsersEmailBody()){
                    syslog(LOG_INFO, 'Hospital Email Title '.$phy->getHospital()->getNewUsersEmailSubject());
                    syslog(LOG_INFO, 'Hospital Email Body '.$phy->getHospital()->getNewUsersEmailBody());
                    $title = $phy->getHospital()->getNewUsersEmailSubject();
                    $emailBody = $phy->getHospital()->getNewUsersEmailBody();
                    $emailService = $this->container->get('navio.email.email_notification');
                    $output->writeln("Sending welcome email {$email} with Subject {$title} \n");
                    $env = new \Twig_Environment(new \Twig_Loader_Array(array($emailBody=>$emailBody)));
                    $output->writeln("Sending welcome email to physician with name: {$phy->getFirstName()} {$phy->getLastName()} and email: {$email}");
                    // Email physician's access code
                    $emailService->sendEmail(
                        $phy->getHospital(),
                        $email,
                        $title,
                        $env->render($emailBody),
                        'text/html'
                    );
                }
            }
            if($bExists){
                $output->writeln(sprintf('Updated <comment>%s</comment>: <comment>%s</comment> <comment>%s</comment>', $username,$firstname,$lastname));
                //var_dump($changeset);
                foreach($changesetP as $key => $value){
                    $val1= $value[0] != null ? $value[0] : 'NULL';
                    $val2= $value[1] != null ? $value[1] : 'NULL';
                    try{
                        $output->writeln(sprintf('Updated <comment>%s</comment> <comment>%s</comment> => <comment>%s</comment>', $key,$val1,$val2));
//                        echo sprintf('Updated <comment>%s</comment> <comment>%s</comment> => <comment>%s</comment>', $key,$val1,$val2);
                    }
                    catch(\Exception $e){                        
                    }
                }
                foreach($changesetU as $key => $value){
                    $val1= $value[0] != null ? $value[0] : 'NULL';
                    $val2= $value[1] != null ? $value[1] : 'NULL';
                    try{
                        $output->writeln(sprintf('Updated <comment>%s</comment> <comment>%s</comment> => <comment>%s</comment>',$key,$val1,$val2));
                    }
                    catch(\Exception $e){                        
                    }
                }
            }
            else {
                $output->writeln(sprintf('Created <comment>%s</comment> <comment>%s</comment> <comment>%s</comment>', $username,$firstname,$lastname));                        
            }
        }
        else {
            $output->writeln(sprintf('No Changes <comment>%s</comment>', $username));            
        }                    
    } 
}
