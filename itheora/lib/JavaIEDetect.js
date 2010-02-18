/* PluginDetect v0.4.7 ( Java Flash VLC ) by Eric Gerds www.pinlady.net/PluginDetect */ if(!PluginDetect){var PluginDetect={getNum:function(A,_2){if(!this.num(A)){return null}var m;if(typeof _2=="undefined"){m=/[\d][\d\.\_,-]*/.exec(A)}else{m=(new RegExp(_2)).exec(A)}return m?m[0].replace(/[\.\_-]/g,","):null},hasMimeType:function(_4){var s,t,z,M=_4.constructor==String?[_4]:_4;for(z=0;z<M.length;z++){s=navigator.mimeTypes[M[z]];if(s&&s.enabledPlugin){t=s.enabledPlugin;if(t.name||t.description){return s}}}return null},findNavPlugin:function(N,_7){var _8=N.constructor==String?N:N.join(".*"),numS=_7===false?"":"\\d";var i,re=new RegExp(_8+".*"+numS+"|"+numS+".*"+_8,"i");var _a=navigator.plugins;for(i=0;i<_a.length;i++){if(re.test(_a[i].description)||re.test(_a[i].name)){return _a[i]}}return null},getAXO:function(_b){var _c,e;try{_c=new ActiveXObject(_b);return _c}catch(e){}return null},num:function(A){return (typeof A!="string"?false:(/\d/).test(A))},compareNums:function(_e,_f){if(!this.num(_e)||!this.num(_f)){return 0}if(this.plugin&&this.plugin.compareNums){return this.plugin.compareNums(_e,_f)}var m1=_e.split(","),m2=_f.split(","),x,p=parseInt;for(x=0;x<Math.min(m1.length,m2.length);x++){if(p(m1[x],10)>p(m2[x],10)){return 1}if(p(m1[x],10)<p(m2[x],10)){return -1}}return 0},formatNum:function(num){if(!this.num(num)){return null}var x,n=num.replace(/\s/g,"").replace(/[\.\_]/g,",").split(",").concat(["0","0","0","0"]);return n[0]+","+n[1]+","+n[2]+","+n[3]},initScript:function(){var $=this,IE;$.isIE=(/*@cc_on!@*/false);$.IEver=-1;$.ActiveXEnabled=false;if($.isIE){IE=(/msie\s*\d\.{0,1}\d*/i).exec(navigator.userAgent);if(IE){$.IEver=parseFloat((/\d.{0,1}\d*/i).exec(IE[0]),10)}var _14,x;_14=["ShockwaveFlash.ShockwaveFlash","Msxml2.XMLHTTP","Microsoft.XMLDOM","Msxml2.DOMDocument","TDCCtl.TDCCtl","Shell.UIHelper","Scripting.Dictionary","wmplayer.ocx"];for(x=0;x<_14.length;x++){if($.getAXO(_14[x])){$.ActiveXEnabled=true;break}}}if($.isIE){$.head=typeof document.getElementsByTagName!="undefined"?document.getElementsByTagName("head")[0]:null}},init:function(_15){if(typeof _15!="string"){return -3}_15=_15.toLowerCase().replace(/\s/g,"");var $=this,IE,p;if(typeof $[_15]=="undefined"){return -3}p=$[_15];$.plugin=p;if(typeof p.installed=="undefined"){p.minversion={};p.installed=null;p.version=null;p.getVersionDone=null}$.garbage=false;if($.isIE&&!$.ActiveXEnabled){return -2}return 1},isMinVersion:function(_17,_18,_19){var $=PluginDetect,i=$.init(_17);if(i<0){return i}if(typeof _18=="undefined"||_18==null){_18="0"}if(typeof _18=="number"){_18=_18.toString()}if(!$.num(_18)){return -3}_18=$.formatNum(_18);if(typeof _19=="undefined"){_19=null}var p=$.plugin,m=p.minversion;if(typeof m["a"+_18]=="undefined"){if(p.getVersionDone==null&&typeof p.isMinVersion!="undefined"&&$.IEver>=p.minIEver){var tmp,x;for(x in m){tmp=$.compareNums(_18,x.substring(1,x.length));if(m[x]==1&&tmp<=0){return 1}if(m[x]==-1&&tmp>=0){return -1}}m["a"+_18]=p.isMinVersion(_18,_19)?1:-1;if(p.version!=null||p.installed==-1||p.installed==1){p.getVersionDone=1}}else{if(p.getVersionDone==null){p.getVersion(_18,_19)}if(p.version!=null||p.installed!=null){p.getVersionDone=1;m["a"+_18]=(p.installed==-1?-1:(p.version==null?0:($.compareNums(p.version,_18)>=0?1:-1)))}else{m["a"+_18]=-1}}}$.cleanup();return m["a"+_18];return -3},getVersion:function(_1d,_1e){return null},cleanup:function(){var $=this;if($.garbage&&typeof window.CollectGarbage!="undefined"){window.CollectGarbage()}},isActiveXObject:function(_22){var $=this,result,e,s="<object width=\"1\" height=\"1\" "+"style=\"display:none\" "+$.plugin.getCodeBaseVersion(_22)+">"+$.plugin.HTML+"</object>";if($.head.firstChild){$.head.insertBefore(document.createElement("object"),$.head.firstChild)}else{$.head.appendChild(document.createElement("object"))}$.head.firstChild.outerHTML=s;try{$.head.firstChild.classid=$.plugin.classID}catch(e){}result=false;try{if($.head.firstChild.object){result=true}}catch(e){}try{if(result&&$.head.firstChild.readyState<4){$.garbage=true}}catch(e){}$.head.removeChild($.head.firstChild);return result},search:function(min){var $=this;if(typeof min!="undefined"){return $.isActiveXObject(min)}},dummy1:0}}PluginDetect.initScript();PluginDetect.java={mimeType:"application/x-java-applet",classID:"clsid:8AD9C840-044E-11D1-B3E9-00805F499D93",DTKclassID:"clsid:CAFEEFAC-DEC7-0000-0000-ABCDEFFEDCBA",DTKmimeType:"application/npruntime-scriptable-plugin;DeploymentToolkit",minWebStart:"1,4,2,0",JavaVersions:["1,9,1,25","1,8,1,25","1,7,1,25","1,6,1,25","1,5,0,25","1,4,2,25","1,3,1,25"],lowestPreApproved:"1,6,0,02",lowestSearchable:"1,3,1,0",searchJava:function(min,_32){var e,z,T,$=PluginDetect;var _34,C_DE,C,DE,v;var AXO=ActiveXObject;var _36=(typeof _32!="undefined")?_32:this.minWebStart;var Q=min.split(","),x;for(x=0;x<4;x++){Q[x]=parseInt(Q[x],10)}for(x=0;x<3;x++){if(Q[x]>9){Q[x]=9}}if(Q[3]>99){Q[3]=99}var _38="JavaPlugin."+Q[0]+Q[1]+Q[2]+(Q[3]>0?("_"+(Q[3]<10?"0":"")+Q[3]):"");for(z=0;z<this.JavaVersions.length;z++){if($.compareNums(min,this.JavaVersions[z])>0){return null}T=this.JavaVersions[z].split(",");_34="JavaPlugin."+T[0]+T[1];v=T[0]+"."+T[1]+".";for(C=T[2];C>=0;C--){if($.compareNums(T[0]+","+T[1]+","+C+",0",_36)>=0){try{new AXO("JavaWebStart.isInstalled."+v+C+".0")}catch(e){continue}}if($.compareNums(min,T[0]+","+T[1]+","+C+","+T[3])>0){return null}for(DE=T[3];DE>=0;DE--){C_DE=C+"_"+(DE<10?"0"+DE:DE);try{new AXO(_34+C_DE);return v+C_DE}catch(e){}if(_34+C_DE==_38){return null}}try{new AXO(_34+C);return v+C}catch(e){}if(_34+C==_38){return null}}}return null},minIEver:7,HTML:"<param name=\"code\" value=\"A14999.class\" />",getCodeBaseVersion:function(v){var r=v.replace(/[\.\_]/g,",").split(","),$=PluginDetect;if($.compareNums(v,"1,4,1,02")<0){v=r[0]+","+r[1]+","+r[2]+","+r[3]}else{if($.compareNums(v,"1,5,0,02")<0){v=r[0]+","+r[1]+","+r[2]+","+r[3]+"0"}else{v=Math.round((parseFloat(r[0]+"."+r[1],10)-1.5)*10+5)+","+r[2]+","+r[3]+"0"+",0"}}return "codebase=\"#version="+v+"\""},digits:[2,8,8,32],getFromMimeType:function(_3b){var x,t,$=PluginDetect;var re=new RegExp(_3b);var tmp,v="0,0,0,0",digits="";for(x=0;x<navigator.mimeTypes.length;x++){t=navigator.mimeTypes[x];if(re.test(t.type)&&t.enabledPlugin){t=t.type.substring(t.type.indexOf("=")+1,t.type.length);tmp=$.formatNum(t);if($.compareNums(tmp,v)>0){v=tmp;digits=t}}}return digits.replace(/[\.\_]/g,",")},hasRun:false,value:null,queryJavaHandler:function(){var $=PluginDetect.java,j=window.java,e;$.hasRun=true;try{if(typeof j.lang!="undefined"&&typeof j.lang.System!="undefined"){$.value=j.lang.System.getProperty("java.version")+" "}}catch(e){}},queryJava:function(){var $=PluginDetect,t=this,nua=navigator.userAgent,e;if(typeof window.java!="undefined"&&window.navigator.javaEnabled()){if(/gecko/i.test(nua)){if($.hasMimeType("application/x-java-vm")){try{var div=document.createElement("div"),evObj=document.createEvent("HTMLEvents");evObj.initEvent("focus",false,true);div.addEventListener("focus",t.queryJavaHandler,false);div.dispatchEvent(evObj)}catch(e){}if(!t.hasRun){t.queryJavaHandler()}}}else{if(/opera.9\.(0|1)/i.test(nua)&&/mac/i.test(nua)){return null}t.queryJavaHandler()}}return t.value},getVersion:function(min,jar){if(typeof min=="undefined"){min=null}if(typeof jar=="undefined"){jar=null}var _44=null,$=PluginDetect;var dtk=this.searchJavaDTK();if(dtk==-1&&$.isIE){this.installed=-1;return}if(dtk!=-1&&dtk!=null){_44=dtk}if(!$.isIE){var p1,p2,p,mt,tmp;mt=($.hasMimeType(this.mimeType)&&navigator.javaEnabled());if(!_44&&mt){tmp="Java[^\\d]*Plug-in";p=$.findNavPlugin(tmp);if(p){tmp=new RegExp(tmp,"i");p1=tmp.test(p.description)?$.getNum(p.description):null;p2=tmp.test(p.name)?$.getNum(p.name):null;if(p1&&p2){_44=($.compareNums($.formatNum(p1),$.formatNum(p2))>=0)?p1:p2}else{_44=p1||p2}}}if(!_44&&mt){tmp=this.getFromMimeType("application/x-java-applet.*jpi-version.*=");if(tmp!=""){_44=tmp}}if(!_44&&mt&&/macintosh.*safari/i.test(navigator.userAgent)){p=$.findNavPlugin("Java.*\\d.*Plug-in.*Cocoa",false);if(p){p1=$.getNum(p.description);if(p1){_44=p1}}}if(!_44){p=this.queryJava();if(p){_44=p}}if(!_44&&mt){p=this.appletDetect(jar);if(p[0]){_44=p[0]}}if(!_44&&mt&&!/macintosh.*ppc/i.test(navigator.userAgent)){tmp=this.getFromMimeType("application/x-java-applet.*version.*=");if(tmp!=""){_44=tmp}}this.installed=_44?1:-1;if(!_44&&mt){if(/safari/i.test(navigator.userAgent)){this.installed=0}}}else{var Q;if($.IEver>=this.minIEver){if(!_44){Q=this.findMax(this.lowestPreApproved,min);_44=this.searchJava(Q,this.lowestPreApproved)}if(!_44){tmp=this.appletDetect(jar);if(tmp[0]){_44=tmp[0]}}if(!_44){_44=this.CBSearch()}}else{if(!_44){Q=this.findMax(this.lowestSearchable,min);_44=this.searchJava(Q)}}if(min!=null&&!_44){return}this.installed=_44?1:-1}this.setVersion(_44)},isMinVersion:function(min,jar){if(typeof jar=="undefined"){jar=null}var _4a=null,$=PluginDetect,Q,tmp,dtk;dtk=this.searchJavaDTK();if(dtk==-1&&$.isIE){this.installed=-1;return false}if(dtk!=-1&&dtk!=null){_4a=dtk}if(!_4a){Q=this.findMax(this.lowestPreApproved,min);_4a=this.searchJava(Q,this.lowestPreApproved)}if(!_4a){tmp=this.appletDetect(jar);if(tmp[0]){_4a=tmp[0]}}if(!_4a){if(this.CBSearch(min)){this.installed=0;return true}}if(_4a){this.installed=1;this.setVersion(_4a);if($.compareNums(this.version,min)>=0){return true}};return false},findMax:function(_4b,_4c){var $=PluginDetect;if(typeof _4c=="undefined"||_4c==null||$.compareNums(_4c,_4b)<0){return _4b}return _4c},setVersion:function(_4e){var $=PluginDetect;this.version=$.formatNum($.getNum(_4e));if(typeof this.version=="string"&&this.allVersions.length==0){this.allVersions[0]=this.version}},allVersions:[],searchJavaDTK:function(){if(typeof this.DTKversion!="undefined"){return this.DTKversion}this.allVersions=[];var $=PluginDetect,e,x;var _51=[null,null],obj;var len=null;if($.isIE&&$.IEver>=6){_51=$.instantiate("object","","")}if(!$.isIE&&$.hasMimeType(this.DTKmimeType)){_51=$.instantiate("object","type="+this.DTKmimeType,"")}if(_51[0]&&_51[1]&&_51[1].parentNode){obj=_51[0].firstChild;if($.isIE&&$.IEver>=6){try{obj.classid=this.DTKclassID}catch(e){}try{if(obj.object&&obj.readyState<4){$.garbage=true}}catch(e){}}try{len=obj.jvms.getLength();if(len!=null&&len>0){for(x=0;x<len;x++){this.allVersions[x]=$.formatNum($.getNum(obj.jvms.get(x).version))}}}catch(e){}_51[1].parentNode.removeChild(_51[1])}this.DTKversion=this.allVersions.length>0?this.allVersions[this.allVersions.length-1]:(len==0?-1:null);return this.DTKversion},CBSearch:function(min){var $=PluginDetect;$.isActiveXObject("99,99,99,99");return (typeof min!="undefined"?$.search(min):$.search())},appletDetect:function(jar){if(!jar||typeof jar!="string"){return [null,null]}if(typeof this.appletDetectResult!="undefined"){return this.appletDetectResult}var $=PluginDetect,e,version=null,vendor=null,obj;var _57;var par="<param name=\"archive\" value=\""+jar+"\" />"+"<param name=\"mayscript\" value=\"true\" />"+"<param name=\"scriptable\" value=\"true\" />";if($.isIE){_57=$.instantiate("object","archive=\""+jar+"\" code=\"A.class\" type=\"application/x-java-applet\"","<param name=\"code\" value=\"A.class\" />"+par)}if(!$.isIE){_57=$.instantiate("object","archive=\""+jar+"\" classid=\"java:A.class\" type=\"application/x-java-applet\"",par)}if(_57[0]&&_57[1]&&_57[1].parentNode){obj=_57[0].firstChild;try{if($.isIE&&obj.object&&obj.readyState<4){$.garbage=true}}catch(e){}try{version=obj.getVersion()+" "}catch(e){}try{vendor=obj.getVendor()+" "}catch(e){}_57[1].parentNode.removeChild(_57[1])}this.appletDetectResult=[version,vendor];return this.appletDetectResult}};PluginDetect.flash={mimeType:["application/x-shockwave-flash","application/futuresplash"],progID:"ShockwaveFlash.ShockwaveFlash",classID:"clsid:D27CDB6E-AE6D-11CF-96B8-444553540000",getVersion:function(){var _5d=function(A){if(!A){return null}var m=/[\d][\d\,\.\s]*[rRdD]{0,1}[\d\,]*/.exec(A);return m?m[0].replace(/[rRdD\.]/g,",").replace(/\s/g,""):null};var p,$=PluginDetect,e,i,version=null,AXO=null,majV=null;if(!$.isIE){p=$.findNavPlugin("Flash");if(p&&p.description&&$.hasMimeType(this.mimeType)){version=_5d(p.description)}}else{for(i=15;i>2;i--){AXO=$.getAXO(this.progID+"."+i);if(AXO){majV=i.toString();break}}if(majV=="6"){try{AXO.AllowScriptAccess="always"}catch(e){return "6,0,21,0"}}try{version=_5d(AXO.GetVariable("$version"))}catch(e){}if(!version&&majV){version=majV}}this.installed=version?1:-1;this.version=$.formatNum(version);return true}};PluginDetect.instantiate=function(_62,_63,_64){var e,d=document,tag1="<"+_62+" width=\"1\" height=\"1\" "+_63+">"+_64+"</"+_62+">",body=(d.getElementsByTagName("body")[0]||d.body),div=d.createElement("div");if(body){body.appendChild(div)}else{try{d.write("<div>o</div><div>"+tag1+"</div>");body=(d.getElementsByTagName("body")[0]||d.body);body.removeChild(body.firstChild);div=body.firstChild}catch(e){try{body=d.createElement("body");d.getElementsByTagName("html")[0].appendChild(body);body.appendChild(div);div.innerHTML=tag1;return [div,body]}catch(e){}}return [div,div]}if(div&&div.parentNode){try{div.innerHTML=tag1}catch(e){}}return [div,div]};PluginDetect.vlc={mimeType:"application/x-vlc-plugin",progID:"VideoLAN.VLCPlugin",compareNums:function(_71,_72){var m1=_71.split(","),m2=_72.split(","),x;var a1,a2,b1,b2,t;for(x=0;x<Math.min(m1.length,m2.length);x++){t=/([\d]+)([a-z]?)/.test(m1[x]);a1=parseInt(m1[x],10);if(x==2){a2=RegExp.$2.length>0?RegExp.$2.charCodeAt(0):-1}t=/([\d]+)([a-z]?)/.test(m2[x]);b1=parseInt(m2[x],10);if(x==2){b2=RegExp.$2.length>0?RegExp.$2.charCodeAt(0):-1}if(a1!=b1){return (a1>b1?1:-1)}if(x==2&&a2!=b2){return (a2>b2?1:-1)}}return 0},getVersion:function(){var $=PluginDetect,p,version=null,e;if(!$.isIE){if($.hasMimeType(this.mimeType)){p=$.findNavPlugin(["VLC","(Plug-in|Plugin)"],false);if(p&&p.description){version=$.getNum(p.description,"[\\d][\\d\\.]*[a-z]*")}}this.installed=version?1:-1}else{p=$.getAXO(this.progID);if(p){try{version=$.getNum(p.VersionInfo,"[\\d][\\d\\.]*[a-z]*")}catch(e){}}this.installed=p?1:-1}this.version=$.formatNum(version)}};
