<?php
/**
* This class is to pull data from FlightAware
 *
**/
class flightsClient
{
    protected $config;

    public function __construct(){
        $this->config = parse_ini_file('config.ini');
    }

    public function getEnroute($icao, $limit = 100, $offset = 0){
        $params = array(
            'airport'=>$icao,
            'howMany'=>$limit,
            'filter'=> 'airline',
            'offset'=> $offset
        );
        $result = $this->get('Enroute', $params)->EnrouteResult;
        return $result;
    }

    public function getMapFlight($id){
        $params = array(
            'ident'=>$id,
            'height'=> 100,
            'width'=> 150
        );
        $result = $this->get('MapFlight', $params)->MapFlightResult;
        return $result;
    }

    public function getAirportInfo($id){
        $params = array(
            'airportCode'=>$id
        );
        $result = $this->get('AirportInfo', $params)->AirportInfoResult;
        return $result;
    }

    public function getMetarEx($id){
        $params = array(
            'airport'=>$id,
            'startTime'=> '',
            'howMany'=>1,
            'offset'=>4
        );
        $result = $this->get('MetarEx', $params)->MetarExResult->metar[0];
        return $result;
    }

    public function getAirlineFlightSchedules($icao, $limit = 100, $offset = 0){

        $now = new DateTime();
        $timeLimit = new DateTime();
        $interval = new DateInterval('PT6H');
        $timeLimit = $timeLimit->add($interval);

        $params = array(
            'origin'=>$icao,
            'howMany'=>$limit,
            'filter'=> 'airline',
            'offset'=> $offset,
            'startDate'=> $now->getTimestamp(),
            'endDate'=> $timeLimit->getTimestamp(),
        );
        $result = $this->get('AirlineFlightSchedules', $params)->AirlineFlightSchedulesResult->data;
        return $result;
    }
    public function getTailOwner($id){
        $params = array(
            'ident'=>$id
        );
        $result = $this->get('TailOwner', $params)->TailOwnerResult;
        return $result;
    }

    public function getTailOwnersMulti($idArr){
        $paramsDefault = array();
        $result = $this->getMulti('TailOwner', $idArr, $paramsDefault);
        return $result;
    }

    public function getMapsMulti($idArr){
        $paramsDefault = array(
            'height'=> 100,
            'width'=> 150
        );
        $result = $this->getMulti('MapFlight', $idArr, $paramsDefault);
        return $result;
    }

    public function getAirportInfosMulti($idArr){
        $paramsDefault = array(

        );
        $result = $this->getMulti('AirportInfo', $idArr, $paramsDefault);
        return $result;
    }

    protected function get($endpoint, $params = []) {

        $curlDefault = array(
            CURLOPT_USERPWD => $this->config['login'] . ":" . $this->config['password'],
            CURLOPT_RETURNTRANSFER => TRUE,
            //CURLOPT_VERBOSE => TRUE, // TRUE to output verbose information. Writes output to STDERR, or the file specified using CURLOPT_STDERR.
            //CURLOPT_STDERR => $verbose = fopen('php://temp', 'rw+'),
        );

        $ch = curl_init($this->config['baseUrl'] . $endpoint . '?' . http_build_query($params));

        curl_setopt_array($ch, $curlDefault);

        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseCode === 401) {
            echo 'auth failed';
        } else {
            if ($responseCode !== 200) {
                //echo "Verbose information:\n<pre>", !rewind($verbose), htmlspecialchars(stream_get_contents($verbose)), "</pre>\n";
            }
        }

        curl_close($ch);

        $result = json_decode($output);

        if (isset($result->error)) {
            if ($result->error === 'no data available') {
                echo 'no data';
            }
        }

        return $result;
    }

    protected function getMulti($endpoint, $idArr, $paramsDefault = []) {

        $res = array();

        $mh = curl_multi_init();

        foreach($idArr as $i => $id)
        {
            $newParams = array(
                'ident'=>$id,
                'airportCode'=>$id,
            );
            $params = array_merge($paramsDefault,$newParams);

            $ch[$i] = curl_init($this->config['baseUrl'] . $endpoint . '?' . http_build_query($params));
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch[$i], CURLOPT_USERPWD, $this->config['login'] . ":" . $this->config['password']);
            curl_multi_add_handle($mh, $ch[$i]);
        }

        do {
            $execReturnValue = curl_multi_exec($mh, $runningHandles);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);

        while ($runningHandles && $execReturnValue == CURLM_OK)
        {

            if (curl_multi_select($mh) != -1)
            {
                usleep(100);
            }

            do {
                $execReturnValue = curl_multi_exec($mh, $runningHandles);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        }

        if ($execReturnValue != CURLM_OK)
        {
            trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
        }

        foreach($idArr as $i => $id)
        {
            $curlError = curl_error($ch[$i]);

            if ($curlError == "")
            {
                $responseContent = curl_multi_getcontent($ch[$i]);
                $res[$i] = json_decode($responseContent);
            }
            else
            {
                echo "Curl error on handle $i: $curlError\n";
            }

            curl_multi_remove_handle($mh, $ch[$i]);
            curl_close($ch[$i]);
        }

        curl_multi_close($mh);

        return $res;

    }
}
