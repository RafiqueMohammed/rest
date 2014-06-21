<?php

/**
 * Created by Rafique
 * Date: 6/21/14
 * Time: 3:19 PM
 */
class Subscription
{

    private $db, $uid, $error;


    protected $activation, $today, $expiration;

    public function __construct($db, $uid)
    {
        $this->db = $db;
        $this->uid = $uid;
    }

    /**
     * @param mixed $error
     */
    private function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $activation
     */
    public function setActivation($activation)
    {
        $this->activation = $activation;
    }

    /**
     * @return mixed
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * @param mixed $today
     */
    public function setToday($today)
    {
        $this->today = $today;
    }

    /**
     * @return mixed
     */
    public function getToday()
    {
        return $this->today;
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->expiration;
    }


    public function addDefaultSubscription()
    {
        $today = $this->getToday();
        $expiration = $this->getExpiration();
        if (isset($this->expiration, $this->today)) {
            $this->db->query("INSERT INTO `" . TAB_SUBSCRIPTION . "`(`mem_id`,`activation_date`,`expiration_date`) VALUES('{$this->uid}','$today','$expiration')");

            if ($this->db->affected_rows > 0) {
                return true;
            } else {
                $this->setError($this->db->error);
                return false;
            }
        } else {
            $this->setError("Required Date is missing");
            return false;
        }


    }
} 