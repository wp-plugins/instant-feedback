function isElementInViewport(e){if(e instanceof jQuery){e=e[0]}var t=e.getBoundingClientRect();return t.top>=0&&t.left>=0&&t.bottom<=(window.innerHeight||document.documentElement.clientHeight)&&t.right<=(window.innerWidth||document.documentElement.clientWidth)}function callback(){if(block){block=false;cmye()}}function imageLoad(){var e=document.createElement("div");var t=document.createElement("img");var n=eff_json.ext_path;if(!eff_json.ext_path){n="/wp-content/plugins/instant-feedback"}t.src=n+"/loading.gif";e.style.textAlign="center";e.appendChild(t);return e}function fireIfElementVisible(e,t){return function(){if(isElementInViewport(e)){t()}}}function sendMessage(e){e.contentWindow.postMessage("resize",myeffecto_host)}function receiveMessage(e){var t=e.data;if(t){var n=t.indexOf("init")!=-1;if(n||t.indexOf("reco")!=-1){var r="175";var i=t.split("#");var s=i[0];if(s==="init"){r=i[1].valueOf();var o=i[2].valueOf();if(o=="0L"){r=0}else{r=Math.max(o,r)}}else{r=i[1]}r=r+"px";var u=i[3];try{u=u.split("(");var a=document.getElementById(u[0]);var f=a.parentNode;changeFrmHt(f,a,r)}catch(l){document.getElementById("effecto_bar").style.height=r}}}}function changeFrmHt(e,t,n){e.style.height=n;t.style.height=n;t.height=n}function makeSrc(e){var t=eff_json.effecto_uniquename;var n=eff_json.effectoPageurl;var r=eff_json.effectoAuthorName;var i=eff_json.effectoCategory;var s=eff_json.effectoPreview;var o=eff_json.effectoPostId;var u=eff_json.effectoPagetitle;var a=eff_json.effUserInfo;var f=e+"/ep?s="+encodeURIComponent(t);try{if(r){}}catch(l){r=null}try{if(i){}}catch(l){i=null}if(n!=null&&n!=""){f=f+"&l="+encodeURIComponent(n)}else{f=f+"&l="+encodeURIComponent(window.location.href)}if(s=="true"){f=f+"&ty=preview"}if(o!=null){f=f+"&p="+o}if(u==null||u==""){u=document.title}else{if(u.indexOf("<a")!=-1||u.indexOf("&lt;a")!=-1){u=document.title}}f=f+"&pt="+encodeURIComponent(u);if(r!=null){f=f+"&au="+r}if(i!=null){f=f+"&t="+i}if(a){f=f+"&uif="+encodeURIComponent(JSON.stringify(a))}if(eff_json.ext_path!=null&&eff_json.ext_path!=""){f=f+"&ext="+eff_json.ext_path}var c=document.referrer;if(c!=null&&c.length>0){var h=c.indexOf("myeffecto.com");if(h==-1){f=f+"&pv="+encodeURI(c)}else{f=f+"&pv=recommend"}}return f}function checkEle(e,t){for(var n=0;n<e.childNodes.length;n++){if(e.childNodes[n].id==t){return true}}return false}function cmye(){if(location.protocol=="https:"){myeffecto_host="https://myeffecto.appspot.com"}var e=document.getElementById("effecto_bar");var t=imageLoad();e.appendChild(t);var n="mye-"+eff_json.effecto_uniquename;if(!checkEle(e,n)){var r=makeSrc(myeffecto_host);var i=document.createElement("iframe");i.src=r;i.frameborder="no";i.onload=function(){t.parentNode.removeChild(t);i.style.display="block";sendMessage(i)};i.width="100%";i.style.display="none";i.id=n;i.scrolling="no";i.verticalscrolling="0";i.horizontalscrolling="no";i.style.width="100%";i.style.border="0";i.style.overflow="hidden";i.style.clear="both";i.allowtransparency="true";e.appendChild(i);e.style.clear="both"}}var block=true;var el=jQuery("#effecto_bar");var handler=fireIfElementVisible(el,callback);jQuery(window).on("DOMContentLoaded load resize scroll",handler);var myeffecto_host="http://www.myeffecto.com";window.addEventListener("message",receiveMessage,false);window.onunload=function(){delete myeffecto_host}