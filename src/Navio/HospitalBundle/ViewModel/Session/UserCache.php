<?php
/**
 * User: robertfaulkner
 * Date: 17/03/15
 * Time: 08:53
 */

namespace Navio\HospitalBundle\ViewModel\Session;


use Symfony\Component\HttpFoundation\Session\Session;

class UserCache {

    const ALL_PATIENTS_FOR_USER = 'global.all_patients_for_user';
    const ALL_STAFF_FOR_USER = 'global.all_staff_for_user';

    const DEFAULT_TIMEOUT = 300; // seconds. 5 mins (60*5)

    private $session;
    private $userId;

    public function __construct($userId,$session)
    {
        $this->session = $session;
        $this->userId = (string)$userId;
    }

    public function getAllPatientsForUser()
    {
        $key = UserCache::ALL_PATIENTS_FOR_USER . $this->userId;
        return $this->getFromSessionIfValid($key);
    }

    public function setAllPatientsForUser($value)
    {
        $key = UserCache::ALL_PATIENTS_FOR_USER . $this->userId;
        $this->setSessionWithTTL($key, $value, UserCache::DEFAULT_TIMEOUT);
    }

    public function getAllStaffForUser()
    {
        $key = UserCache::ALL_STAFF_FOR_USER . $this->userId;
        return $this->getFromSessionIfValid($key);
    }

    public function setAllStaffForUser($value)
    {
        $key = UserCache::ALL_STAFF_FOR_USER . $this->userId;
        $this->setSessionWithTTL($key, $value, UserCache::DEFAULT_TIMEOUT);
    }

    private function getFromSessionIfValid($sessionKey)
    {
        if ($this->session->has($sessionKey . 'TS')) {
            $ts = $this->session->get($sessionKey . 'TS');
            if($ts < time()){
                // Expired
                return null;
            }
            if ($this->session->has($sessionKey)) {
                // Valid cache
                return $this->session->get($sessionKey);
            }
        }
        return null;
    }

    private function setSessionWithTTL($sessionKey, $value, $ttl)
    {
        $this->session->set($sessionKey, $value);
        $this->session->set($sessionKey . 'TS', time() + intval($ttl));
    }
}
