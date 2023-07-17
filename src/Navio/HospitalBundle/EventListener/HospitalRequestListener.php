<?php
/**
 * User: robertfaulkner
 * Date: 23/02/15
 * Time: 17:02
 */

namespace Navio\HospitalBundle\EventListener;

use Navio\HospitalBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
//use Symfony\Component\HttpKernel\HttpKernel;Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface



class HospitalRequestListener {

    protected $container;
    protected $twig;

    /**
     * @InjectParams({"container" = @Inject("service_container")})
     *
     */
    public function __construct(ContainerInterface $container, \Twig_Environment $twig) // this is @service_container
    {
        $this->container = $container;
        $this->twig = $twig;
    }

    /**
     * @Observe("kernel.request")
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Intercept requests and set timezone and language
        // NOTE: if going to home page, ( as is done while login or impersonation) force setting of language

        $request    =   $event->getRequest();
        $route      =   $request->get('_route');

        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $session    =   $request->getSession();

        //  Check locale, otherwise lookup locale
        $lang = $session->get('_locale');
        if( !$lang  || $route=="home" || $route=="users/home"){
            $hosp = $this->getHosp();
            if (!is_null($hosp)) {
                $lang = $hosp->getLocale();
            }
            if($lang===null){
                $lang = 'en_US';
            }

            $session->set('_locale', $lang);
            $request->setLocale($lang);
//            syslog(LOG_INFO,"L = /".$lang);
        }

        $tz = $session->get('global.default_timezone');
        if (!$tz || $route=="home" || $route=="users/home") {
            $hosp = $this->getHosp();
            if (!is_null($hosp)) {
                $tz = $hosp->getTimeZone();
                //syslog(LOG_INFO,"setTZ = ".$tz);
            }
            if($tz===null) {$tz = 'America/New_York';}

            $session->set('global.default_timezone', $tz);
        }

        date_default_timezone_set($tz);
        $request->getSession()->set('_locale', $lang);
        $request->setLocale($request->getSession()->get('_locale', $lang));
        $this->twig->getExtension('core')->setTimezone($tz);
        $this->isUpdatePasswordNeeded($event);
    }

    private function isUpdatePasswordNeeded(GetResponseEvent $event) {
        $request    =   $event->getRequest();
        if(strpos($event->getRequest()->get('_route'), '_assetic_') === false) //skip any assetic URIs
        {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            if($user instanceof User && $user->isCredentialsExpired())
            {
                $request->getSession()->set('updatePasswordNeeded', true);
            }
            $isUpdatePasswordNeeded = $request->getSession()->get('updatePasswordNeeded');
            if ($isUpdatePasswordNeeded) {
                $expectedRoute = 'physician_pass_set';
                if ($expectedRoute === $event->getRequest()->get('_route')) {
                    return;
                }

                $url = $this->container->get('router')->generate($expectedRoute);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }
        }
    }

    private function getHosp() {
        $hosp = null;
        $context = $this->container->get('security.token_storage');
        $token = $context->getToken();
        if($token){
            $user = $token->getUser();
            if (is_a($user, 'Navio\HospitalBundle\Entity\User')){
                if ($context->getToken() && $context->getToken()->getUser() !== 'anon.') {
                    $hosp = $user->getHospital();
                }
            }
        }
        return $hosp;
    }
}
