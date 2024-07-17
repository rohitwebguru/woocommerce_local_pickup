<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="kvell-extend-warrenty" style="width:100%">
    <input type="checkbox" id="warrenty-check" /> <label>Extend Warrenty</label>
    <div class="warranties" style="display:none">
        <?php
            $warenties=get_post_meta(get_the_ID(),'warranties',true);
            if($warenties):
        ?>
        <?php foreach($warenties['extended_warrenty'] as $key=>$value): ?>
        <?php
        if(!isset($warenties['extended_warrenty']) || empty($warenties['extended_warrenty'][$key])) $warenties['extended_warrenty'][$key]=NULL;
        if(!isset($warenties['extended_start_price']) || empty($warenties['extended_start_price'][$key])) $warenties['extended_start_price'][$key]=NULL;
        if(!isset($warenties['extended_end_price']) || empty($warenties['extended_end_price'][$key])) $warenties['extended_end_price'][$key]=NULL;  
        if(!isset($warenties['extended_warrenty_price']) || empty($warenties['extended_warrenty_price'][$key])) $warenties['extended_warrenty_price'][$key]=NULL;        
        ?>
        <div>
            <input type="radio" placeholder="Enter warrenty name" value="<?php echo $key; ?>" name="extended_warrenty" />
            <?php echo $value; ?>
            <?php //echo "(".$warenties['extended_start_price'][$key].'-'.$warenties['extended_end_price'][$key].")"; ?>
            <?php echo "Price : ". $warenties['extended_warrenty_price'][$key]; ?>
        </div>      
        <?php endforeach; ?> 
        <?php 
            else:
            echo "No Warenties Applicable";
            
            endif;
        ?> 
    </div>   
</div>