<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Chat</title>

<link rel="stylesheet" href="static/css/chatstyle.css" type="text/css" />

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="static/js/chat.js"></script>
<script type="text/javascript">
	
	// ask user for name with popup prompt    
	var nick = prompt("Enter your chat name:", "Guest");
	
	// default name is 'Guest'
	if (!nick || nick === ' ') {
		nick = "Guest";
	}
	
	// strip tags
	nick = nick.replace(/(<([^>]+)>)/ig,"");
	
	// ask user for name with popup prompt    
	var name = prompt("Enter who you want to chat with:", "Bob");
	
	// default name is 'Guest'
	if (!name || name === ' ') {
		name = "Bob";
	}
	
	// strip tags
	name = name.replace(/(<([^>]+)>)/ig,"");
	
	var chat = new Chat(name);
	
	// display name on page
	$("#name-area").html("You are: <span>" + nick + "</span>");
	
	$(function() {
	
		chat.getState(); 
		
		// watch textarea for key presses
		$("#sendie").keydown(function(event) {  
		
		var key = event.which;  
	
		//all keys including return.  
		if (key >= 33) {
			
			var maxLength = $(this).attr("maxlength");  
			var length = this.value.length;  
			
			// don't allow new content if length is maxed out
			if (length >= maxLength) {  
				event.preventDefault();  
			}  
			}  
		});
		// watch textarea for release of key press
		$('#sendie').keyup(function(e) {	
							
			if (e.keyCode == 13) { 
			
			var text = $(this).val();
				var maxLength = $(this).attr("maxlength");  
			var length = text.length; 
			
			// send 
			if (length <= maxLength + 1) { 
			
				chat.send(text, nick);	
				$(this).val("");
				
			} else {
			
					$(this).val(text.substring(0, maxLength));
					
				}	
				
				
			}
		});
		
		setInterval('chat.update()', 1000);
		
	});
</script>

</head>

<body>

<div id="page-wrap">

	<h2>jQuery/PHP Chat</h2>
	
	<p id="name-area"></p>
	
	<div id="chat-wrap"><div id="chat-area"></div></div>
	
	<form id="send-message-area">
		<p>Your message: </p>
		<textarea id="sendie" maxlength = '100' ></textarea>
	</form>
	
	<button onclick="chat.clear()">CLEAR</button>

</div>

</body>

</html>
