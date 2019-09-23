<?php

namespace App\Controller;

use App\Models\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Models\Spdo;

class VesselTrackController extends AbstractController
{
    private $BDD;
    private $parameters;
    private $ipUser;
    private $user;

    public function __construct()
    {
        // Instanciate BDD connection
        $this->BDD=new Spdo($_ENV["DB_HOST"],$_ENV["USERNAME"], $_ENV["PASSWORD"], $_ENV["DB_NAME"]);

        // Set parameters for the ProcStock
        $this->parameters=array(
            'mmsi'     =>0,
            'FromDate' =>'',
            'ToDate'   =>'',
            'MinLat'   =>0,
            'MaxLat'   =>0,
            'MinLong'  =>0,
            'MaxLong'  =>0
        );
    }

    /**
     * @Route("/track", methods={"GET"})
     */
    public function index(Request $request)
    {
       // set User
        $this->user=new User($request->getClientIp());
        if (!$this->user->connect())
            return $this->json([
                'status' => 'Excedeed limit request, you must waiting',
                'status_code' => '412',
            ]);

        // Verification of existence of required parameters
        if (empty($request->get('FromDate').$request->get("DaysPast")) && empty($request->get("mmsi")))
        {
            return $this->json([
                'status' => 'One or few parameter required is forget',
                'status_code' => '412',
            ]);
        }
        else
        {
            if (!$this->isValidDate($request->get('FromDate')) || !$this->isValidDate($request->get('ToDate')))
                return $this->json([
                    'status' => 'date format incorrect : must be Y-m-d H:m:s',
                    'status_code' => '412',
                ]);

            $vessels=explode(",", $request->get("mmsi"));

            // If a number of day is precised
            if (!empty($request->get("DaysPast")))
            {
                $this->parameters['FromDate']=date('Y-m-d H:i:s', strtotime(' - '.abs($request->get("DaysPast")).' days'));
                $this->parameters['ToDate']=date('Y-m-d H:i:s');
            }
            else
            {
                $this->parameters['FromDate']=$request->get('FromDate');
                $this->parameters['ToDate']=$request->get('ToDate');
            }

            foreach($vessels as $vessel)
            {
                if (is_numeric($vessel))
                {
                    // Define vessel ID
                    $this->parameters['mmsi']=$vessel;
                    $result=$this->BDD->execute("PS_EXT_VESSEL_TRACK", $this->parameters);
                }
            }

            // Increment connection user
            $this->user->logAction();
        }

        return $this->json($result);

    }

    private function isValidDate($date) {
        return date('Y-m-d H:i:s', strtotime($date)) === $date;
    }
}
