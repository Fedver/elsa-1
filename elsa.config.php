<?php

    session_start();

    // Page header.
    $header_index   = "<header id='header' class='alt'>";
    $header_page    = "<header id='header'>";
    $header_general = " <h1><a href='index.php'>ELSA</a></h1>
					     <nav id='nav'>
						    <ul>
							    <li><a href='index.php'>Home</a></li>
							    <li>
								    <a href='#' class='icon fa-angle-down'>API</a>
								    <ul>
									    <li><a href='generic.html'>How to get API Key</a></li>
									    <li><a href='contact.html'>Documentation</a></li>
								    </ul>
							    </li>";
    if ($_SESSION['user_id'] && $_SESSION['user_email'])
        $header_general .= "    <li>
								    <a href='#' class='icon fa-angle-down'>".$_SESSION['user_email']."</a>
								    <ul>
                                        <li><a href='contact.html'>User Profile</a></li>
									    <li><a href='generic.html'>Your API Key</a></li>
										<li><a href='#' onclick='logout();'>Logout</a></li>
								    </ul>
							    </li>";
    else
        $header_general .= "    <li><a href='login.php'>Login</a></li>
								<li><a href='signup.php'>Sign Up</a></li>";

    $header_general .= "    </ul>
					     </nav>
				        </header>";

    // CSS and JavaScript SRCs.
    $src_files      = " <link rel='stylesheet' href='css/main.css' />
		                <!--[if lte IE 8]><link rel='stylesheet' href='css/ie8.css' /><![endif]-->
			            <script src='js/jquery.min.js'></script>
			            <script src='js/jquery.dropotron.min.js'></script>
			            <script src='js/jquery.scrollgress.min.js'></script>
			            <script src='js/skel.min.js'></script>
			            <script src='js/util.js'></script>
			            <!--[if lte IE 8]><script src='js/ie/respond.min.js'></script><![endif]-->
                        <!--[if lte IE 8]><script src='js/ie/html5shiv.js'></script><![endif]-->
			            <script src='js/main.js'></script>
						<script src='js/spin.js'></script>
						<script src='js/jquery.spin.js'></script>
                        <script src='js/account_manage.js'></script>";

    // Page footer.
    $footer         = " <footer id='footer'>
					     <ul class='icons'>
						    <li><a href='#' class='icon fa-github'><span class='label'>Github</span></a></li>
					     </ul>
					     <ul class='copyright'>
						    <li>&copy; 2016. </li><li>Design: Federico Orlandi</li>
					     </ul>
				        </footer>";

?>