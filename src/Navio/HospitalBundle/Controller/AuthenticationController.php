<?php

namespace Navio\HospitalBundle\Controller;

use Detection\MobileDetect;
use Navio\HospitalBundle\Entity\HospitalSetting;
use Navio\SAMLBundle\Security\Core\SAML;
use Navio\Utils\AccessCodeRedirectTrait;
use Navio\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Navio\HospitalBundle\Service\PreAuthService;

/**
 * User: Nanda Yemparala
 * Date: 2019-05-02
 * Time: 14:51
 */

class AuthenticationController extends NHController
{
    use AccessCodeRedirectTrait;

    /**
     * @Route("/hosp-login/{hid}", name="hospital_login")
     * @Template("HospitalBundle:Hospital:webLogin.html.twig")
     */
    public function webAuthAction(Request $request, $hid)
    {
        $hospRepo = $this->get('doctrine')->getRepository('HospitalBundle:Hospital');

        $hospital = $hospRepo->find($hid);
        if($hospital == null)
        {
            return $this->flashBack('error', 'Not configured for this organization', 'fos_user_security_login');
        }
        $loginTypes = $hospital->getSetting(HospitalSetting::$SETTING_HOSPITAL_LOGIN_TYPES);
        if(Utils::IsNullOrEmptyString($loginTypes))
        {
            return $this->flashBack('error', 'Not configured for this organization', 'fos_user_security_login');
        }
        $loginTypes = json_decode($loginTypes);
        // get the app id from headers to determine the redirect scheme
        if ($request->headers->has(SAML::SAML_APP_AUTH_REDIRECT_SCHEME_HEADER))
        {
            $this->get('session')->set(SAML::SAML_APP_REDIRECT_SCHEME_SESSION,
                $request->headers->get(SAML::SAML_APP_AUTH_REDIRECT_SCHEME_HEADER));
        }
        $mobileDetect = new MobileDetect();
        $deviceType = $mobileDetect->isMobile() ? 'mobile':'desktop';
        $loginTypes = array_filter($loginTypes, function ($item) use (&$deviceType) {
            return property_exists($item, 'display') ? $item->display == $deviceType : true;
        });

        return [
            'hospital' => $hospital,
            'authTypes' => $loginTypes
        ];
    }

    /**
     * @Route("/validate_access_code", name="validate_access_code")
     */
    public function accessCodeAuth(Request $request)
    {
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile() == false)
        {
            return $this->flashBack('error', "Access code login can only be used from mobile application");
        }

        $hid = $request->request->get('hid');
        $accessCode = trim($request->request->get('access_code'));

        if($hid == null || $accessCode == null)
        {
            syslog(LOG_ERR, __CLASS__." Invalid arguments received hid: $hid ac: $accessCode");
            return $this->flashBack('error', 'Unable to login. Please contact support if issue persists');
        }
        $phyRepo = $this->get('doctrine')->getRepository('HospitalBundle:Physician');
        $hospital = $this->get('doctrine')->getRepository('HospitalBundle:Hospital')->find($hid);
        $physician = $phyRepo->isAccessCodeValid($accessCode, $hospital);
        if($physician == null)
        {
            return $this->flashBack('error', 'Invalid access code');
        }

        return $this->redirect($this->getRedirectUriWithAccessCode($this->container, $physician->getAccessCode()));
    }


    protected function generateToken()
    {
        $token = $this->get('security.csrf.token_manager')
            ->getToken('authenticate');
        return $token;
    }

    /**
     * @Route("/if-v2/hospital/pre_auth_settings", name="pre_auth_api_hospital_settings", defaults={"_format" : "json"})
     * @Method("POST")
     */
    public function preAuthHospitalSettings(Request $request){
        $currentUser = $this->computePhysician($request, false);
        if($currentUser instanceof Response){
            return $currentUser;
        }

        $jsonResponse['response'] = null;
        // Get the hospital id from headers
        $hid = $request->headers->get('X-HID');

        // Check hospital id is set?
        if(!$hid){
            $jsonResponse['response'] = "Invalid request";
            $response = new Response(json_encode($jsonResponse), 200, ['Content-Type' => 'application/json']);
            return $response;
        }
        // Call preAuth service for further operation
        $preAuthSettingsService = $this->get('navio_pre_auth_settings_service');
        $data = $preAuthSettingsService->preAuthProcess($request, $hid);

        // Check data has set
        if($data){
            $jsonResponse['response'] = $data;
        }
        $response = new Response(json_encode($jsonResponse), 200, ['Content-Type' => 'application/json']);
        return $response;
    }
}