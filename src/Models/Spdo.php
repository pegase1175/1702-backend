<?php
namespace App\Models;
/**
 * Created by PhpStorm.
 * User: aconti
 * Date: 21/09/2019
 * Version: 1.00
 * ----------------------------------
 * classe métier d'accès aux données
 *
 * Historique des modifications
 * --------------------------------
 * <Trigramme> - <Date> - <Version> : <Commentaire>
 */

Use mysqli;

class Spdo
{
    private $spdo = null;
    private $lastErrorMsg="";

    public function __construct($host, $usr, $pwd, $database)
    {
        $this->spdo = new mysqli($host, $usr, $pwd, $database);
    }

    public function __destruct()
    {
        $this->spdo = null;
    }

    public function ErrorMessage()
    {
        return $this->lastErrorMsg;
    }

    public function execute ($storedName, $parameters)
    {
        // Récupération de la liste des paramètres de la procédure stockée
        $lv_call   = "CALL $storedName(";

        foreach($parameters as $lv_key=>$lv_value)
        {
            mysqli_query($this->spdo ,"SET @_$lv_key = '$lv_value'");
            $lv_call   .= " @_$lv_key,";
        }
        $lv_call   = substr($lv_call, 0, -1).")";
        $record=array();

        mysqli_multi_query ($this->spdo, $lv_call) OR DIE (mysqli_error($this->spdo));
        while (mysqli_more_results($this->spdo)) {

            if ($result = mysqli_store_result($this->spdo))
            {
                while ($row = mysqli_fetch_assoc($result))
                    $record[]=$row;

                mysqli_free_result($result);
            }
            mysqli_next_result($this->spdo);

        }

        return $record;

    }

    public function rotateQuery($fieldKey, $resultQuery)
    {
        $result=null;

        // On vérifie la présence de ligne
        if (isset($resultQuery))
        {
            foreach ($resultQuery as $row)
            {
                $result[$row[$fieldKey]]=$row;
            }
        }

        return $result;
    }
}