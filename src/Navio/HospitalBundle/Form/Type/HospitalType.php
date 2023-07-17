<?php
namespace Navio\HospitalBundle\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Navio\HospitalBundle\Entity\HospitalAgency;
use Navio\HospitalBundle\Entity\Hospital;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class HospitalType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
        $hosp = $options['hosp'];
        $timezoneList = array();
        foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, 'US') as $tz) {
            $timezoneList[$tz] = $tz;
        }

        $em = $options['em'];
        $hospitalRepo = $em->getRepository('HospitalBundle:Hospital');

        $hospitalPhysicianTerms = $hospitalRepo->getHospitalPhysicianTerms($hosp);
        $hospitalPatientTerms   = $hospitalRepo->getHospitalPatientTerms($hosp);

        $hospitalPhysicianTermsText = "";
        $hospitalPatientTermsText   = "";

        if ($hospitalPhysicianTerms) {
            $hospitalPhysicianTermsText = $hospitalPhysicianTerms->getTermsBody();
        }

        if ($hospitalPatientTerms) {
            $hospitalPatientTermsText = $hospitalPatientTerms->getTermsBody();
        }

     

        $builder->add('name', TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('logo', FileType::class,array('data_class' => null))
               ->add('city',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('state',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('itPhone',TextType::class,array('attr'=>array('size'=>'30'),)) 
               ->add('itEmail',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('emrPhone',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('deletionTime',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('passwordTimeout',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('scheduleNotificationEmail',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('accessCodeEmailTitle',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('accessCodeEmailBody', TextareaType::class, array('attr' => array('class' => 'tinymce','data-theme' => 'advanced')))
               ->add('accessRequestText',TextareaType::class,array('attr'=>array('size'=>'30'),'required'  => false))
               ->add('makeEmailSubject',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('makeEmailBody',TextareaType::class,array('attr' => array('class' => 'tinymce','data-theme' => 'advanced')))
               ->add('newUsersEmailSubject',TextType::class,array('attr'=>array('size'=>'30'),))
               ->add('newUsersEmailBody',TextareaType::class,array('attr' => array('class' => 'tinymce','data-theme' => 'advanced')))
               ->add('timezone', ChoiceType::class, array(
							'choices'   => $timezoneList,
							'required'  => true,
					));
               
        $builder->add('hospitalPhysicianTerms', TextareaType::class, array( 'mapped' => false, 'data' => $hospitalPhysicianTermsText, 'attr' => array('class' => 'tinymce','data-theme' => 'advanced')));
        $builder->add('hospitalPatientTerms', TextareaType::class, array( 'mapped' => false, 'data' => $hospitalPatientTermsText, 'attr' => array('class' => 'tinymce','data-theme' => 'advanced')));
        $builder->add('save', SubmitType::class);
   }
   public function configureOptions(OptionsResolver $resolver)
   {
       $resolver->setDefaults(array(
           'data_class'=>'Navio\HospitalBundle\Entity\Hospital',
           'hosp'=>null,
           'em'=>null,
           'code'=>null
       ));
   }
   public function getName()
   {
       return 'firstName';
   }

   static function zoneId($z) {
	$zones =  array_search($z,\DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, 'US'));
   }

   static function zone($i) {
	return \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, 'US')[$i];
   }
}
