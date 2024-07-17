<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="extend_warrenty_data" class="panel extend_options_panel wc-metaboxes-wrapper hidden"> 

<?php 

$warenties=get_post_meta(get_the_ID(),'warranties',true);;

//$warenties=$post_data;
if(!$warenties){
    $warenties=array();
    $warenties['extended_warrenty']=array(0 => NULL);
    $warenties['extended_start_price']=array(0 => NULL);
    $warenties['extended_end_price']=array(0 => NULL); 
    $warenties['extended_warrenty_price']=array(0 => NULL); 
}

?>
<div class="toolbar toolbar-top">
    <button type="button" class="button add_warrenty">Add Warrenty</button>
</div>
<table id="extended_warrenty_options">
    <thead>
    <tr>
        <th>Warrenty</th>
        <th>Start Price</th>
        <th>End Price</th>
        <th>Warrenty Price</th>
    </tr> 
</thead>   
<tbody>
    <?php foreach($warenties['extended_warrenty'] as $key=>$value): ?>
    <?php
        if(!isset($warenties['extended_warrenty']) || empty($warenties['extended_warrenty'][$key])) $warenties['extended_warrenty'][$key]=NULL;
        if(!isset($warenties['extended_start_price']) || empty($warenties['extended_start_price'][$key])) $warenties['extended_start_price'][$key]=NULL;
        if(!isset($warenties['extended_end_price']) || empty($warenties['extended_end_price'][$key])) $warenties['extended_end_price'][$key]=NULL;  
        if(!isset($warenties['extended_warrenty_price']) || empty($warenties['extended_warrenty_price'][$key])) $warenties['extended_warrenty_price'][$key]=NULL;
        
    ?>
    <tr>
        <td><input type="text" placeholder="Enter warrenty name" value="<?php echo $value ?>" name="extended_warrenty[<?php echo $key; ?>]" /></td>
        <td><input type="number" placeholder="Enter start price" value="<?php echo $warenties['extended_start_price'][$key]; ?>" name="extended_start_price[<?php echo $key; ?>]" /> <?php echo get_woocommerce_currency_symbol(); ?></td>
        <td><input type="number" placeholder="Enter end price" value="<?php echo $warenties['extended_end_price'][$key]; ?>" name="extended_end_price[<?php echo $key; ?>]" /> <?php echo get_woocommerce_currency_symbol(); ?></td>
        <td><input type="number" placeholder="Enter warrenty Price" value="<?php echo $warenties['extended_warrenty_price'][$key]; ?>" name="extended_warrenty_price[<?php echo $key; ?>]" /> <?php echo get_woocommerce_currency_symbol(); ?><a href="javascript:void(0)" class="remove-warrenty">X</a></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<div class="toolbar">    
    <button type="button" class="button save_extended_warrenty button-primary">Save Warrenties</button>
</div>
</div>
