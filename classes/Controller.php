<?php

class Controller {

    public function __construct(){

    }

    public function getArrivals($icao){

        $limit = 100;
        $offset = 0;

        $flightsClient = new flightsClient();
        $res = $flightsClient->getEnroute($icao, $limit, $offset);
        $airport = $flightsClient->getAirportInfo($icao);
        $metar = $flightsClient->getMetarEx($icao);

        $allFlights = array();



        if($res->enroute){
            $resFiltered = $this->filterByTimeFrame($res->enroute);
            $resWithAirline = $this->prepareArrivalFlights($resFiltered);
            $allFlights = array_merge($allFlights, $resWithAirline);
        }

        $c=0;
        while( $res->next_offset > 0 && $c < 15 ) {
            $c++;
            $offset = $offset + 100;
            $res = $flightsClient->getEnroute($icao, $limit, $offset);
            $resFilteredToAdd = $this->filterByTimeFrame($res->enroute);
            $resWithAirlineToAdd = $this->prepareArrivalFlights($resFilteredToAdd);
            $allFlights = array_merge($allFlights, $resWithAirlineToAdd);
        }


        $data = array(
            'heading' => 'Arrivals to '.$airport->name,
            'subHeading'=> $airport->location.' | '.$metar->cloud_friendly.' | '.$metar->temp_air.'C | '.$metar->wind_friendly,
            'airport'=> array(
                'name'=>$airport->location,
                'long'=>$airport->longitude,
                'lat'=>$airport->latitude
            ),
            'flights' => $allFlights,
            'labels'=> array('Flight ID','Planned Arrival Time', 'Filed Departed Time', 'Airline'),
        );

        return $data;
    }

    private function prepareArrivalFlights($enroute){
            $flights = array();
            $idents = array();

            foreach($enroute as $f){

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

            $flightsClient = new flightsClient();

            $owners = $flightsClient->getTailOwnersMulti($idents);

            foreach($flights as $i => $f){
                $f->owner = $owners[$i]->TailOwnerResult;
            }

            return $flights;
    }

    public function getDepartures($icao){

        $limit = 100;
        $offset = 0;
        $allFlights = array();

        $flightsClient = new flightsClient();

        // get departure flights from API
        $res = $flightsClient->getAirlineFlightSchedules($icao, $limit, $offset);

        $airport = $flightsClient->getAirportInfo($icao);
        $metar = $flightsClient->getMetarEx($icao);
        $resWithFlights = $this->prepareDepartureFlights($res->data, $airport);

        // add flights to all flights
        $allFlights = array_merge($allFlights, $resWithFlights);


        // API return is max 100 items, so we need to paginate, limit 15
        $c=0;
        while( $res->next_offset > 0 && $c < 15 ) {
            $c++;
            $resFilteredToAdd = $this->filterByTimeFrame($res->data);
            $resWithAirlineToAdd = $this->prepareDepartureFlights($resFilteredToAdd, $airport);
            $allFlights = array_merge($allFlights, $resWithAirlineToAdd);
            $res = $flightsClient->getAirlineFlightSchedules($icao, $limit, $offset);
            $offset = $offset + 100;
        }

        // form data array
        $data = array(
            'heading' => 'Departures from '.$airport->name,
            'subHeading'=> $airport->location.' | '.$metar->cloud_friendly.' | '.$metar->temp_air.'C | '.$metar->wind_friendly,
            'airport'=> array(
                'name'=>$airport->location,
                'long'=>$airport->longitude,
                'lat'=>$airport->latitude
            ),
            'flights' => $allFlights,
            'labels'=> array('Flight ID','Planned Arrival Time', 'Planned Departure Time', 'Airline'),
        );


        return $data;
    }

    private function prepareDepartureFlights($res, $airport){
            $flights = array();
            $idents = array();
            $icaos = array();
            $flightsClient = new flightsClient();

            // put data in object
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

            // call API for more details
            $owners = $flightsClient->getTailOwnersMulti($idents);
            $airports = $flightsClient->getAirportInfosMulti($icaos);

            // add more data to $flights array
            foreach($flights as $i => $f){
                $f->owner = $owners[$i]->TailOwnerResult;
                $f->destination = $airports[$i]->AirportInfoResult->name;
                $f->destinationName = $airports[$i]->AirportInfoResult->location;
            }

            // sort $flights array by departure time ASC
            usort($flights, function($a, $b)
            {
                $date1 = $a->dep;
                $date2 = $b->dep;
                if ($date1 < $date2) return -1;
                if ($date1 == $date2) return 0;
                if ($date1 > $date2) return 1;
            });

            return $flights;
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
            if(property_exists($f, 'estimatedarrivaltime')){
                // in case of arrivals
                $flightTS = $f->estimatedarrivaltime;
            } else {
                // departures
                $flightTS = $f->departuretime;
            }
            $ts = $ts->setTimestamp($flightTS);
            if($ts > $now && $ts < $timeLimit) {
                // if records within timeframe
                $data[]= $f;
            }
        }
        return $data;
    }

}
