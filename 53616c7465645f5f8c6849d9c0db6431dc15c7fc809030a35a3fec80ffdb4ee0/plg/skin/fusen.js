/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// fusen.js
// 付箋プラグイン用JavaScript
// ohguma@rnc-com.co.jp
//
// v 1.0 2005/03/12 初版
// v 1.1 (欠番)
// v 1.2 2005/03/16 削除確認追加,Lock時のDrag廃止
// v 1.3 2005/03/17 XHTML1.1対応?
// v 1.4 2005/03/18 検索機能追加
// v 1.5 2005/03/18 検索機能修正(convert_html後の表示内容で検索)
// v 1.6 (欠番)
// v 1.7 2005/04/02 onload修正,関数名変更,付箋データ保持方法変更,線関係修正,DblClick対応
// v 1.8 2005/04/03 AJAX対応
// v 1.9 2005/04/13 Opera対応(Opera時リアルタイム更新しない)
//                  一覧からページ表示時のエラー対応
//                  ゴミ箱表示時関連のバグ修正
//                  Timer関連のバグ修正
// v 1.10 2005/05/10 JSON形式の付箋データ受信時にunescape処理追加

var fusenOffsetX = 0;
var fusenOffsetY = 0;

// browser check
var fusenOP = window.opera;             // OP
var fusenGK = document.getElementById;  // Gecko or IE
var fusenIE = document.all;             // IE

// mouse position
var fusenMouseX = '';
var fusenMouseY = '';

var fusenObj;			// fusen data(JSON, set fusen.ini.php too)
var fusenBorderObj;		// define border style(set fusen.ini.php)
var fusenMovingObj = null;
var fusenDustboxFlg = false;
var fusenTimerID;		// Interval Timer ID
var fusenInterval;		// Interval time [msec](set fusen.ini.php)

function fusen_getElement(id) {
	return document.getElementById(id);
}

// Open window for object information.
function fusen_debugobj(objref) {
	var obj = null;
	var str = '';
	if (typeof(objref) == 'string') obj = fusen_getElement(objref);
	else obj = objref;
	if (obj) 
		for(i in obj)
			try {
				str += i + "=" + obj[i] + "\n";
			} catch (e) {
			}
	else str = objref;
	debugWin = window.open('', '');
	window.debugWin.document.write('<html>\n<body>\n<pre>\n' + str + '\n</pre>\n</body>\n</html>');
}

// Create HTTP request object.
function fusen_httprequest(){
	if (fusenOP) return '';
	try {
		return new XMLHttpRequest();
	} catch(e) {
		var MSXML_XMLHTTP_PROGIDS = new Array(
			'MSXML2.XMLHTTP.5.0',
			'MSXML2.XMLHTTP.4.0',
			'MSXML2.XMLHTTP.3.0',
			'MSXML2.XMLHTTP',
			'Microsoft.XMLHTTP'
		);
		for (var i in MSXML_XMLHTTP_PROGIDS) {
			try {
				return new ActiveXObject(MSXML_XMLHTTP_PROGIDS[i]);
			} catch (e) {
			}
		}
	}
	throw 'Unable to create HTTP request object.';
}

// Post fusen data.
function fusen_postdata() {
	var frm = fusen_getElement('edit_frm');
	var re = /input|textarea|select/i;
	var tag = '';
	var postdata = '';

	if (fusenOP) return '';
	for (var i = 0; i < frm.length; i++ ) {
		var child = frm[i];
		tag = String(child.tagName);
		if (tag.match(re)) {
			if (postdata!='') postdata += '&';
			postdata += encodeURIComponent(child.name) +
				'=' + encodeURIComponent(child.value);
		}
	}
	try {
		var xmlhttp = fusen_httprequest();
		var url = location.pathname;
		xmlhttp.open('POST', url, false);
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded;');
		xmlhttp.send(postdata);
	} catch(e) {
		alert(e);
		throw 'Unable to post fusen data.';
	} 
	if(xmlhttp.status == 200 || xmlhttp.status == 0){
		return xmlhttp.responseText;
	} else {
		throw 'Fusen data not posted.';
	}
}

// Get fusen date.
function fusen_getdata() {
	var txt;
	if (fusenOP) return '';
	try {
		fusen_getElement('edit_mode').value = 'getdata';
		var txt = unescape(fusen_postdata());
		eval( 'fusenObj = ' + txt );
	} catch(e) {
		alert(e);
	}
}

// Get text in fusen.
function fusen_getchildtext(objref) {
	var obj;
	var output = '';
	if (typeof objref == 'string') obj = fusen_getElement(objref);
	else obj = objref;
	if (!obj) return '';
	var group = obj.childNodes;
	for (var i = 0; i < group.length; i++) {
		if (group[i].nodeType == 3) output += group[i].nodeValue.replace(/[\r\n]/,'');
		if (group[i].childNodes.length > 0) output += fusen_getchildtext(group[i]);
	}
	return output; 
}

function fusen_grep(pat) {
	fusenMovingObj = null;
	var re = new RegExp(pat, 'im');
	for(var id in fusenObj) {
		if (!fusenDustboxFlg && (fusenObj[id].del)) continue;
		if (fusenDustboxFlg && !(fusenObj[id].del)) continue;
		if (fusenObj[id].disp.match(re)) {
			fusen_getElement('fusen_id' + id).style.visibility = "visible";
		} else {
			fusen_getElement('fusen_id' + id).style.visibility = "hidden";
		}
	}
}

// editbox control
function fusen_new() {
	fusenMovingObj = null;
	fusen_stopTimer();
	fusen_getElement('edit_id').value = '';
	fusen_getElement('edit_ln').value = '';
	fusen_getElement('tc000000').selected = true;
	fusen_getElement('bgffffff').selected = true;
	fusen_getElement('edit_body').value = '';
	fusen_getElement('edit_l').value = fusenMouseX;
	fusen_getElement('edit_t').value = fusenMouseY;
	fusen_getElement('edit_mode').value = 'edit';
	fusen_show('fusen_editbox');
}

function fusen_editbox_hide() {
	fusenMovingObj = null;
	fusen_hide('fusen_editbox');
	fusen_startTimer();
}

function fusen_setpos(id) {
	fusenMovingObj = null;
	var obj = fusen_getElement('fusen_id' + id);
	fusen_getElement('edit_id').value = id;
	fusen_getElement('edit_l').value = parseInt(obj.style.left.replace("px",""));
	fusen_getElement('edit_t').value = parseInt(obj.style.top.replace("px",""));
//	fusen_getElement('edit_z').value = fusen_getElement(id).style.zIndex;
	fusen_getElement('edit_mode').value = 'set';
//	fusen_getElement('edit_frm').submit();
	fusen_postdata();
}

function fusen_edit(id) {
	fusenMovingObj = null;
	fusen_stopTimer();
	var obj = fusen_getElement('fusen_id' + id);
	fusen_getElement('edit_id').value = id;
	fusen_getElement('edit_l').value = parseInt(obj.style.left.replace("px",""));
	fusen_getElement('edit_t').value = parseInt(obj.style.top.replace("px",""));
	fusen_getElement('edit_ln').value = (fusenObj[id].ln) ? 'id' + fusenObj[id].ln : '';
	fusen_getElement('edit_body').value = fusenObj[id].txt;
	fusen_getElement('edit_mode').value = 'edit';

	var tcid = fusenObj[id].tc;
	if (!tcid) tcid = 'tc000000';
	else tcid = 'tc' + tcid.substr(1);
	var tcobj = fusen_getElement(tcid);
	if (!tcobj) fusen_getElement('tc000000').selected = true;
	else fusen_getElement(tcid).selected = true;

	var bgid = fusenObj[id].bg;
	if (!bgid) bgid = 'bgffffff';
	else bgid = 'bg' + bgid.substr(1);
	var bgobj = fusen_getElement(bgid);
	if (!bgobj) fusen_getElement('bg000000').selected = true;
	else fusen_getElement(bgid).selected = true;

	fusen_show('fusen_editbox');
}

function fusen_link(id) {
	fusenMovingObj = null;
	fusen_getElement('edit_l').value = 200;
	fusen_getElement('edit_t').value = 200;
	fusen_getElement('edit_id').value = '';
	fusen_getElement('edit_ln').value = 'id' + id;
	fusen_getElement('edit_body').value = '';
	fusen_getElement('edit_mode').value = 'edit';
	fusen_show('fusen_editbox');
}

function fusen_del(id) {
	fusenMovingObj = null;
	var ok;
	if (fusenDustboxFlg) ok = confirm('完全削除しますか？');
	else ok = confirm('削除しますか？');
	if (ok) {
		fusen_getElement('edit_id').value = id;
		fusen_getElement('edit_mode').value = 'del';
		fusen_getElement('edit_frm').submit();
	}
}

function fusen_lock(id) {
	fusenMovingObj = null;
	fusen_getElement('edit_id').value = id;
	fusen_getElement('edit_pass').value = prompt('管理用パスワードを入力してください。','');
	fusen_getElement('edit_mode').value = 'lock';
	fusen_getElement('edit_frm').submit();
}

function fusen_unlock(id) {
	fusenMovingObj = null;
	fusen_getElement('edit_id').value = id;
	fusen_getElement('edit_pass').value = prompt('管理用パスワードを入力してください。','');
	fusen_getElement('edit_mode').value = 'unlock';
	fusen_getElement('edit_frm').submit();
}

function fusen_recover(id) {
	fusenMovingObj = null;
	fusen_getElement('edit_id').value = id;
	fusen_getElement('edit_mode').value = 'recover';
	fusen_getElement('edit_frm').submit();
}

function fusen_show(id) {
	fusen_getElement(id).onmousedown = null;
	fusen_getElement(id).style.visibility = "visible";
	fusen_getElement(id).style.zIndex = 2;
	document.onmouseup = null;
	document.onmousemove = null;
}

function fusen_hide(id) {
	fusen_getElement(id).style.visibility = "hidden";
	document.onmouseup = fusen_onmouseup;
	document.onmousemove = fusen_onmousemove;
}

function fusen_dustbox() {
	fusenMovingObj = null;
	fusenDustboxFlg = !fusenDustboxFlg;
	for(var id in fusenObj) {
		var obj = fusen_getElement('fusen_id' + id);
		if (fusenObj[id].del) {
			if (fusenDustboxFlg) obj.style.visibility = 'visible';
			else obj.style.visibility = 'hidden';
		} else {
			if (fusenDustboxFlg) obj.style.visibility = 'hidden';
			else obj.style.visibility = 'visible';
		}
	}
	if (fusenDustboxFlg) fusen_removelines();
	else fusen_setlines();
}

function fusen_create_menuobj(id, mode) {
	var menuobj = document.createElement("DIV");
	menuobj.style.color = '#000000';
	menuobj.style.width = 'auto';
	menuobj.style.height = 'auto';
	menuobj.style.backgroundColor = '#cccccc';
	menuobj.style.borderBottom = '1px solid #000000';
	menuobj.style.padding = '1px';
	menuobj.style.whiteSpace = 'nowrap';
	menuobj.style.fontSize = '80%';
	menuobj.innerHTML = 'id' + id
	if (mode == 'del')
		menuobj.innerHTML +=
			' <a href="javascript:fusen_recover(' + id + ')" title="ゴミ箱から戻す">recover</a>' +
			' <a href="javascript:fusen_del(' + id + ')" title="完全削除">del</a>';
	else if (mode == 'lock')
		menuobj.innerHTML +=
			' <a href="javascript:fusen_unlock(' + id + ')" title="ロック解除">unlock</a>';
	else 
		menuobj.innerHTML +=
			' <a href="javascript:fusen_setpos(' + id + ')" title="位置設定">set</a>' +
			' <a href="javascript:fusen_edit(' + id + ')" title="編集">edit</a>' +
			' <a href="javascript:fusen_lock(' + id + ')" title="ロック">lock</a>' +
			' <a href="javascript:fusen_link(' + id + ')" title="線を引く">line</a>' +
			' <a href="javascript:fusen_del(' + id + ')" title="削除">del</a>';
	return menuobj;
}

function fusen_create_contentsobj(id, obj) {
	var contentsobj = document.createElement("DIV");
	contentsobj.style.width = 'auto';
	contentsobj.style.height = 'auto';
	contentsobj.style.padding = '2px';
	contentsobj.id = 'fusen_id' + id + 'contents';
	contentsobj.innerHTML = obj.disp;
	return contentsobj;
}

function fusen_create(id, obj) {
	var fusenobj = document.createElement("DIV");
	var menuobj;
	var border;
	var visible = 'visible';
	if (obj.del) {
		menuobj =  fusen_create_menuobj(id, 'del');
		border = fusenBorderObj['del'];
	} else  if (obj.lk) {
		menuobj =  fusen_create_menuobj(id, 'lock');
		border = fusenBorderObj['lock'];
	} else {
		menuobj =  fusen_create_menuobj(id, 'normal');
		border = fusenBorderObj['normal'];
	}
	visible = (fusenDustboxFlg ^ (!obj.del)) ? 'visible' : 'hidden';
	fusenobj.id = 'fusen_id' + id;
	fusenobj.style.left = obj.x + 'px';
	fusenobj.style.top = obj.y + 'px';
	fusenobj.style.color = obj.tc;
	fusenobj.style.backgroundColor = obj.bg;
	fusenobj.style.zIndex = obj.z;
	fusenobj.style.border = border;
	fusenobj.style.visibility = visible;
	fusenobj.style.position = 'absolute';
	fusenobj.style.whiteSpace = 'nowrap';
	fusenobj.style.width = 'auto';
	fusenobj.style.height = 'auto';
	if (!fusenIE && fusenGK) fusenobj.style.MozOpacity = 0.9;
	fusenobj.appendChild(menuobj);
	fusenobj.appendChild(fusen_create_contentsobj(id, obj));
	return fusenobj;
}


// Line draw

function fusen_removelines() {
	var id, lineid, obj;
	for(id in fusenObj) {
		if (fusenObj[id].ln) {
			lineid = 'line' + id + '_' + fusenObj[id].ln;
			obj = fusen_getElement(lineid);
			if (obj) obj.parentNode.removeChild(obj);
		}
	}
}

function fusen_setlines() {
	if (!fusenDustboxFlg) {
		for(var id in fusenObj) {
			if (fusenObj[id].ln && !fusenObj[id].del && !fusenObj[fusenObj[id].ln].del) {
				fusen_setline(id, fusenObj[id].ln);
			}
		}
	}
}

function fusen_setline(fromid, toid){
	function getCenter(obj){
		x = parseInt(obj.style.left.replace("px",""));
		x = x + obj.offsetWidth / 2;
		return x;
	}
	function getVCenter(obj){
		y = parseInt(obj.style.top.replace("px",""));
		y = y + obj.offsetHeight / 2;
		return y;
	}

	var lineid = 'line' + fromid + '_' + toid;
	var obj = fusen_getElement(lineid);
	if (obj) obj.parentNode.removeChild(obj);
	var fobj = fusen_getElement('fusen_id' + fromid);
	var tobj = fusen_getElement('fusen_id' + toid);
	if(!fobj) return;
	if(!tobj) return;
	var x1 = getCenter(fobj);
	var y1 = getVCenter(fobj);
	var x2 = getCenter(tobj);
	var y2 = getVCenter(tobj);
	var obj = fusen_drawLine(x1, y1, x2, y2, '#000000', lineid);
	document.getElementById('fusen_area').appendChild(obj);
}

function fusen_drawLine(x1, y1, x2, y2, color, nid){
	function _drawLine(x1,y1,x2,y2,color){
		var objLine = document.createElement("div")
		var strColor = color
		with(objLine.style){
			backgroundColor = strColor
			position  = "absolute"
			overflow  = "hidden"
			width     = Math.abs(x2-x1+1) + "px"
			height    = Math.abs(y2-y1+1) + "px"
			top  = Math.min(y1,y2) + "px"
			left = Math.min(x1,x2) + "px"
			zIndex = "0"
		}
		return objLine;
	}

	var objLines = document.createElement("div")
	objLines.id = nid;
	if((x1 == x2) || (y1 == y2)){
		objLines.appendChild(_drawLine(x1,y1,x2,y2,color));
	} else{
		objLines.appendChild(_drawLine(x1,y1,x1,y2,color));
		objLines.appendChild(_drawLine(x1,y2,x2,y2,color));
	}
	return objLines;
}


// Event

function fusen_onmousedown(e) {
	fusen_stopTimer();
	fusenMovingObj = this;
	if (fusenIE) {
		fusenOffsetX = event.clientX + document.body.scrollLeft - fusenMovingObj.style.posLeft;
		fusenOffsetY = event.clientY + document.body.scrollTop - fusenMovingObj.style.posTop;
	} else {
		fusenOffsetX = e.pageX - parseInt(fusenMovingObj.style.left.replace("px",""));
		fusenOffsetY = e.pageY - parseInt(fusenMovingObj.style.top.replace("px",""));
	}
	for(var id in fusenObj) {
		fusen_getElement('fusen_id' + id).style.zIndex = 1;
	}
	fusenMovingObj.style.zIndex = 2;
	return false;
}

function fusen_onmousemove(e) {
	if(fusenIE){
		fusenMouseX = document.body.scrollLeft + event.clientX;
		fusenMouseY = document.body.scrollTop + event.clientY;
	} else {
		fusenMouseX = e.pageX;
		fusenMouseY = e.pageY;
	}
	if (fusenMovingObj) {
		if (fusenIE) {
			fusenMovingObj.style.posLeft = event.clientX + document.body.scrollLeft - fusenOffsetX;
			fusenMovingObj.style.posTop = event.clientY + document.body.scrollTop - fusenOffsetY;
		} else {
			fusenMovingObj.style.left = (e.pageX - fusenOffsetX) + "px";
			fusenMovingObj.style.top = (e.pageY - fusenOffsetY) + "px";
		}
		if (!fusenDustboxFlg) fusen_setlines();
		return false;
	}
}

function fusen_onmouseup(e) {
	if (fusenMovingObj) fusen_setpos(fusenMovingObj.id.replace('fusen_id',''));
	fusenMovingObj = null;
	fusen_startTimer();
}

// Timer

function fusen_stopTimer() {
//	if ( fusenInterval > 0 ) {
		clearTimeout(fusenTimerID);
//	}
}

function fusen_startTimer() {
	if ( fusenInterval > 0 ) {
		fusenTimerID = setTimeout("fusen_init()", fusenInterval);
	}
}

// Initialize

function fusen_init() {
	var obj = fusen_getElement('fusen_area');
	if (!obj) {
		obj = document.createElement("DIV");
		obj.id = 'fusen_area';
		bodyobj = document.getElementsByTagName("BODY");
		bodyobj[0].appendChild(obj);
	};
	while (obj.childNodes.length > 0) obj.removeChild(obj.firstChild);
	if (!fusenOP) fusen_getdata();
	for(var id in fusenObj) {
		obj = fusen_create(id, fusenObj[id]);
		document.getElementById('fusen_area').appendChild(obj);
		if (!(fusenObj[id].del) && !(fusenObj[id].lk)) { 
			obj.onmousedown = fusen_onmousedown;
			obj.onmouseup = fusen_onmouseup;
			obj.onmousemove = fusen_onmousemove;
		}
	}
	fusen_setlines();
	fusen_startTimer();
}


var __fusen_onload_save = window.onload;
window.onload = function() {
	if (__fusen_onload_save) __fusen_onload_save();
	fusen_init();
	fusen_startTimer();
}

if (fusenIE) {
	var __fusen_ondblclick_save = document.ondblclick;
	document.ondblclick = function() {
		if (__fusen_ondblclick_save) __fusen_ondblclick_save();
		fusen_new();
	}
} else {
	var __fusen_ondblclick_save = window.ondblclick;
	window.ondblclick = function() {
		if (__fusen_ondblclick_save) __fusen_ondblclick_save();
		fusen_new();
	}
}
