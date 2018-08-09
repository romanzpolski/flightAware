<?php



?>

<div id="content" class="container">
    <div class="row">
        <div class="col-sm-6 bg-success" style="min-height: 150px;">
            <h3><?php echo $data['heading']; ?></h3>
            <h6><?php echo $data['subHeading']; ?></h6>
        </div>
        <div class="col-sm-6">
            <div id="map" style="width:100%; min-height: 150px;"></div>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php

                        foreach($data['labels'] as $label){
                            echo '<th>'.$label.'</th>';
                        }

                        ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $counter = 0;
                foreach($data['flights'] as $f){
                    include('list-item.php');
                    $counter++;
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
