<?php

class Controller {

    public function __construct(){

    }

    public function getArrivals($icao, $pagination = -1){

        $limit = 200;
        $offset = $pagination ? $pagination*200 : 0;

        $r= 0;
        $rf=0;
        $resFiltered = array();

        $flightsClient = new flightsClient();
        $res = $flightsClient->getEnroute($icao, $limit, $offset);
        $airport = $flightsClient->getAirportInfo($icao);
        $metar = $flightsClient->getMetarEx($icao);

        if($res->enroute){
            $r = count($res->enroute);
            $resFiltered = $this->filterByTimeFrame($res->enroute);
            $rf = count($resFiltered);
        }

        if($pagination == 0 && ($r + $rf) < 200){
            $pagination = -1;
        }

        $data = array(
            'heading' => 'Arrivals to '.$airport->name,
            'subHeading'=> $airport->location.' | '.$metar->cloud_friendly.' | '.$metar->temp_air.'C | '.$metar->wind_friendly,
            'airport'=> array(
                'name'=>$airport->location,
                'long'=>$airport->longitude,
                'lat'=>$airport->latitude
            ),
            'flights' => array(),
            'pagination'=> $pagination,
            'labels'=> array('Flight ID','Planned Arrival Time', 'Filed Departed Time', 'Airline'),
        );


        if($resFiltered){
            $flights = array();
            $idents = array();

            foreach($resFiltered as $f){


                $flight = (object)[
                    'id'=> $f->ident,
                    'arr'=> $this->formatDates($f->estimatedarrivaltime),
                    'dep'=> $this->formatDates($f->filed_departuretime),
                    'origin'=> $f->origin,
                    'originName'=> $f->originName,
                    'destinationName'=> $f->destinationName,
                ];
                $flights[]=$flight;
                $idents[]= $f->ident;
            }

            $owners = $flightsClient->getTailOwnersMulti($idents);

            foreach($flights as $i => $f){
                $f->owner = $owners[$i]->TailOwnerResult;
            }

            $data['flights'] = $flights;
        }

        return $data;
    }

    public function getDepartures($icao, $pagination = -1){

        $limit = 200;
        $offset = $pagination ? $pagination*200 : 0;

        $r= 0;
        $rf=0;
        $resFiltered = array();

        $flightsClient = new flightsClient();
        $res = $flightsClient->getAirlineFlightSchedules($icao, $limit, $offset);
        $airport = $flightsClient->getAirportInfo($icao);
        $metar = $flightsClient->getMetarEx($icao);


        if($pagination == 0 && ($r + $rf) < 200){
            $pagination = -1;
        }

        $data = array(
            'heading' => 'Departures from '.$airport->name,
            'subHeading'=> $airport->location.' | '.$metar->cloud_friendly.' | '.$metar->temp_air.'C | '.$metar->wind_friendly,
            'airport'=> array(
                'name'=>$airport->location,
                'long'=>$airport->longitude,
                'lat'=>$airport->latitude
            ),
            'flights' => array(),
            'pagination'=> $pagination,
            'labels'=> array('Flight ID','Planned Arrival Time', 'Planned Departure Time', 'Airline'),
        );


        if($res){
            $flights = array();
            $idents = array();
            $icaos = array();

            foreach($res as $f){


                $flight = (object)[
                    'id'=> $f->ident,
                    'arr'=> $this->formatDates($f->arrivaltime),
                    'dep'=> $this->formatDates($f->departuretime),
                    'origin'=> $f->origin,
                    'originName'=> $airport->name,
                    'destinationName'=> $f->destination,
                ];
                $flights[]=$flight;
                $idents[]= $f->ident;
                $icaos[]= $f->destination;
            }

            $owners = $flightsClient->getTailOwnersMulti($idents);
            $airports = $flightsClient->getAirportInfosMulti($icaos);

//            echo '<pre>';
//            var_dump($airports);
//            echo '</pre>';

            foreach($flights as $i => $f){
                $f->owner = $owners[$i]->TailOwnerResult;
                $f->destination = $airports[$i]->AirportInfoResult->name;
                $f->destinationName = $airports[$i]->AirportInfoResult->location;
            }

            $data['flights'] = $flights;
        }

        return $data;
    }

    private function formatDates($timestamp){
        $d = new DateTime();
        $d->setTimestamp($timestamp);

        return $d->format('D d H:i:s');
    }

    private function filterByTimeFrame($arr){
        $data = array();

        $now = new DateTime();
        $timeLimit = new DateTime();
        $interval = new DateInterval('PT6H');
        $timeLimit = $timeLimit->add($interval);

        foreach($arr as $f){
            $ts = new DateTime();
            $ts = $ts->setTimestamp($f->estimatedarrivaltime);
            if($ts > $now && $ts < $timeLimit) {
                $data[]= $f;
            }
        }
        return $data;
    }

    public function getPageQueryString($string, $mode = 'next'){
        $query = array();
        parse_str($string, $query);
        if($mode == 'next'){
            $query['pagination'] = $query['pagination'] + 1 ;
        } else {
            $query['pagination'] = $query['pagination'] - 1;
        }
        return http_build_query($query);
    }
}
