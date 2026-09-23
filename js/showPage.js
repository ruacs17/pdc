function showThis(aID,page,TeqtLe,rf='2'){

    var wd=width()-18;

    var hh=height()-12;
    if(rf==2)
    var myUrl=page+"&height="+hh+"&width="+wd+"&keepThis=true&TB_iframe=true&refresh=true";
	else
	var myUrl=page+"&height="+hh+"&width="+wd+"&keepThis=true&TB_iframe=true";

    document.getElementById(aID).href=myUrl;    

    document.getElementById(aID).title=TeqtLe;      

}