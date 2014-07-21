
function isElementInViewport (el) {

    //special bonus for those using jQuery
    if (el instanceof jQuery) {
        el = el[0];
    }

    var rect = el.getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
    );
}
var block = true;
function callback(){
	if(block){
		block=false;
    	cmye();
    }
} 
function imageLoad(){
	var loadCont =  document.createElement('div');
	var lod =  document.createElement('img');
	var imgSrc =eff_json.ext_path;
	if(!eff_json.ext_path){
		imgSrc="/wp-content/plugins/instant-feedback";
	}
	lod.src=imgSrc+"/loading.gif";
	loadCont.style.textAlign="center";
	loadCont.appendChild(lod);
	return loadCont;
}
function fireIfElementVisible (el, callback) {
    return function () {
        if ( isElementInViewport(el) ) {
            callback();
        }
    }
}
var el = jQuery("#effecto_bar");
var handler = fireIfElementVisible (el, callback);


//jQuery
jQuery(window).on('DOMContentLoaded load resize scroll', handler); 


var myeffecto_host = "http://www.myeffecto.com";
function sendMessage(effectoframe){
	 effectoframe.contentWindow.postMessage("resize", myeffecto_host);
}
window.addEventListener("message", receiveMessage, false);
function receiveMessage(event) {
	var msg = event.data;
	if(msg){
		var isInit =msg.indexOf("init")!=-1; 
		if(isInit || msg.indexOf("reco")!=-1){
			var ht = '175';var res = msg.split("#");var a = res[0];
			if(a==='init'){ht = res[1].valueOf();var minht = res[2].valueOf();
			if(minht=='0L'){ht=0;}else{	ht = Math.max(minht,ht); }
			}else{ht=res[1];}
			ht = ht + 'px';
			var eId=res[3];
			try{eId=eId.split("(");
			var fram=document.getElementById(eId[0]);
			var div=fram.parentNode;
			changeFrmHt(div,fram,ht)
			}catch(e){
			document.getElementById('effecto_bar').style.height = ht;}
		}
	}
}
function changeFrmHt(d,f,ht){
	d.style.height = ht;
	f.style.height = ht;
	f.height = ht;
}
function makeSrc(ho) {
	var sname = eff_json.effecto_uniquename;
	var pUrl = eff_json.effectoPageurl;
	var pAuthor = eff_json.effectoAuthorName;
	var pCategory = eff_json.effectoCategory;
	var pPreview = eff_json.effectoPreview;
	var pId = eff_json.effectoPostId;
	var pTitle = eff_json.effectoPagetitle;
	var userInfo = eff_json.effUserInfo;

	var effectoSrc = ho + "/ep?s="+ encodeURIComponent(sname);
	try {if (pAuthor) {}} catch (e) {pAuthor = null;}
	try {if (pCategory) {}} catch (e) {pCategory = null;}
	if (pUrl != null && pUrl != "") {
		effectoSrc = effectoSrc + '&l=' + encodeURIComponent(pUrl);
	} else {
		effectoSrc = effectoSrc + '&l=' + encodeURIComponent(window.location.href);
	}
	if (pPreview == 'true') {
		effectoSrc = effectoSrc + "&ty=preview";
	}
	if (pId != null) {
		effectoSrc = effectoSrc + '&p=' + pId;
	}

	if(pTitle==null || pTitle==""){pTitle=document.title}
	else {
		if (pTitle.indexOf("<a") != -1 || pTitle.indexOf("&lt;a") != -1) {
			pTitle = document.title;
		}
	}
	effectoSrc = effectoSrc + "&pt=" + encodeURIComponent(pTitle);
	if (pAuthor != null) {
		effectoSrc = effectoSrc + "&au=" + pAuthor;
	}
	if (pCategory != null) {
		effectoSrc = effectoSrc + "&t=" + pCategory;
	}
	if (userInfo) {
		effectoSrc = effectoSrc + "&uif=" +  encodeURIComponent(JSON.stringify(userInfo));
	}
	if(eff_json.ext_path!=null && eff_json.ext_path!=""){
		effectoSrc = effectoSrc + "&ext="+eff_json.ext_path;
	}
	var prevPag= document.referrer;
	if(prevPag!=null && prevPag.length>0){
		var rec=prevPag.indexOf("myeffecto.com")
		if(rec==-1){
			effectoSrc = effectoSrc + "&pv=" + encodeURI(prevPag);
		}
		else{
			effectoSrc = effectoSrc + "&pv=recommend";
		}
	}
	return effectoSrc;
}
function checkEle(ele, innerId) {
	for ( var i = 0; i < ele.childNodes.length; i++) {
	if (ele.childNodes[i].id == innerId) {return true;}}
	return false;
}
function cmye() {
	if (location.protocol == "https:") {
		myeffecto_host = "https://myeffecto.appspot.com";
	}
	var effectobar = document.getElementById('effecto_bar');
	var imgEl = imageLoad();
	effectobar.appendChild(imgEl);
	var myeId = 'mye-' + eff_json.effecto_uniquename;
	if(!checkEle(effectobar, myeId)){
		var effectoSrc = makeSrc(myeffecto_host);
		var effectoframe = document.createElement('iframe');
		effectoframe.src = effectoSrc;
		effectoframe.frameborder = "no";
		effectoframe.onload = function() {
			imgEl.parentNode.removeChild(imgEl);
			effectoframe.style.display = "block";
			sendMessage(effectoframe);
		};
		effectoframe.width = "100%";
		effectoframe.style.display = "none";
		effectoframe.id = myeId;
		effectoframe.scrolling = "no";
		effectoframe.verticalscrolling = "0";
		effectoframe.horizontalscrolling = "no";
		effectoframe.style.width = "100%";
		effectoframe.style.border = "0";
		effectoframe.style.overflow = "hidden";
		effectoframe.style.clear = "both";
		effectoframe.allowtransparency = "true";
		effectobar.appendChild(effectoframe);
		effectobar.style.clear = "both";
	}
}
window.onunload=function(){delete myeffecto_host};