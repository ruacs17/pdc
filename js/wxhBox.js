function width(){ 
var e = window, a = 'inner';
	if ( !( 'innerWidth' in window ) ){
		a = 'client';
		e = document.documentElement || document.body;
}
return e[ a+'Width' ] - 300;
}

function height(){
var e = window, a = 'inner';
	if ( !( 'innerWidth' in window ) ){
		a = 'client';
		e = document.documentElement || document.body;
	}	
	return e[ a+'Height' ] - 220;
}