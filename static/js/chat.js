/* 
URL: https://css-tricks.com/jquery-php-chat/
Created by: Kenrick Beckett
Name: Chat Engine
Extensively edited by Brad Spendlove, BYU
*/
function Chat (n) {
	console.log("created chat with name: "+n);
	this.instance = false;
	this.state = 0;
	this.name = n;
	this.popfunc = null;
	this.update = function (){
		if(!this.instance && this.name && typeof(this.state) != 'undefined') {
			this.instance = true;
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'update',
					'state': that.state,
					'name': that.name
				},
				dataType: "json",
				success: function(data){
					if(data.text){
						for (var i = 0; i < data.text.length; i++) {
							$('#chat-area').append($("<p>"+ data.text[i] +"</p>"));
						}
						if (data.text.length > 0){
							document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
						}
					}
					if (that.popfunc && data.pop) that.popfunc();
					that.instance = false;
					that.state = data.state;
				},
			});
		}
		else {
			setTimeout(this.update, 1500);
		}
	}
	this.getState = function (){
		if(!this.instance && this.name){
			this.instance = true;
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'getState',
					'name': that.name
				},
				dataType: "json",
				
				success: function(data){
					that.state = data.state;
					that.instance = false;
				},
			});
		}	
	}
	this.send = function (message, nickname){
		if(this.name){
			this.update();
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'send',
					'message': message,
					'name': that.name,
					'nickname': nickname,
				},
				dataType: "json",
				success: function(data){
					that.update();
				},
			});
		}
	}
	this.clear = function (){
		$('#chat-area').empty();
		this.getState();
	}
	this.close = function (n){
		if(this.name){
			this.update();
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'leave',
					'nickname': n,
					'name': that.name,
				},
				dataType: "json",
				success: function(data){
					that.update();
				},
			});
		}
	}
	this.enter = function (n){
		if(this.name){
			this.update();
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'enter',
					'nickname': n,
					'name': that.name,
				},
				dataType: "json",
				success: function(data){
					that.update();
				},
			});
		}
	}
	this.notify = function (n){
		if(this.name){
			this.update();
			var that = this;
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {  
					'function': 'notify',
					'name': that.name,
				},
				dataType: "json",
				success: function(data){
					that.update();
				},
			});
		}
	}
	this.clear();
}

//send the message

