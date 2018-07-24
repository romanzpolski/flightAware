<?php ?>
<br>
<br>
<br>
<div id="footer">
    <div class="container">
        <div class="row">
            <div class="col-sm-4 text-left">
                <?php if($data['pagination'] > 0){ ?>
                    <div class="page-item">
                        <a class="page-link btn btn-default" href="results?<?php echo $Controller->getPageQueryString($_SERVER['QUERY_STRING'], 'previous'); ?>" tabindex="-1" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Previous</span>
                            <span class="sr-only">First</span>
                        </a>
                    </div>
                <?php } ?>
            </div>
            <div class="col-sm-4"></div>
            <div class="col-sm-4 text-right">
                <?php if($data['pagination'] >= 0 && count($data['flights']) == 100){ ?>
                    <div class="page-item">
                        <a class="page-link btn btn-default" href="results?<?php echo $Controller->getPageQueryString($_SERVER['QUERY_STRING'], 'next'); ?>" aria-label="Next">
                            <span aria-hidden="true">Next &raquo;</span>
                            <span class="sr-only">Last</span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<br>
<br>
<br>
<br>
<br>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDygNeolG94Ds0dcZaCekiLyy8AYbHtqmY&callback=initMap"
        async defer></script>
</body>

</html>
