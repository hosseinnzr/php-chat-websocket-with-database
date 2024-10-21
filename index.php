<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign/signin.php");
    exit();
}
?>

<?php 
$colors = array('#007AFF','#FF7000','#FF7000','#15E25F','#CFC700','#CFC700','#CF1100','#CF00BE','#F00');
$color_pick = array_rand($colors);
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
.chat-wrapper {
	font: bold 11px/normal 'lucida grande', tahoma, verdana, arial, sans-serif;
    background: #00a6bb;
    padding: 20px;
    margin: 20px auto;
    box-shadow: 2px 2px 2px 0px #00000017;
	max-width:700px;
	min-width:500px;
}
#message-box {
    width: 97%;
    display: inline-block;
    height: 300px;
    background: #fff;
    box-shadow: inset 0px 0px 2px #00000017;
    overflow: auto;
    padding: 10px;
}
.user-panel{
    margin-top: 10px;
}
input[type=text]{
    border: none;
    padding: 5px 5px;
    box-shadow: 2px 2px 2px #0000001c;
}
input[type=text]#name{
    width:20%;
}
input[type=text]#message{
    width:80%;
}
button#send-message {
    border: none;
    padding: 5px 15px;
    background: #11e0fb;
    box-shadow: 2px 2px 2px #0000001c;
}
</style>
</head>
<body>

    <p>This is your dashboard.</p>
    <a href="logout.php">Log Out</a>

<div class="chat-wrapper">
<h3>Welcome, <?php echo $_SESSION['username']; ?></h3>
<div id="message-box"></div>
<div class="user-panel">
<input style="display: none;" type="text" name="name" id="name" placeholder="Your Name" maxlength="15" value="<?php echo $_SESSION['username']?>"/>
<input type="text" name="message" id="message" placeholder="Type your message here..." maxlength="100" />
<button id="send-message">Send</button>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script language="javascript" type="text/javascript">  
	//create a new WebSocket object.
	var msgBox = $('#message-box');
	var wsUri = "ws://localhost:9000/demo/server.php"; 	
	websocket = new WebSocket(wsUri); 
	
	websocket.onopen = function(ev) { // connection is open 
		msgBox.append('<div class="system_msg" style="color:#bbbbbb">Welcome to my "Demo WebSocket Chat box"!</div>'); //notify user
	}
	// Message received from server
	websocket.onmessage = function(ev) {
		var response 		= JSON.parse(ev.data); //PHP sends Json data
		
		var res_type 		= response.type; //message type
		var user_message 	= response.message; //message text
		var user_name 		= response.name; //user name
		var user_color 		= response.color; //color

		var login_username = "<?php echo $_SESSION['username']; ?>";

		if (res_type === 'previous_messages') {
        	// Display previous messages
			response.messages.forEach(function(message) {
				if( message.username == login_username){
					msgBox.append('<div style="direction: rtl"><span class="user_name" style="color:#1160d4;">' + message.username + '</span><br> <span class="user_message">' + message.message + '</span><br></div>');
				}else{
					msgBox.append('<div"><span class="user_name" style="color:#1160d4;">' + message.username + '</span><br><span class="user_message">' + message.message + '</span><br></div>');
				}
			});
		} else if (res_type === 'usermsg') {
			// Display new message
			if( message.username == login_username){
				msgBox.append('<div style="direction: rtl;"><span class="user_name" style="color:#1160d4;">' + message.username + '</span><br> <span class="user_message">' + message.message + '</span><br></div>');
			}else{
				msgBox.append('<div><span class="user_name" style="color:#1160d4;">' + user_name + '</span><br><span class="user_message">' + user_message + '</span></div>');
			}
			
		} else if (res_type === 'system') {
			msgBox.append('<div style="color:#bbbbbb; text-align:center">' + login_username + ' ' + user_message + '</div>');
		}

		msgBox[0].scrollTop = msgBox[0].scrollHeight; //scroll message 
	};
	
	websocket.onerror	= function(ev){ msgBox.append('<div class="system_error">Error Occurred - ' + ev.data + '</div>'); }; 
	websocket.onclose 	= function(ev){ msgBox.append('<div class="system_msg">Connection Closed</div>'); }; 

	//Message send button
	$('#send-message').click(function(){
		send_message();
	});
	
	//User hits enter key 
	$( "#message" ).on( "keydown", function( event ) {
	  if(event.which==13){
		  send_message();
	  }
	});
	
	//Send message
	function send_message(){
		var message_input = $('#message'); //user message text
		var name_input = $('#name'); //user name
		
		if(message_input.val() == ""){ //empty name?
			alert("Enter your Name please!");
			return;
		}
		if(message_input.val() == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}

		//prepare json data
		var msg = {
			message: message_input.val(),
			name: name_input.val(),
			color : '<?php echo $colors[$color_pick]; ?>'
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));	
		message_input.val(''); //reset message input
	}
</script>
</body>
</html>
