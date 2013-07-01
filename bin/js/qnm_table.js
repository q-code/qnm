/*
Summary
-------
Theses functions add/remove rows (called "sub-rows") under a specific row of a table.
To work with this function, the table <table> and each row <tr> must have unique id.
If your page contains several tables, be sure that the <tr> id are unique through all tables.

Technical tips
--------------
The created sub-rows <tr> will have id like id+r0. Example, sub-rows added under the row 't1a1' will have id: 't1a1r0', 't1a1r1', ...
These id are used to be able to remove the sub-rows (ex: removing sub-rows under the row 't1a1' will remove all rows having id like 't1a1r*')
The created  cells <td> will have id like id+r0+c0. Example, cells of the sub-row 't1a1r0' will have id: 't1a1r0c0', 't1a1r0c1', ...
The sub-rows <tr> include the class 'popuprow' and the cells <td> include the class 'popupcell'.
The cells are filled with the array values of [rows] (and the blank character &nbsp; if no data).

qtPopupRows(): Returns true (null in case of error). When creating sub-rows, you must specify the at least one row dataset [rows].
qtPopoutRows(): Returns true (null in case of error). When removing sub-rows under one row, all these sub-rows are removed automatically. If other sub-rows (belonging to other row) were opened, they remain visible.
*/

function qtPopupRows(tableid,rowid,rows,senderid)
{
	var doc = document;
	// rows is an array of array values cells
	if (doc.getElementById(tableid)==null) return null;
	if (doc.getElementById(rowid)==null) return null;
	// Count the number of rows in [rows]
	var intRows = rows.length;
	if ( intRows==0 ) return null;
	// Find the position next to the row [rowid] in table [tableid]
	var intPosition = qtGetRowPosition(tableid,rowid)+1;
	if ( intPosition==0 ) return null;
	// Count the number of columns in the row [rowid]
	var domCells = doc.getElementById(rowid).cells;
	var intCells = doc.getElementById(rowid).cells.length;
	// Insert [rows] rows
	var i=0;
	var j=0;
	for (i=0;i<intRows;i++)
	{
		var r=doc.getElementById(tableid).insertRow(intPosition);
		r.className = 'popuprow';
		r.id = rowid+'r'+i;
		// Insert [intColumns] columns
		for (j=0;j<intCells;j++)
		{
		var c = r.insertCell(j);
		c.id = rowid+'r'+i+'c'+j;
		c.className = 'popupcell '+domCells[j].className;
		c.innerHTML = (rows[i][j]==null ? "&nbsp;" : rows[i][j]);
		}
	}
	// Unset sender function
	if (doc.getElementById(senderid)!=null)
	{
	doc.getElementById(senderid).innerHTML='<img src="admin/ico_up.gif" alt="[-]"/>';
	doc.getElementById(senderid).className='popout_ctrl';
	}
	return true;
}
function qtPopoutRows(tableid,rowid,senderid)
{
	var doc = document;
	var sFindId = rowid+'r';
	var rows = doc.getElementById(tableid).rows;
	var i = rows.length-1;
	do { if ( rows[i] && rows[i].id.match(sFindId) ) doc.getElementById(tableid).deleteRow(i); } while(i--);
	// Unset sender function
	if ( doc.getElementById(senderid)!=null)
	{
	doc.getElementById(senderid).innerHTML='<img src="admin/ico_dw.gif" alt="[+]"/>';
	doc.getElementById(senderid).className='popup_ctrl';
	}
	return true;
}
function qtGetRowPosition(tableid,rowid)
{
	var doc=document.getElementById(tableid); if ( !doc ) return -1;
	var rows=doc.rows, i=rows.length-1; if ( i<0 ) return -1;
	do { if (rows[i].id==rowid) return i; } while(i--);
	return -1;
}
function qtSplitDataString(data,shift)
{
	var src = data.split("||");
	var a = [], b = [];
	var i = src.length-1;
	do { b = src[i].split("|"); a[i] = b; } while( i-- );
	return a;
}
function qtCheckboxAll(checkboxid,name,useHighlight)
{
	var doc = document;
	var checkbox = doc.getElementById(checkboxid); if ( !checkbox ) return;
	var checkboxes = doc.getElementsByName(name);
	var i = checkboxes.length-1; if ( i<0 ) return;
	do
	{
	checkboxes[i].checked=checkbox.checked;
	if (useHighlight) qtHighlight("tr_"+checkboxes[i].id,checkbox.checked);
	}
	while(i--);
}
function qtCheckboxOne(name,id)
{
	// Check/uncheck header checkbox when all/none boxes are checked
	// This function is not mandatory
	var doc = document; if ( !doc.getElementById(id) ) return;
	var checkboxes = doc.getElementsByName(name); if ( checkboxes.length<1 ) return;
	var n = 0, i = checkboxes.length-1; if ( i<0 ) return;
	do { if ( checkboxes[i].checked ) n++; } while(i--);
	doc.getElementById(id).checked = ( n==checkboxes.length );
}
function qtCheckboxToggle(id)
{
	var doc = document.getElementById(id); if ( !doc ) return;
	doc.click();
}
function qtHighlight(id,bHighlighted)
{
	var doc = document.getElementById(id); if ( !doc ) return;
	doc.className = doc.className.replace(" checked","");
	if ( bHighlighted ) doc.className += " checked";
}