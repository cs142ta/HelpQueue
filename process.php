<?php
/* 
URL: https://css-tricks.com/jquery-php-chat/
Created by: Kenrick Beckett
Name: Chat Engine
Extensively edited by Brad Spendlove, BYU
*/
	$function = $_POST['function'];
	$log = array();
	$log['done'] = true;
	switch($function) {
	case('getState'):
		$name = htmlentities(strip_tags($_POST['name']));
		if(file_exists('chats/'.$name.'chat.txt')){
			$lines = file('chats/'.$name.'chat.txt');
		}
		$log['state'] = count($lines); 
	break;	
		
	case('update'):
		$log['pop'] = false;
		$name = htmlentities(strip_tags($_POST['name']));
		$state = $_POST['state'];
		if(file_exists('chats/'.$name.'chat.txt')){
			$lines = file('chats/'.$name.'chat.txt');
		}
		$count =  count($lines);
		if($state == $count){
			$log['state'] = $state;
			$log['text'] = false;
			
		}
		else{
			$text= array();
			$log['state'] = count($lines);
			foreach ($lines as $line_num => $line) {
				if($line_num >= $state){
					if(0 === strpos($line, '!')){
						$log['pop']=true;
					}
					else{
						$text[] =  $line = str_replace("\n", "", $line);
					}
				}
			}
			$log['text'] = $text; 
		}
		
	break;
		
	case('send'):
		$name = htmlentities(strip_tags($_POST['name']));
		$nickname = htmlentities(strip_tags($_POST['nickname']));
		$message = htmlentities(strip_tags($_POST['message']));
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if(($message) != "\n"){
			if(preg_match($reg_exUrl, $message, $url)) {
				$message = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a>', $message);
			}
			fwrite(fopen('chats/'.$name.'chat.txt', 'a'), "<span>". $nickname . "</span>" . $message = str_replace("\n", " ", $message) . "\n"); 
		}
	break;
	
	case('leave'):
		$name = htmlentities(strip_tags($_POST['name']));
		$nickname = htmlentities(strip_tags($_POST['nickname']));
		fwrite(fopen('chats/'.$name.'chat.txt', 'a'), "<i>". $nickname . " has left the chat</i>\n"); 
	break;
	
	case('enter'):
		$name = htmlentities(strip_tags($_POST['name']));
		$nickname = htmlentities(strip_tags($_POST['nickname']));
		fwrite(fopen('chats/'.$name.'chat.txt', 'a'), "<i>". $nickname . " has entered</i>\n"); 
	break;
	
	case('notify'):
		$name = htmlentities(strip_tags($_POST['name']));
		fwrite(fopen('chats/'.$name.'chat.txt', 'a'), "!!requesting student chat\n");
	break;
		
	}
	echo json_encode($log);
?>
