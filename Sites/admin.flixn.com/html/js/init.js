function fillScreen() {
	winH = findHeight();
	hN=winH-126;
	if( typeof( window.innerWidth ) != 'number' ) {
		document.getElementById('center').style.height=hN+'px';
		document.getElementById('center').style.height=hN+'px';
	}
	document.getElementById('center').style.minHeight=hN+'px';
	document.getElementById('center').style.minHeight=hN+'px';
}

function findHeight(){
	var nH = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		nH = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		nH = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		nH = document.body.clientHeight;
	}
	return nH;
}

window.onload = function()
{
	if(action == "settings" && navigator.appName == "Microsoft Internet Explorer")
	{
		//nothing
	}
	else
	{
		fillScreen();
	}
}