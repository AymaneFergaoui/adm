(function ($) {

	$(document).ready(function() {

					

		/* Manage active class for second level in sidebar for activities based on path */

		var fullpath = location.href;

		

		// remove last slash

		if(fullpath.charAt(fullpath.length-1) == "/"){ fullpath = fullpath.substr(0, fullpath.length - 1);}

		

		console.log(fullpath);

		

		if(fullpath.indexOf("activites-des-amis") >= 0){

			$("aside ul.nav li a.active").removeClass("active");

			$("aside ul.nav li a.active-trail").removeClass("active-trail");

			

			if(fullpath.indexOf("field_interests_tid=8") >= 0 || fullpath.indexOf("field_interests_target_id=8") >= 0){ // VUE Midi de l'art

				$("aside ul.nav li:eq(0) a").addClass("active");

			}

			else if(fullpath.indexOf("field_interests_tid=13") >= 0 || fullpath.indexOf("field_interests_target_id=13") >= 0){ // VUE Visite luxembourg

				$("aside ul.nav li:eq(1) a").addClass("active");

			}					

			else if(fullpath.indexOf("field_interests_tid=14") >= 0 || fullpath.indexOf("field_interests_target_id=14") >= 0){ // VUE Visite etranger

				$("aside ul.nav li:eq(2) a").addClass("active");

			}	

			else if(fullpath.indexOf("activites-des-amis/") >= 0){ // PAGE Activé des Amis

				//console.log("PAGE Activé des Amis [fullpath : "+fullpath+"]");  // Rien actif

			}

			else{ // VUE TOUTES les activités content type

				$("aside ul.nav li:eq(3) a").addClass("active");

			}	

		}

		else if(fullpath.indexOf("actualites-des-amis/") >= 0 || fullpath.indexOf("museum-news/") >= 0){  // PAGE Actualités ou Museum News ( à l'interieur de la vue du même nom)

			$("aside ul.nav li a.active").removeClass("active");

			$("aside ul.nav li a.active-trail").removeClass("active-trail");

		}

		else if(fullpath.indexOf("devenir-membre") >= 0){

			$("aside ul.nav li a.active").removeClass("active");

			$("aside ul.nav li a.active-trail").removeClass("active-trail");

			$("aside ul.nav li:eq(0) a").addClass("active");

		}

		else if(fullpath.indexOf("devenir-donateur") >= 0){

			$("aside ul.nav li a.active").removeClass("active");

			$("aside ul.nav li a.active-trail").removeClass("active-trail");

			$("aside ul.nav li:eq(1) a").addClass("active");

		}

		

		/* Header Crossed item */

		$('.header-crossed').click(function(){$('.header-crossed').toggleClass('active');});

		

		if(!$("body").hasClass("logged-in")){

			$("#btn-devenir-membre").html("<button class='btn btn-primary'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Devenir membre </button>").click(function(){

				window.location.href='./user/register'; // Si problème utiliser le sitepath.

			});

		}

		else{

			$("#btn-devenir-membre").remove();

		}

		

		

		// footer lock

		FooterFixedOnResize()

		$( window ).resize(function() {

		  FooterFixedOnResize()

		});	

	});



	/* Calcul Size on Resize */

	function FooterFixedOnResize() {

		//Note : les 88 correspondent au padding-top sur main-container de 90px - les 2 bordures de 1px

		var minh = parseInt($(window).height() - $('#toolbar').height() - $('header').height() - $('footer').height() + 88);

		//console.log($(window).height()+" - "+$('#toolbar').height()+" - "+$('header').height()+" - "+$('footer').height()+" + 88 = "+minh+" <> "+$('.main-container').height());

		if($('.main-container').height() < minh){

			$('.main-container').css('min-height', minh);

		}

	}

	

	/* Recuperation paramètre Url */

	function urlParam(url,name){

		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);

		if (results==null){

		   return null;

		}

		else{

		   return results[1] || 0;

		}

	}

	

	/* Calendar click */

	if ($('.atcb-link')[0]){

		$(".calendar-btn").click(function() {

		   $('html, body').animate({

				scrollTop: ($('.atcb-link').first().offset().top)

			},500);

			$(".atcb-list").css("visibility","visible");

			$(".atcb-list").delay(5000).queue( function(next){ 

				$(".atcb-list").css("visibility",""); 

				next(); 

			});

		});

	} else {

		$(".calendar-btn").hide();

	}

    

	

	

	/* Analytics */

	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){

		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),

		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)

		  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-56293709-6', 'auto');

		  ga('send', 'pageview');  

})(jQuery);