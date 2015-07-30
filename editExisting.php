<?php
global $hostString;
global $eff_settings_page;
$sname=$_GET['sname'];
$defaultPage = get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page;
?><div style="position:relative"><div id="load" style="display:none;"></div>
<iframe src="<?php echo $hostString ?>/plugin_editor?out=1&sname=<?php echo $sname?>" width="100%"  seamless="" scrolling="no" frameborder="0" allowtransparency="true" height="600px"></iframe>
<Script>
var eMeth=window.addEventListener ? 'addEventListener':'attachEvent';
var msgEv = eMeth == 'attachEvent' ? 'onmessage' : 'message';var detect = window[eMeth];
detect(msgEv,editMessage,false);
function editMessage(e){
 var m = e.data;
 if(e.origin=='<?php echo $hostString ?>' && m.indexOf('mye_edit')>-1){
jQuery("#load").css("display","");
 	window.location="<?php echo $defaultPage ?>";
 }}
</script>
</div>