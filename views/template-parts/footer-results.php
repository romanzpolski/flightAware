<?php ?>
<br>
<br>
<br>
<div id="footer">


    <br>
    <br>
    <br>
    <br>
    <br>

</div>

<script>
    var map;
    function initMap() {
        var LatLng = {lat: <?php echo $data['airport']['lat']; ?>, lng: <?php echo $data['airport']['long']; ?>};
        map = new google.maps.Map(document.getElementById('map'), {
            center: LatLng,
            zoom: 8,
            //disableDefaultUI: true,
            //zoomControl: false,
            //scaleControl: true
        });

        var marker = new google.maps.Marker({
            position: LatLng,
            map: map,
            title: '<?php echo $data['airport']['name']; ?>'
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDygNeolG94Ds0dcZaCekiLyy8AYbHtqmY&callback=initMap"
        async defer></script>
</body>

</html>
