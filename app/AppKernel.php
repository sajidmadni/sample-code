<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function __construct($environment, $debug)
    {
            date_default_timezone_set( 'America/New_York' );
            parent::__construct($environment, $debug);
    }
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\WebServerBundle\WebServerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
//            new Navio\UserBundle\UserBundle(),
            new FOS\UserBundle\FOSUserBundle(),
	       //new FOS\MessageBundle\FOSMessageBundle(),
            new Navio\MessageBundle\MessageBundle(),
            new Navio\NotificationBundle\NotificationBundle(),
            //new Navio\HospitalBundle\SurveyBundle(),
            new Navio\HospitalBundle\HospitalBundle(),
            new Navio\SAMLBundle\SAMLBundle(),
            new Navio\SyncBundle\SyncBundle(),
            new Navio\PublicBundle\PublicBundle(),
            new Navio\RMSPushNotificationsBundle\RMSPushNotificationsBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle,
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new Navio\FrameworkBundle\NavioFrameworkBundle(),
            new Navio\RegistryBundle\NavioRegistryBundle(),
            new Navio\ConsultBundle\ConsultBundle(),
            new Tetranz\Select2EntityBundle\TetranzSelect2EntityBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),
            new LightSaml\SymfonyBridgeBundle\LightSamlSymfonyBridgeBundle(),
            new LightSaml\SpBundle\LightSamlSpBundle(),
            new Vresh\TwilioBundle\VreshTwilioBundle(),
            new \KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle(),

            /**
             * NEW BUNDLES FOR CODE RE-FACTORING & CLEANLINESS
             *
             * These will be re-factored over the course of development, for new
             * modules please create a new bundle!
             *
             * THIS IS A WORK IN PROGRESS
             */

            //  PRACTICE UNITE - CORE
            new PracticeUnite\CoreBundle\PracticeUniteCoreBundle(),
//            new PracticeUnite\CoreUIBundle\PracticeUniteCoreUIBundle(),

            //  PRACTICE UNITE - MODULES
//            new PracticeUnite\OnCallBundle\PracticeUniteOnCallBundle(),
            new Navio\WorkflowBundle\WorkflowBundle(),
            new Navio\ServiceRecoveryBundle\ServiceRecoveryBundle(),
            new Navio\OAuthBundle\NavioOAuthBundle(),
            new Navio\ReportingBundle\ReportingBundle(),
            new Navio\PublicBundle\NavioPublicBundle(),
            new IMAG\LdapBundle\IMAGLdapBundle(),
            new Navio\EngagementBundle\NavioEngagementBundle(),
            new PracticeUnite\ReferenceBundle\PracticeUniteReferenceBundle(),
            new Navio\AdminBundle\NavioAdminBundle(),
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    public function getVarDir() {
        return $this->getRootDir() . ".var";
    }

    public function getCacheDir() {
        return $this->getVarDir() . '/' . $this->environment . '/cache';
    }

    public function getUploadDir() {
        return $this->getVarDir() . '/uploads/';
    }

    public function getImageDir() {
        return $this->getVarDir() . '/images/';
    }

    public function getLogDir() {
        return $this->getVarDir() . '/' . $this->environment . '/logs';
    }

}
