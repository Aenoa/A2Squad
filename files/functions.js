function _checkAll(field)
{
for (i = 0; i < field.length; i++)
	field[i].checked = true ;
}

function _uncheckAll(field)
{
for (i = 0; i < field.length; i++)
	field[i].checked = false ;
}

function CheckAll(Act, Acv)
{
var IsCheck = Act=="Check all"?true:false;
var oColl = document.getElementsByName(Acv);
for (i=0;i<oColl.length;i++)
 oColl.item(i).checked = IsCheck;
return IsCheck?"Uncheck All":"Check all";
}