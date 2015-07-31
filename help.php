<?php
session_start();
	include "class_lib.php";
	include "template.php";
	$template= new template('html');
	//=========		
	if(!$_POST['login']&&!$_SESSION['islogin']){
		$template->set_filenames(array(
			'login' => 'login.html')
		);
		$template->pparse('login');
	}
	//=========	
	else if($_POST['login']){
		include "classes/login.php";
		$login=new login();
		$test=$login->logger($_POST['log'],$_POST['pass']);
		if(!$test){
			$template->set_filenames(array(
				'login' => 'login.html')
			);
			$template->assign_block_vars('switch_login_fails',	array());
			$template->pparse('login');
			$template->set_filenames(array(
				'footer' => 'footer.html')
			);
			$template->pparse('footer');
			exit();
		}
		if($test){
			$_SESSION['islogin']=$login->islogin;
			$_SESSION['access']=$login->access;
			$_SESSION['emp']=$login->EMP;
			$_SESSION['zone']=$login->zone;		
			$session=new session_info();
			$page=new layout();
			$page->call_info($_GET['pageload'],$session->access);
		}
	}
	//=========
	else if($_SESSION['islogin']==1){
		$session=new session_info();
		$page=new layout();
		$page->call_info($_GET['pageload'],$session->access);
	}
	else{
		die("<h6>Logon Hacking Atempt</h6>");
	}
	if($session->islogin==1){
		$template->set_filenames(array(
			'header' => 'header.html')
		);
		$template->assign_vars(array(
			'TITLE' => $page->display[TITLE],)
		);
		foreach($page->tabledata as $category=>$a){
			if($category){
				$template->assign_block_vars('menu', array(
					'CATEGORY' => $category,
				));
				foreach($a as $b=>$output){
					$template->assign_block_vars('menu.sub',	array(
						'CURRENT'=> $_SERVER['PHP_SELF'],
						'PAGE_NUM' => $output[PAGE],
						'PAGE_NAME' => $output[NAME],)
					);
				}
			}	
		}
			$template->pparse('header');
	//=========
			$template->set_filenames(array(
				'body' => 'help/'.$_GET['topic'].'.html',)
			);
						$template->pparse('body');

	}
	$template->set_filenames(array(
			'footer' => 'footer.html')
		);

	$template->pparse('footer');
?>