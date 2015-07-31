//Short Script Library
							/*************************
							 * Cursor Focus on Input.*
							 *************************/
function PMA_focusInput(param1,param2)
{	
		var input_1 = document.getElementById(param1);
		var input_2 = document.getElementById(param2);
    if (input_1.value == '') {
        input_1.focus();
    } else {
        input_2.focus();
    }
}
							/***********************
							 * Opens a work window.*
							 ***********************/

function openMise(param1,param2) {
    window.open("./mise.php?cid=" + param1 +"&mi="+param2, "popout", "width=400,height=200,status=yes");
}
function openPicture(param1,param2) {
    window.open("./flex/change_picture/change.php?cid="+ param1 + "&uid=" +param2, "popout", "width=495,height=208,status=1");
}
function openAddDetail(param1,param2) {
    window.open("./flex/add_detail/add_detail.php?cid="+ param1 + "&empid=" +param2, "popout", "width=511,height=436,status=1");
}
function refreshpage() {
    goTo('navigation.php?server=' + encodeURIComponent(server) +
        '&db=' + encodeURIComponent(db)  +
        '&table=' + encodeURIComponent(table) +
        '&lang=' + encodeURIComponent(lang) +
        '&collation_connection=' + encodeURIComponent(collation_connection)
        );
}
							/***********************
							 * Returns to the page.*
							 ***********************/
function returnWindow() {
    window.close();
}
							/***********************
							 *Link Click Challenge.*
							 ***********************/
function confirmLink(theLink, theSqlQuery)
{
	var confirmMsg  = 'Est ce que vous voulez vraiment ';
	if (confirmMsg == '' || typeof(window.opera) != 'undefined') {
		return true;
	}
	var is_confirmed = confirm(confirmMsg + ' :\n' + theSqlQuery);
	if (is_confirmed) {
		if ( typeof(theLink.href) != 'undefined' ) {
			theLink.href += '';
		} else if ( typeof(theLink.form) != 'undefined' ) {
			theLink.form.action += '';
		}
	}
	return is_confirmed;
}
							/***********************
							 *	Password Strength. *
							 ***********************/
function passwordStrength(password,username) {
    var shortPass = 1, badPass = 2, goodPass = 3, strongPass = 4;

	//password < 4
    if (password.length < 4 ) { return shortPass };

    //password == username
    if (password.toLowerCase()==username.toLowerCase()) return badPass;

	var symbolSize = 0;
	if (password.match(/[0-9]/)) symbolSize +=10;
	if (password.match(/[a-z]/)) symbolSize +=26;
	if (password.match(/[A-Z]/)) symbolSize +=26;
	if (password.match(/[^a-zA-Z0-9]/)) symbolSize +=31;

	var natLog = Math.log( Math.pow(symbolSize,password.length) );
	var score = natLog / Math.LN2;
	if (score < 40 )  return badPass
	if (score < 56 )  return goodPass
    return strongPass;
}