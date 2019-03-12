<?php 
include("access.php");
if(isset($_POST['block'])){
	include("../includes/db.conn.php");
	include("../includes/conf.class.php");
	$bookingId       = time();
	$sql = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO bsi_bookings (booking_id,booking_time, pickup_datetime, dropoff_datetime, client_id, is_block, payment_success, block_name,pick_loc,drop_loc) values(".$bookingId.", NOW(),'". $_SESSION['sv_mcheckindate']."', '".$_SESSION['sv_mcheckoutdate']."', '0', 1, 1, '".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['block_name'])."','". $bsiCore->getlocname($_SESSION['sv_pickup'])."','". $bsiCore->getlocname($_SESSION['sv_dropoff'])."')");	
	mysqli_query($GLOBALS["___mysqli_ston"], "insert into bsi_res_data values(".$bookingId.", ".$bsiCore->ClearInput($_POST['choose']).")");
	header("location:car-blocking.php");
	exit;
}
include("header.php"); 
include("../includes/conf.class.php");
include("../includes/admin.class.php");
if(isset($_POST['submit'])){
	include ('../includes/search.class.php');
	$bsisearch = new bsiSearch();
}
?>
<link rel="stylesheet" type="text/css" href="../css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.validate.css" />
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
 <script type="text/javascript" src="../js/dtpicker/jquery.ui.datepicker-<?=$langauge_selcted?>.js"></script>
<script type="text/javascript">
$(document).ready(function(){
 $.datepicker.setDefaults({ dateFormat: '<?php echo $bsiCore->config['conf_dateformat'];?>' });
    $("#txtFromDate").datepicker({
        minDate: 0,
        maxDate: "+365D",
        numberOfMonths: 2,
        onSelect: function(selected) {
    	var date = $(this).datepicker('getDate');
         if(date){
            date.setDate(date.getDate());
          }
          $("#txtToDate").datepicker("option","minDate", date)
        }
    });
 
    $("#txtToDate").datepicker({ 
        minDate: 0,
        maxDate:"+365D",
        numberOfMonths: 2,
        onSelect: function(selected) {
           $("#txtFromDate").datepicker("option","maxDate", selected)
        }
    });  
 $("#datepickerImage").click(function() { 
    $("#txtFromDate").datepicker("show");
  });
 $("#datepickerImage1").click(function() { 
    $("#txtToDate").datepicker("show");
  });
});
</script>
<div id="container-inside">
<span style="font-size:16px; font-weight:bold"><?php echo CAR_BLOCKING_SEARCH;?></span>
<hr />
  <table cellpadding="4" width="100%">
    <tr>
      <td width="35%" valign="top"><form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" id="form1">
           <table cellpadding="0"  cellspacing="7" border="0"  align="left" style="text-align:left;">
     <td><strong><?php echo BLOCK_CAR_TYPE;?>:</strong></td>   
     <td><?php echo $bsiCore->getCartypeCombobox();?></td> 
    </tr>
    <tr><td><strong><?php echo PICK_UP_LOCATION_TEXT;?>:</strong></td>
     <td><select name="pickuploc" id="pickuploc"><option value="0"  selected="selected">-- Select Pick-up Location --</option><?php echo $bsiCore->getDroppickLocation(); ?></select></td>
    </tr>
    <tr><td><strong><?php echo DROP_OFF_LOCATION_TEXT;?>:</strong></td>
     <td><select name="dropoffloc" id="dropoffloc"><option value="0"  selected="selected">-- Select Drop-off Location --</option><?php echo $bsiCore->getDroppickLocation(); ?></td> 
    </tr>  
    <tr> 
     <td><strong><?php echo BLOCK_CAR_PICK_UP_DATE;?>:</strong></td> 
     <td><input id="txtFromDate" name="pickup" style="width:68px" type="text" readonly="readonly" />
      <span style="padding-left:0px;"><a id="datepickerImage" href="javascript:;"><img src="../images/month.png" height="16px" width="16px" style=" margin-bottom:-4px;" border="0" /></a></span> <select name="pickUpTime"  style="width:90px;">  
 <option value="00:00:00">12:00 AM</option> 
 <option value="00:30:00">12:30 AM</option>
 <option value="01:00:00">1:00 AM</option>
 <option value="01:30:00">1:30 AM</option>
 <option value="02:00:00">2:00 AM</option>
 <option value="02:30:00">2:30 AM</option>
 <option value="03:00:00">3:00 AM</option>
 <option value="03:30:00">3:30 AM</option>
 <option value="04:00:00">4:00 AM</option>
 <option value="04:30:00">4:30 AM</option>
 <option value="05:00:00">5:00 AM</option>
 <option value="05:30:00">5:30 AM</option>
 <option value="06:00:00">6:00 AM</option>
 <option value="06:30:00">6:30 AM</option>
 <option value="07:00:00">7:00 AM</option>
 <option value="07:30:00">7:30 AM</option>
 <option value="08:00:00">8:00 AM</option>
 <option value="08:30:00">8:30 AM</option>
 <option value="09:00:00" selected="selected">9:00 AM</option>
 <option value="09:30:00">9:30 AM</option>
 <option value="10:00:00">10:00 AM</option>
 <option value="10:30:00">10:30 AM</option>
 <option value="11:00:00">11:00 AM</option>
 <option value="11:30:00">11:30 AM</option>
 <option value="12:00:00">12:00 PM</option>
 <option value="12:30:00">12:30 PM</option>
 <option value="13:00:00">1:00 PM</option>
 <option value="13:30:00">1:30 PM</option>
 <option value="14:00:00">2:00 PM</option>
 <option value="14:30:00">2:30 PM</option>
 <option value="15:00:00">3:00 PM</option>
 <option value="15:30:00">3:30 PM</option>
 <option value="16:00:00">4:00 PM</option>
 <option value="16:30:00">4:30 PM</option>
 <option value="17:00:00">5:00 PM</option>
 <option value="17:30:00">5:30 PM</option>
 <option value="18:00:00">6:00 PM</option>
 <option value="18:30:00">6:30 PM</option>
 <option value="19:00:00">7:00 PM</option>
 <option value="19:30:00">7:30 PM</option>
 <option value="20:00:00">8:00 PM</option>
 <option value="20:30:00">8:30 PM</option>
 <option value="21:00:00">9:00 PM</option>
 <option value="21:30:00">9:30 PM</option>
 <option value="22:00:00">10:00 PM</option>
 <option value="22:30:00">10:30 PM</option>
 <option value="23:00:00">11:00 PM</option>
 <option value="23:30:00">11:30 PM</option>
</select></td>
    </tr>
    <tr>
     <td><strong><?php echo BLOCK_CAR_DROP_OFF_DATE;?>:</strong></td>
     <td><input id="txtToDate" name="dropoff" style="width:68px" type="text" readonly="readonly"/>
      <span style="padding-left:0px;"><a id="datepickerImage1" href="javascript:;"><img src="../images/month.png" height="18px" width="18px" style=" margin-bottom:-4px;" border="0" /></a></span>  <select name="dropoffTime"  style="width:90px;">
 <option value="00:00:00">12:00 AM</option>
 <option value="00:30:00">12:30 AM</option>
 <option value="01:00:00">1:00 AM</option>
 <option value="01:30:00">1:30 AM</option>
 <option value="02:00:00">2:00 AM</option>
 <option value="02:30:00">2:30 AM</option>
 <option value="03:00:00">3:00 AM</option>
 <option value="03:30:00">3:30 AM</option>
 <option value="04:00:00">4:00 AM</option>
 <option value="04:30:00">4:30 AM</option>
 <option value="05:00:00">5:00 AM</option>
 <option value="05:30:00">5:30 AM</option>
 <option value="06:00:00">6:00 AM</option>
 <option value="06:30:00">6:30 AM</option>
 <option value="07:00:00">7:00 AM</option>
 <option value="07:30:00">7:30 AM</option>
 <option value="08:00:00">8:00 AM</option>
 <option value="08:30:00">8:30 AM</option>
 <option value="09:00:00" selected="selected">9:00 AM</option>
 <option value="09:30:00">9:30 AM</option>
 <option value="10:00:00">10:00 AM</option>
 <option value="10:30:00">10:30 AM</option>
 <option value="11:00:00">11:00 AM</option>
 <option value="11:30:00">11:30 AM</option>
 <option value="12:00:00">12:00 PM</option>
 <option value="12:30:00">12:30 PM</option>
 <option value="13:00:00">1:00 PM</option>
 <option value="13:30:00">1:30 PM</option>
 <option value="14:00:00">2:00 PM</option>
 <option value="14:30:00">2:30 PM</option>
 <option value="15:00:00">3:00 PM</option>
 <option value="15:30:00">3:30 PM</option>
 <option value="16:00:00">4:00 PM</option>
 <option value="16:30:00">4:30 PM</option>
 <option value="17:00:00">5:00 PM</option>
 <option value="17:30:00">5:30 PM</option>
 <option value="18:00:00">6:00 PM</option>
 <option value="18:30:00">6:30 PM</option>
 <option value="19:00:00">7:00 PM</option>
 <option value="19:30:00">7:30 PM</option>
 <option value="20:00:00">8:00 PM</option>
 <option value="20:30:00">8:30 PM</option>
 <option value="21:00:00">9:00 PM</option>
 <option value="21:30:00">9:30 PM</option>
 <option value="22:00:00">10:00 PM</option>
 <option value="22:30:00">10:30 PM</option>
 <option value="23:00:00">11:00 PM</option>
 <option value="23:30:00">11:30 PM</option>
</select></td>
    </tr>
    <tr>
              <td></td>
              <td><input type="submit" value="<?php echo BLOCK_CAR_SEARCH;?>" name="submit" style="background:#e5f9bb; cursor:pointer; cursor:hand;"/></td>
            </tr>
  </table>
        </form></td>
      <td valign="top"><?php if(isset($_POST['submit'])){  ?>
        <form name="adminsearchresult" id="adminsearchresult" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
          <table cellpadding="4" cellspacing="2" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; border:#999 solid 1px;" width="450px">
          <input type="hidden" name="pickup" value="<?php echo $_POST['pickup'];?>"/>
          <input type="hidden" name="dropoff" value="<?php echo $_POST['dropoff'];?>"/>
          <input type="hidden" name="pickUpTime" value="<?php echo $_POST['pickUpTime'];?>"/>
          <input type="hidden" name="dropoffTime" value="<?php echo $_POST['dropoffTime'];?>"/>
          <input type="hidden" name="car_type" value="<?php echo $_POST['car_type'];?>"/>
            <tr>
              <th align="left" colspan="2"><b>
                <?php echo BLOCK_CAR_SEARCH_RESULT;?>
                (
                <?php echo $_POST['pickup'];?> <?php echo date('g:i A',strtotime($bsiCore->getMySqlDate($_POST['pickup'])." ".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['pickUpTime'])));?>
                <?php echo BLOCK_CAR_TO;?>
                <?php echo $_POST['dropoff'];?> <?php echo date('g:i A',strtotime($bsiCore->getMySqlDate($_POST['dropoff'])." ".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['dropoffTime'])));?>
                ) =
                <?=
				ceil((((strtotime($bsiCore->getMySqlDate($_POST['dropoff'])." ".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['dropoffTime'])))-(strtotime($bsiCore->getMySqlDate($_POST['pickup'])." ".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['pickUpTime']))))/60)/1440);
				// echo ceil($mins/1440);
				?>
                
                <?php echo BLOCK_CAR_DAYS;?></b></th>
            </tr>
            <tr>
              <td align="left" ><?php echo BLOCK_CAR_NAME_AND_DESCRIPTION;?></td>
              <td><input type="text" name="block_name" id="block_name"  style="width:230px !important;"/></td>
            </tr>
            <tr><td colspan="2"><hr /></td></tr>
            <tr>
              <th align="left"><?php echo BLOCK_CAR_NAME;?></th>
              <th align="left"><?php echo BLOCK_CAR_AVAILABLITY;?></th>
            </tr>
             <tr><td colspan="2"><hr /></td></tr>
            <?php
	 	$gotSearchResult = false;		
				$apartment_result = $bsisearch->getAvailableCar();
				if(intval($apartment_result['roomcnt']) > 0) {
					$gotSearchResult = true;
					foreach($_SESSION['svars_details'] as $arr1){
						/*echo "<pre>";
						print_r($arr1);
						echo "<pre>";die;*/
						foreach($arr1 as $key=>$arr2){
							$carstring='';
						if(is_array($arr1[$key])){
							$cartype=mysqli_fetch_assoc(mysqli_query($GLOBALS["___mysqli_ston"], "select type_title from bsi_car_type where id=".$arr1[$key]['car_type_id']));
							$carvendor=mysqli_fetch_assoc(mysqli_query($GLOBALS["___mysqli_ston"], "select vendor_title from bsi_car_vendor where id=".$arr1[$key]['car_vendor_id']));
							$carstring=$cartype['type_title'].' '.$carvendor['vendor_title'].' '.$arr1[$key]['car_model'];
								
				 ?>
                    <tr>
                      <td><?php echo $carstring;?></td>
                      <td><input type="radio" value="<?php echo $arr1[$key]['car_id'];?>" name="choose"/></td>
                    </tr>
					<?php 
					         }
                           }
                                
                        }
                    
                      }  
			if($gotSearchResult){
				echo '<tr>
				  <td>&nbsp;</td>
				  <td><input type="submit" value="'.CAR_BLOCK.'" name="block" style="background:#e5f9bb; cursor:pointer; cursor:hand;"/></td>
				</tr>';
			}else{
				echo '<tr>
				  <td colspan="2" align="center" style="color:red;"><b>'.SORRY_NO_CAR_AVAILABLE_TEXT.'</b></td>
				</tr>';
			}
			?>
           </table>
        </form>
        <? } ?></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
	$().ready(function() {
		$("#form1").validate();
		
     });
         
</script> 
<script src="js/jquery.validate.js" type="text/javascript"></script>
<?php include("footer.php"); ?>
