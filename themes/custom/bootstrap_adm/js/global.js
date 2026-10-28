/**

 * @file

 * Global utilities.

 *

 */

(function ($, Drupal) {



  'use strict';



  var initialized;

  var media_breakpoint = 991;



  function init() {

    if (!initialized) {

      initialized = true;

	    

	  $(document).ready(function() {


			$(function(){
				setInterval(oneSecondFunction, 1000);
			});
				
			function oneSecondFunction() {
				if (jQuery(window).width() < 767) {
					jQuery('.fc-today a').click();
					jQuery('.fc-more').click();
				}
			}


			/* Return to top */

			$('#return-to-top').click(function() {$('body,html').animate({scrollTop : 0}, 500);});



			/* Anchor Smooth Scroll */

			document.querySelectorAll('a[href^="#"]').forEach(anchor => {

				anchor.addEventListener('click', function (e) {

					e.preventDefault();

					if(this.getAttribute('href') != "#"){

						document.querySelector(this.getAttribute('href')).scrollIntoView({

							behavior: 'smooth'

						});

					}

				});

			});
			

	  });

	  

	  /* Scroll Return to top  */

	  $(window).scroll(function() {

			if ($(this).scrollTop() >= 250 && document.body.scrollHeight > 1000) {

				$('#return-to-top').fadeIn(200);} else {$('#return-to-top').fadeOut(200);

			}

	  });	

	  

	  /* On Resize */

	  $(window).on('resize', function(){

		    var w_width = $(window).width();

			var d_width = $(document).width();

			if(w_width >= media_breakpoint && d_width >= media_breakpoint){if (!$("body").hasClass("parallax-enable")) {$("body").addClass("parallax-enable");}}

			else{if ($("body").hasClass("parallax-enable")) {$("body").removeClass("parallax-enable");}}

	  });



    }

  }



  

  Drupal.behaviors.bootstrap_barrio_subtheme = {

    attach: function (context, settings) {

		init()

	}

  };


	/* Translate specific UI strings via Drupal.t */
	if (typeof Drupal !== 'undefined' && typeof Drupal.t === 'function') {
		var translatedStrings = {
			'French': Drupal.t('French'),
			'English': Drupal.t('English'),
			'German': Drupal.t('German'),
			'Information personelle': Drupal.t('Information personelle'),
			'Information personnelle': Drupal.t('Information personnelle'),
			'Personal information': Drupal.t('Personal information'),
			'Persönliche Informationen': Drupal.t('Persönliche Informationen'),
			'Name': Drupal.t('Name'),
			'Prénom': Drupal.t('Prénom'),
			'Société': Drupal.t('Société'),
			'Date de naissance': Drupal.t('Date de naissance'),
			'Email address': Drupal.t('Email address'),
			'Username': Drupal.t('Username'),
			'Téléphone mobile': Drupal.t('Téléphone mobile'),
			'Rue': Drupal.t('Rue'),
			'Code Postal': Drupal.t('Code Postal'),
			'Localité': Drupal.t('Localité'),
			'Centres d\'intérêt': Drupal.t('Centres d\'intérêt'),
			'En quelle langue préférez-vous assister aux visites ?': Drupal.t('En quelle langue préférez-vous assister aux visites ?'),
			'Carte couple/famille/YFM': Drupal.t('Carte couple/famille/YFM'),
			'Prénom du conjoint': Drupal.t('Prénom du conjoint'),
			'Nom du conjoint': Drupal.t('Nom du conjoint'),
			'Email du conjoint': Drupal.t('Email du conjoint'),
			'Téléphone mobile du conjoint': Drupal.t('Téléphone mobile du conjoint'),
			'Date de naissance du conjoint': Drupal.t('Date de naissance du conjoint'),
			'Petit Ami de la carte famille': Drupal.t('Petit Ami de la carte famille'),
			'Politique de confidentialité': Drupal.t('Politique de confidentialité'),
			'Create new account': Drupal.t('Create new account')
		};

		// Also translate any other elements with these texts
		$('*').each(function() {
			var $element = $(this);
			if ($element.children().length === 0) { // Only text nodes
				var originalText = $.trim($element.text());
				if (translatedStrings[originalText]) {
					$element.text(translatedStrings[originalText]);
				}
			}
		});
	}



})(jQuery, Drupal);


(function () {
	// Translation mapping
	const translations = {
	  en: {
		"French": "French",
		"English": "English",
		"German": "German",
		"Information personelle": "Personal information",
		"Information personnelle": "Personal information",
		"Personal information": "Personal information",
		"Persönliche Informationen": "Personal information",
		"Name": "Name",
		"Nom": "Name",
		"Prénom": "First name",
		"Société": "Company",
		"Date de naissance": "Date of birth",
		"E-Mail-Adresse": "Email address",
		"Username": "Username",
		"Téléphone mobile": "Mobile phone",
		"Rue": "Street",
		"Code Postal": "Zip code",
		"Localité": "City",
		"Centres d'intérêt": "Area of interest",
		"Assemblées générales ordinaires": "General Assembly",
		"Midis de l'Art": "Midis de l'Art",
		"Visites culturelles au Luxembourg": "Cultural guided tours in Luxembourg",
		"Visites culturelles à l'étranger": "Cultural trips abroad",
		"-Arty Hours": "Arty Hours",
		"Visites en famille": "Guided family tours",
		"Young Friends of Museums": "Young Friends of Museums",
		"En quelle langue préférez-vous assister aux visites ?": "Preferred language for the guided tours ?",
		"Carte couple ou carte famille.": "Members card for a couple or a family",
		"Prénom du conjoint": "First name of the partner",
		"Nom du conjoint": "Name of the partner",
		"Email du conjoint": "Email of the partner",
		"Téléphone mobile du conjoint": "Mobile phone of the partner",
		"Date de naissance du conjoint": "Date of birth of the partner",
		"Petit Ami de la carte famille": "Petit Ami of the family card",
		"Politique de confidentialité": "Privacy Policy",
		"Create new account": "Create new account",
		"Merci de remplir le formulaire pour créer votre compte.": "Please fill out the form to create your account.",
		"Un email de confirmation vous sera adressé ainsi que votre mot de passe.": "A confirmation email will be sent to you along with your password.",
		"Vous pourrez alors vous connecter à votre compte et effectuer le règlement de la cotisation.": "You will then be able to log in to your account and pay the membership fee."
	  },
	  de: {
		"French": "Französisch",
		"English": "Englisch",
		"German": "Deutsch",
		"Information personnelle": "Persönliche Informationen",
		"Nom": "Familienname",
		"Prénom": "Vorname",
		"Société": "Firma",
		"Date de naissance": "Geburtsdatum",
		"Email address": "Email Adresse",
		"Username": "Benutzername",
		"Téléphone mobile": "Mobiltelefonnummer",
		"Rue": "Strasse",
		"Code Postal": "Postleitzahl",
		"Localité": "Ort",
		"Centres d'intérêt": "Interessensgebiet",
		"Assemblées générales ordinaires": "Jahreshauptversammlungen",
		"Visites culturelles au Luxembourg": "Kulturelle Führungen in Luxemburg",
		"Visites culturelles à l'étranger": "Kulturelle Besichtigungen im Ausland",
		"Visites en famille": "Familienbesuche",
		"Young Friends of Museums": "Junge Freunde der Museen",
		"En quelle langue préférez-vous assister aux visites ?": "In welcher Sprache möchten Sie an den Führungen teilnehmen?",
		"Carte couple ou carte famille.": "Mitgliedskarte für ein Paar oder eine Familie",
		"Prénom du conjoint": "Vorname des Partners",
		"Nom du conjoint": "Familienname des Partners",
		"Email du conjoint": "Email Adresse des Partners",
		"Téléphone mobile du conjoint": "Mobiltelefonnummer des Partners",
		"Date de naissance du conjoint": "Geburtsdatum des Partners",
		"Petit Ami de la carte famille": "Petit Ami der Familienkarte",
		"Politique de confidentialité": "Datenschutzerklärung",
		"Create new account": "Neues Konto erstellen",
		"Merci de remplir le formulaire pour créer votre compte.": "Bitte füllen Sie das Formular aus, um Ihr Konto zu erstellen",
		"Un email de confirmation vous sera adressé ainsi que votre mot de passe.": "Sie erhalten eine Bestätigungs-E-Mail mit Ihrem Passwort.",
		"Vous pourrez alors vous connecter à votre compte et effectuer le règlement de la cotisation.": "Sie können sich dann in Ihr Konto einloggen und den Beitrag bezahlen."
	  }
	};
  
	// Detect language from URL or Drupal language
	var lang = new URLSearchParams(window.location.search).get("language");
	if (!lang && typeof Drupal !== 'undefined' && Drupal.settings && Drupal.settings.language) {
		lang = Drupal.settings.language;
	}
	if (!["en", "de"].includes(lang)) return;
  
	// Replace text nodes with jQuery for better compatibility
	jQuery(document).ready(function() {
		jQuery('*').each(function() {
			var $element = jQuery(this);
			if ($element.children().length === 0) { // Only text nodes
				var originalText = jQuery.trim($element.text());
				if (translations[lang] && translations[lang][originalText]) {
					$element.text(translations[lang][originalText]);
				}
			}
		});
	});
  })();
  

// Explicit legend translation based on URL parameter, as requested
jQuery(document).ready(function ($) {
  var params = new URLSearchParams(window.location.search);
  var lang = params.get('language');
  var $legend = $('#group-information-personelle legend .fieldset-legend');
  if (!$legend.length) return;

  if (lang === 'de') {
    $legend.text('Persönliche Informationen');
  } else if (lang === 'en') {
    $legend.text('Personal information');
  }
  // If fr or missing, keep Drupal default text
});

