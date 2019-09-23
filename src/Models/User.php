<?php
/**
 * Created by PhpStorm.
 * User: Shadow
 * Date: 22/09/2019
 * Time: 16:08
 */

namespace App\Models;


class User
{
    private $id;
    private $remoteIP;
    private $BDD;

    public function __construct($IP)
    {
        $this->remoteIP=$IP;

        // Instanciate BDD connection
        $this->BDD=new Spdo($_ENV["DB_HOST"],$_ENV["USERNAME"], $_ENV["PASSWORD"], $_ENV["DB_NAME"]);

    }

    public function connect()
    {
        $parameters=array(
            'ipUser'    =>$this->remoteIP
        );

        $result=$this->BDD->execute("PS_USER_RIGHT", $parameters);
        $this->id=$result[0]["USR_ID"];

        if ($result[0]["AUTH"]=="0")
            return false;
        else
            return true;
    }

    public function logAction()
    {
        $parameters=array(
            'IDUser'    =>$this->id
        );

        $this->BDD->execute("PS_USER_LOG_ACTION", $parameters);
    }
}