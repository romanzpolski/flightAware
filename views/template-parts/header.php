<?php

?>

<!DOCTYPE html>
<html>
<head>
    <title>Irish airports flights finder</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" />

</head>
<body>
<div id="header">
    <div class="container">
        <div class="row">
            <div class="col-sm-6"><h3><a href="/">Search Flights In Ireland</a></h3></div>
            <div class="col-sm-6 form-group text-right">
                <br>
                <form action="results" id="airports-form" role="form" class="form-inline">
                    <label>Please select airport &nbsp;</label>
                    <select name="icao" id="airport-select" class="form-control">
                        <option value="EICK">Cork</option>
                        <option value="EIDL">Donegal</option>
                        <option value="EIDW">Dublin</option>
                        <option value="EIWT">Dublin / Leixlip</option>
                        <option value="EICM">Galway</option>
                        <option value="EIKY">Tralee & Killarney</option>
                        <option value="EIKN">Knock</option>
                        <option value="EINN">Shannon</option>
                        <option value="EISG">Sligo</option>
                        <option value="EIWF">Waterford</option>
                    </select>
                    <select name="mode" class="form-control">
                        <option value="arrivals">Arrivals</option>
                        <option value="departures">Departures</option>
                    </select>
                    <button type="submit" class="btn btn-success">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
