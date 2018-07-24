<?php
//echo '<pre>';
//var_dump($f);
//echo '</pre>';

?>

<tr>
    <td><?php echo $f->id; ?></td>
    <td><?php echo $f->arr."<br>".$f->destinationName ?></td>
    <td><?php echo $f->dep."<br>".$f->originName; ?></td>
    <td>
        <?php if($f->owner->website){
            echo '<a target="blank" href="'.$f->owner->website.'">'.$f->owner->owner.'</a>';
        } else if($f->owner) {
            echo $f->owner->owner;
        } else {
            echo 'No owner';
        } ?>
    </td>
</tr>
