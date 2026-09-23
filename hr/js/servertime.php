<?php date_default_timezone_set("Asia/Manila");?>
var currenttime = '<?php echo date("l F d, Y H:i:s", time())?>'
var currentday = '<?php echo date("N")?>'
var dayarray=new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday")
var montharray=new Array("January","February","March","April","May","June","July","August","September","October","November","December")
var serverdate=new Date(currenttime)

function padlength(what){
var output=(what.toString().length==1)? "0"+what : what
return output
}
function twelvehr(hr){
	if(hr == "00")
		return "12";
	else if(hr > 12)
		return hr - 12;
	else
		return hr;
}
function ampm(hr){
	if(hr >= 12)
		return "pm";
	else
		return "am";
}


function displaytime(){
serverdate.setSeconds(serverdate.getSeconds()+1)
var datestring=dayarray[serverdate.getDay()]+" - "+montharray[serverdate.getMonth()]+" "+padlength(serverdate.getDate())+", "+serverdate.getFullYear()
var timestring=padlength(twelvehr(serverdate.getHours()))+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())+" "+ampm(serverdate.getHours())
document.getElementById("servertime").innerHTML=datestring+" "+"<b style='font-size:14;'>"+timestring+"</b>"
}

function showservertime(){
setInterval("displaytime()", 1000)
}

