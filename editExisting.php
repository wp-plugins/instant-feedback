<?php
global $hostString;
global $eff_settings_page;
$sname=$_GET['sname'];
$defaultPage = get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page;

$editUrl="/plugin_editor?out=1&sname=".$sname;
$editUrl=urlencode($editUrl);
$frameUrl=$hostString."/auth?action=extAcess&from=wp&l=".get_option('siteurl')."&sname=".$sname."&callback=".$editUrl;

?><div style="position:relative"><div id="load" style="display:none;"></div>
<iframe src="<?php echo $frameUrl ?>" width="100%"  seamless="" scrolling="no" frameborder="0" allowtransparency="true" height="600px"></iframe>
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