/*----------------------------------------------------------------------------\
|                            Sortable Table 1.12+02                           |
|-----------------------------------------------------------------------------|
|                         Created by Erik Arvidsson                           |
|                  (http://webfx.eae.net/contact.html#erik)                   |
|                      For WebFX (http://webfx.eae.net/)                      |
|-----------------------------------------------------------------------------|
| A DOM 1 based script that allows an ordinary HTML table to be sortable.     |
|-----------------------------------------------------------------------------|
|                  Copyright (c) 1998 - 2006 Erik Arvidsson                   |
|-----------------------------------------------------------------------------|
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License.  You may obtain a copy |
| of the License at http://www.apache.org/licenses/LICENSE-2.0                |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| Unless  required  by  applicable law or  agreed  to  in  writing,  software |
| distributed under the License is distributed on an  "AS IS" BASIS,  WITHOUT |
| WARRANTIES OR  CONDITIONS OF ANY KIND,  either express or implied.  See the |
| License  for the  specific language  governing permissions  and limitations |
| under the License.                                                          |
|-----------------------------------------------------------------------------|
| 2003-01-10 | First version                                                  |
| 2003-01-19 | Minor changes to the date parsing                              |
| 2003-01-28 | JScript 5.0 fixes (no support for 'in' operator)               |
| 2003-02-01 | Sloppy typo like error fixed in getInnerText                   |
| 2003-07-04 | Added workaround for IE cellIndex bug.                         |
| 2003-11-09 | The bDescending argument to sort was not correctly working     |
|            | Using onclick DOM0 event if no support for addEventListener    |
|            | or attachEvent                                                 |
| 2004-01-13 | Adding addSortType and removeSortType which makes it a lot     |
|            | easier to add new, custom sort types.                          |
| 2004-01-27 | Switch to use descending = false as the default sort order.    |
|            | Change defaultDescending to suit your needs.                   |
| 2004-03-14 | Improved sort type None look and feel a bit                    |
| 2004-08-26 | Made the handling of tBody and tHead more flexible. Now you    |
|            | can use another tHead or no tHead, and you can chose some      |
|            | other tBody.                                                   |
| 2006-04-25 | Changed license to Apache Software License 2.0                 |
|-----------------------------------------------------------------------------|
| 2005-10-06 | condition check (&& c.firstChild.nodeType != 3) panda@arino.jp |
| 2005-10-15 | multiple tHead (and related tBody) rows support panda@arino.jp |
| 2006-09-05 | sortTypes existing check / safari support       panda@arino.jp |
|-----------------------------------------------------------------------------|
| Created 2003-01-10 | All changes are in the log above. | Updated 2006-09-05 |
|-----------------------------------------------------------------------------|
| Modify by オヤジ戦隊ダジャレンジャー(Twitter:@dajya_ranger_)                |
|           SEの良心（https://dajya-ranger.com/）                             |
|-----------------------------------------------------------------------------|
| 2019/08/21 | ファイル名を sortable-table.js に変更                          |
| 2019/08/26 | ソート状態の画像ファイル名を変更                               |
| 2019/08/26 | ソート状態の画像を動的に変更（コード追加）                     |
| 2019/08/27 | SortableTable.toNumber カンマを除去して数値に変換する          |
| 2019/08/29 | SortableTable.toDate 様々な日付形式と時刻も対応するため作り替え|
| 2020/07/30 | テーブルの奇数行・偶数行の背景色をJavaScriptへ移行             |
|            | ヘッダ行の文字選択を不可に修正                                 |
|            | 動的にCSSが完全に置き換わらない不具合を修正                    |
| 2020/08/01 | 添付ファイルドラッグ＆ドロップアップロード対応プラグイン対応   |
| 2020/08/06 | ヘッダ行の折返し禁止指定対応                                   |
\----------------------------------------------------------------------------*/

function SortableTable(oTable, oSortTypes, oBackColors, oNoWrap) {

	this.sortTypes = oSortTypes || [];

	// 2020/07/30 テーブルの奇数行・偶数行の背景色をJavaScriptへ移行
	this.oddColor = oBackColors[0];
	this.evenColor = oBackColors[1];

	// 2020/08/06 ヘッダ行の折返し禁止指定対応
	this.thNoWrap = oNoWrap;

	this.sortColumn = null;
	this.sortRow = null;
	this.descending = null;

	var oThis = this;
	this._headerOnclick = function (e) {
		oThis.headerOnclick(e);
	};

	if (oTable) {
		this.setTable(oTable);
		this.document = oTable.ownerDocument || oTable.document;
	}
	else {
		this.document = document;
	}

	// only IE needs this
	var win = this.document.defaultView || this.document.parentWindow;
	this._onunload = function () {
		oThis.destroy();
	};
	if (win && typeof win.attachEvent != "undefined") {
		win.attachEvent("onunload", this._onunload);
	}

	// 戻り値セット
	return true;
}

SortableTable.gecko = navigator.product == "Gecko";
SortableTable.safari = (navigator.userAgent.indexOf("Safari") != -1);
SortableTable.msie = /msie/i.test(navigator.userAgent);
// Mozilla is faster when doing the DOM manipulations on
// an orphaned element. MSIE is not
SortableTable.removeBeforeSort = SortableTable.gecko;

SortableTable.prototype.onsort = function () {};

// default sort order. true -> descending, false -> ascending
SortableTable.prototype.defaultDescending = false;

// shared between all instances. This is intentional to allow external files
// to modify the prototype
SortableTable.prototype._sortTypeInfo = {};

SortableTable.prototype.setTable = function (oTable) {
	if ( this.tHead )
		this.uninitHeader();
	this.element = oTable;
	this.setTHead( oTable.tHead );
	this.setTBody( oTable.tBodies[0] );
};

SortableTable.prototype.setTHead = function (oTHead) {
	if (this.tHead && this.tHead != oTHead )
		this.uninitHeader();
	this.tHead = oTHead;
	this.step = oTHead.rows.length;
	this.initHeader( this.sortTypes );
};

SortableTable.prototype.setTBody = function (oTBody) {
	this.tBody = oTBody;
};

SortableTable.prototype.setSortTypes = function ( oSortTypes ) {
	if ( this.tHead )
		this.uninitHeader();
	this.sortTypes = oSortTypes || [];
	if ( this.tHead )
		this.initHeader( this.sortTypes );
};

// adds arrow containers and events
// also binds sort type to the header cells so that reordering columns does
// not break the sort types
SortableTable.prototype.initHeader = function (oSortTypes) {
	if (!this.tHead) return;

	this.sortTypes = new Array(this.tHead.rows.length);
	this.head
	var index = 0;

	var doc = this.tHead.ownerDocument || this.tHead.document;
	var sortTypes = oSortTypes || [];

	for (var row = 0; row < this.tHead.rows.length; row++) {
		var cells = this.tHead.rows[row].cells;
		this.sortTypes[row] = new Array(cells.length);
		for (var column = 0; column < cells.length; column++) {
			var c = cells[column];
			var sortType = sortTypes[index++] || 'None';
			this.sortTypes[row][column] = sortType;
			if (sortType == 'None') { continue; }

			if (c.childNodes.length > 1) {
				// 2020/08/01 添付ファイルドラッグ＆ドロップアップロード対応プラグイン対応
				// 子ノードがテキスト以外にある場合はソート状態画像がセット済み
				// よってソート状態画像が複数セットされるのを回避する
				continue;
			}

			if (c.firstChild.nodeName == 'A') {
				c.firstChild.href='javascript:void(0)';
			}

			var img = doc.createElement('IMG');
			// 2019/08/26 ソート状態の画像ファイル名を変更
			img.src = 'image/sort-blank.png';
			c.appendChild(img);

			if (typeof c.addEventListener != "undefined") {
				c.addEventListener("click", this._headerOnclick, false);
			} else if (typeof c.attachEvent != "undefined") {
				c.attachEvent("onclick", this._headerOnclick);
			} else {
				c.onclick = this._headerOnclick;
			}
		}
	}
	// 2020/07/30 ヘッダ行の文字選択を不可に修正
	var thCells = this.element.getElementsByClassName('style_th');
	for ( var loopIndex = 0; loopIndex < thCells.length; loopIndex++ ) {
		thCells[loopIndex].style.userSelect = "none";
		// 2020/08/06 ヘッダ行の折返し禁止指定対応
		if (this.thNoWrap) {
			// 折返し禁止指定がある場合
			thCells[loopIndex].style.whiteSpace = "nowrap";
		}
	}

	this.updateHeaderArrows();
};

// remove arrows and events
SortableTable.prototype.uninitHeader = function () {
	if (! this.tHead) { return; }
	for (var row = 0; row < this.tHead.rows.length; row++) {
		var cells = this.tHead.rows[row].cells;
		for (var column = 0; column < cells.length; column++) {
			var c = cells[column];
			if (this.getSortType(row, column) == 'None') { continue; }
			c.removeChild(c.lastChild);
			if (typeof c.removeEventListener != "undefined") {
				c.removeEventListener("click", this._headerOnclick, false);
			} else if (typeof c.detachEvent != "undefined") {
				c.detachEvent("onclick", this._headerOnclick);
			}
		}
	}
};

SortableTable.prototype.updateHeaderArrows = function () {
	if (! this.tHead) { return; }
	for (var row = 0; row < this.tHead.rows.length; row++) {
		var cells = this.tHead.rows[row].cells;
		for (var column = 0; column < cells.length; column++) {
			var c = cells[column];
			if (this.getSortType(row, column) == 'None') { continue; }
			var img = c.lastChild;
			if (row == this.sortRow && column == this.sortColumn) {
				// 2019/08/26 ソート状態の画像を動的に変更（コード追加）
				// 2020/07/30 動的にCSSが完全に置き換わらない不具合を修正
				img.src = 'image/sort-' + (this.descending ? "descending" : "ascending") + '.png';
				img.className = "sort-arrow " + (this.descending ? "descending" : "ascending");
			} else {
				// 2019/08/26 ソート状態の画像を動的に変更（コード追加）
				img.src = 'image/sort-blank.png';
				img.className = "sort-arrow";
			}
		}
	}

	// 2020/07/30 テーブルの奇数行・偶数行の背景色をJavaScriptへ移行
	var tableRows = this.element.rows;
	for ( var rowIndex = 0; rowIndex < tableRows.length; rowIndex++ ) {
		var cells = tableRows[rowIndex].cells;
		for( var colIndex = 0; colIndex < cells.length; colIndex++ ) {
			if (cells[colIndex].tagName != 'TH') {
				// クラス名が「TD」のみ背景色を設定する（ヘッダ行は「TH」）
				if (rowIndex % 2 == 0) {
					// 偶数行背景色セット
					cells[colIndex].style.backgroundColor
						= (this.evenColor) ? this.evenColor : '';
				} else {
					// 奇数行背景色セット
					cells[colIndex].style.backgroundColor
						= (this.oddColor) ? this.oddColor : '';
				}
			}
		}
	}

};

SortableTable.prototype.headerOnclick = function (e) {
	// find TD element
	var cell = e.target || e.srcElement;
	while (cell.nodeName != "TD" && cell.nodeName != "TH") { cell = cell.parentNode; }
	var row = cell.parentNode;
	while (row.nodeName != 'TR') { row = row.parentNode; }

	var row = row.rowIndex;
	var column = (SortableTable.msie || SortableTable.safari)
		? SortableTable.getCellIndex(cell)
		: cell.cellIndex;
	this.sort(row, column);
};

// IE returns wrong cellIndex when columns are hidden
SortableTable.getCellIndex = function (cell) {
	var cells = cell.parentNode.childNodes;
	for (var column = 0; cells[column] != cell && column < cells.length; column++)
		;
	return column;
};

SortableTable.prototype.getSortType = function (row, column) {
	if (! this.sortTypes[row]) { return "None"; }
	return this.sortTypes[row][column] || "String";
};

// only nRow, nColumn is required
// if bDescending is left out the old value is taken into account
// if sSortType is left out the sort type is found from the sortTypes array

SortableTable.prototype.sort = function (nRow, nColumn, bDescending, sSortType) {
	if (! this.tBody) { return; }
	if (sSortType == null) { sSortType = this.getSortType(nRow, nColumn); }

	// exit if None
	if (sSortType == 'None') { return; }

	if (bDescending == null) {
		this.descending = (this.sortRow == nRow && this.sortColumn == nColumn)
			? ! this.descending : this.defaultDescending;
	} else {
		this.descending = bDescending;
	}

	this.sortRow = nRow;
	this.sortColumn = nColumn;

	if (typeof this.onbeforesort == "function") {
		this.onbeforesort();
	}

	var f = this.getSortFunction(sSortType, nRow, nColumn);
	var a = this.getCache(sSortType, nRow, nColumn);
	var tBody = this.tBody;

	a.sort(f);

	if (this.descending) {
		a.reverse();
	}

	if (SortableTable.removeBeforeSort) {
		// remove from doc
		var nextSibling = tBody.nextSibling;
		var p = tBody.parentNode;
		p.removeChild(tBody);
	}

	// insert in the new order
	for (var i = 0; i < a.length; i++) {
		for (var j = 0; j < a[i].elements.length; j++) {
			tBody.appendChild(a[i].elements[j]);
		}
	}

	if (SortableTable.removeBeforeSort) {
		// insert into doc
		p.insertBefore(tBody, nextSibling);
	}

	this.updateHeaderArrows();

	this.destroyCache(a);

	if (typeof this.onsort == "function") {
		this.onsort();
	}
};

SortableTable.prototype.asyncSort = function (nRow, nColumn, bDescending, sSortType) {
	var oThis = this;
	this._asyncsort = function () {
		oThis.sort(nRow, nColumn, bDescending, sSortType);
	};
	window.setTimeout(this._asyncsort, 1);
};

SortableTable.prototype.getCache = function (sType, nRow, nColumn) {
	if (!this.tBody) { return []; }
	var rows = this.tBody.rows;
	var a = new Array;
	for (var i = 0; i < rows.length; i += this.step) {
		var r = [];
		for (var j = 0; j < this.step; j++) { r.push(rows[i + j]); }
		a.push({
			value:		this.getRowValue(r[nRow], sType, nColumn),
			elements:	r
		});
	};
	return a;
};

SortableTable.prototype.destroyCache = function (oArray) {
	var l = oArray.length;
	for (var i = 0; i < l; i++) {
		oArray[i].value = null;
		oArray[i].elements = null;
		oArray[i] = null;
	}
};

SortableTable.prototype.getRowValue = function (oRow, sType, nColumn) {
	// if we have defined a custom getRowValue use that
	if (this._sortTypeInfo[sType] && this._sortTypeInfo[sType].getRowValue)
		return this._sortTypeInfo[sType].getRowValue(oRow, nColumn);

	var s;
	var c = oRow.cells[nColumn];
	// add condition (&& c.firstChild.nodeType != 3) 2005-10-06 panda@arino.jp
	if (c.firstChild && c.firstChild.nodeType != 3) { c = c.firstChild; }
	if (typeof c.innerText != "undefined")
		s = c.innerText;
	else
		s = SortableTable.getInnerText(c);
	return this.getValueFromString(s, sType);
};

SortableTable.getInnerText = function (oNode) {
	var s = "";
	var cs = oNode.childNodes;
	var l = cs.length;
	for (var i = 0; i < l; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				s += SortableTable.getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				s += cs[i].nodeValue;
				break;
		}
	}
	return s;
};

SortableTable.prototype.getValueFromString = function (sText, sType) {
	if (this._sortTypeInfo[sType])
		return this._sortTypeInfo[sType].getValueFromString( sText );
	return sText;
	/*
	switch (sType) {
		case "Number":
			return Number(sText);
		case "CaseInsensitiveString":
			return sText.toUpperCase();
		case "Date":
			var parts = sText.split("-");
			var d = new Date(0);
			d.setFullYear(parts[0]);
			d.setDate(parts[2]);
			d.setMonth(parts[1] - 1);
			return d.valueOf();
	}
	return sText;
	*/
	};

SortableTable.prototype.getSortFunction = function (sType, nRow, nColumn) {
	if (this._sortTypeInfo[sType])
		return this._sortTypeInfo[sType].compare;
	return SortableTable.basicCompare;
};

SortableTable.prototype.destroy = function () {
	this.uninitHeader();
	var win = this.document.parentWindow;
	if (win && typeof win.detachEvent != "undefined") {	// only IE needs this
		win.detachEvent("onunload", this._onunload);
	}
	this._onunload = null;
	this.element = null;
	this.tHead = null;
	this.step = null;
	this.tBody = null;
	this.document = null;
	this._headerOnclick = null;
	this.sortTypes = null;
	this._asyncsort = null;
	this.onsort = null;
};

// Adds a sort type to all instance of SortableTable
// sType : String - the identifier of the sort type
// fGetValueFromString : function ( s : string ) : T - A function that takes a
//    string and casts it to a desired format. If left out the string is just
//    returned
// fCompareFunction : function ( n1 : T, n2 : T ) : Number - A normal JS sort
//    compare function. Takes two values and compares them. If left out less than,
//    <, compare is used
// fGetRowValue : function( oRow : HTMLTRElement, nColumn : int ) : T - A function
//    that takes the row and the column index and returns the value used to compare.
//    If left out then the innerText is first taken for the cell and then the
//    fGetValueFromString is used to convert that string the desired value and type

SortableTable.prototype.addSortType = function (sType, fGetValueFromString, fCompareFunction, fGetRowValue) {
	this._sortTypeInfo[sType] = {
		type:				sType,
		getValueFromString:	fGetValueFromString || SortableTable.idFunction,
		compare:			fCompareFunction || SortableTable.basicCompare,
		getRowValue:		fGetRowValue
	};
};

// this removes the sort type from all instances of SortableTable
SortableTable.prototype.removeSortType = function (sType) {
	delete this._sortTypeInfo[sType];
};

SortableTable.basicCompare = function compare(n1, n2) {
	if (n1.value < n2.value)
		return -1;
	if (n2.value < n1.value)
		return 1;
	return 0;
};

SortableTable.idFunction = function (x) {
	return x;
};

SortableTable.toUpperCase = function (s) {
	return s.toUpperCase();
};

SortableTable.toNumber = function (s) {
	// 2019/08/27 カンマを除去して数値に変換する
	// 「10個」「3.3cm」等の文字列が入っていても数値（実数）変換可能
	var val = parseFloat(s.replace(/,/g, ''));
	return val;
}

SortableTable.toDate = function (s) {
/* 
	var parts = s.split("-");
	var d = new Date(0);
	d.setFullYear(parts[0]);
	d.setDate(parts[2]);
	d.setMonth(parts[1] - 1);
	return d.valueOf();
*/
	// 2019/08/29 様々な日付形式と時刻も対応するため作り替え
	// yyyy-mm-dd だとタイムゾーンでズレるので yyyy/mm/dd に変換
	// 通常は yyyy/mm/dd hh:mm:ss 形式だが、それ以外のケースにも対応するため
	// 一端 Date.parse を通して日付型にする
	var conv = s.replace(/-/g, '/');
	var val = new Date(Date.parse(conv));
	return val;
};


// add sort types
//SortableTable.prototype.addSortType("Number", Number);
SortableTable.prototype.addSortType("Number", SortableTable.toNumber);
SortableTable.prototype.addSortType("CaseInsensitiveString", SortableTable.toUpperCase);
SortableTable.prototype.addSortType("Date", SortableTable.toDate);
SortableTable.prototype.addSortType("String");
// None is a special case