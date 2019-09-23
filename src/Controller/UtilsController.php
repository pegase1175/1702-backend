<?php
/**
 * Created by PhpStorm.
 * User: aconti
 * Date: 21/09/2019
 * Version: 1.00
 * ----------------------------------
 * classe permettant l'initialisation des data
 *
 * Historique des modifications
 * --------------------------------
 * <Trigramme> - <Date> - <Version> : <Commentaire>
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Models\Spdo;

class UtilsController extends AbstractController
{
    private $BDD;

    /**
     * @Route("/utils/ImportJson", name="ImportJSON")
     */
    public function ImportInitData()
    {
        // Instanciate BDD connection
        //$this->BDD=new Spdo($_ENV["DATABASE_URL"],$_ENV["USERNAME"], $_ENV["PASSWORD"]);

        // Get JSON file and decode contents into PHP arrays/values
        $jsonFile = 'ship_positions.json';
        $jsonData = json_decode(file_get_contents($jsonFile), true);

        // Iterate through JSON and build INSERT statements
        foreach ($jsonData as $row)
        {
            // Set parameters for the INSERT Instruction
            $parametersINIT=array(
                ':mmsi'         =>$row['mmsi'],
                ':status'       =>$row["status"],
                ':station'      =>$row["stationId"],
                ':speed'        =>$row["speed"],
                ':longitude'    =>$row["lon"],
                ':latitude'     =>$row["lat"],
                ':course'       =>$row["course"],
                ':heading'      =>$row["heading"],
                ':rot'          =>$row["rot"],
                ':date'         =>$row["timestamp"]
            );

            $this->BDD->query("
            INSERT INTO T_VESSEL_TRACK_VTR (VTR_MMSI_ID, VTR_STATUS, STA_ID, VTR_SPEED, VTR_LONG, VTR_LAT, VTR_COURSE, VTR_HEADING, VTR_ROT, VTR_DATE ) 
            VALUES (:mmsi, :status, :station, :speed, :longitude, :latitude, :course, :heading, :rot, FROM_UNIXTIME(:date))
            ", $parametersINIT);

        }

        return $this->json([
            'message' => 'Importation ending.'
        ]);
    }
}
