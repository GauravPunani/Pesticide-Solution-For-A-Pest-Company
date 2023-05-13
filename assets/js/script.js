jQuery(document).ready(function() {
	window.$=jQuery;	
	banner();
	
	jQuery('#services-icon').owlCarousel({
		itemsCustom : [
        	[320, 1],
        	[480, 2],
        	[750, 3],
        	[1140, 6]
     	],
     	pagination: false,
     	navigation: true,
     	navigationText: ["<span class='glyphicon glyphicon-menu-left' aria-hidden='true'></span>","<span class='glyphicon glyphicon-menu-right' aria-hidden='true'></span>"],
     	autoPlay: true
	});
	
	jQuery('#toggle').click(function(e){
		e.preventDefault();
		jQuery('.header-navigation').stop(true).slideToggle();
		// jQuery('ul.sub-menu').hide();
	});
	
});

jQuery(window).load(function() {
	owlsett();
	service_icon();
	equalHeight(jQuery('#services-icon p.icon'));
	equalHeight(jQuery('#problem p.icon'));
});

jQuery(window).resize(function(){
	banner();
	owlsett();
	// menu();
	service_icon();
});

function menu(){
	// if( jQuery(window).width() < 750 ){
	// 	jQuery('nav ul').hide();
	// } else {
	// 	jQuery('nav ul').show();
	// 	jQuery('ul.sub-menu').removeAttr('style');
	// }
}

function banner(){
	jQuery('.banner-text').each(function(){
		jQuery(this).css('margin', (jQuery('#banner').height() - jQuery(this).height() )/2 +'px auto');
	});
	
	if( jQuery(window).width() < 460 ){
		jQuery('#banner .form').width( jQuery('#banner').width() );
	} else {
		jQuery('#banner .form').removeAttr('style');
	}
	
}

function owlsett(){
	var t = (jQuery('#services-icon .owl-wrapper-outer').height() / 2) + (jQuery('#services-icon .owl-controls').height() / 2);
	jQuery('#services-icon .owl-controls').css({
		'margin-top':'-'+ t +'px',
		'margin-bottom': t - 30 +'px',
		'margin-left':'-25px',
		'width' : jQuery('#services-icon .owl-wrapper-outer').width() + 50
	});
}

function service_icon(){
	jQuery('.service-icon').each(function(){
		jQuery(this).find('img').css('margin-top', (jQuery(this).height() - jQuery(this).find('img').height() )/2 +'px');
		jQuery(this).parent('div').removeAttr('style');
		if( jQuery(window).width() > (749 - 30) ){
			var b = jQuery(this).parent('div').next('div').height();
				i = jQuery(this).parent('div').height();
			if( i < b ){
				jQuery(this).parent('div').height(b);
				jQuery(this).css('margin-top', (b-i)/2 +'px');
			}
		}
	});
}

function equalHeight(group) {
	var tallest = 0;
	group.each(function() {
		var thisHeight = jQuery(this).height();
		if(thisHeight > tallest) {
			tallest = thisHeight;
		}
	});
	group.height(tallest);
}


// Menu, mobile ready
jQuery(document).on('click', '.menu-item-has-children:not(".children-shown")', function(event) {
    if(jQuery(window).width()>768) return;
    event.preventDefault();
    var $submenu = jQuery(this).children('ul.sub-menu').eq(0),
      animationSpeed = $submenu.children('li').length>1?$submenu.children('li').length*25:5*25;
    if(!jQuery(this).parent('ul').hasClass('sub-menu')){
      jQuery('.header-navigation > .menu-item-has-children').removeClass('children-shown');
      jQuery('ul.sub-menu.shown').removeClass('shown').slideUp(animationSpeed);
      $submenu.addClass('shown').slideDown(animationSpeed);
    }else{
      $submenu.fadeIn(animationSpeed).addClass('shown');
    }
    jQuery(this).addClass('children-shown');
    $submenu.mouseenter(function(){
          clearTimeout(jQuery(this).data('timeoutId'));
      }).mouseleave(function(){
        var $someElement = jQuery(this),
            timeoutId = setTimeout(function(){
                $someElement.slideUp(animationSpeed);
                jQuery('.children-shown').removeClass('children-shown');
            }, 830);
        $someElement.data('timeoutId', timeoutId); 
    });
});




// Restricts input for the given textbox to the given inputFilter function.
function setInputFilter(textbox, inputFilter) {
	["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
	  textbox.addEventListener(event, function() {
		if (inputFilter(this.value)) {
		  this.oldValue = this.value;
		  this.oldSelectionStart = this.selectionStart;
		  this.oldSelectionEnd = this.selectionEnd;
		} else if (this.hasOwnProperty("oldValue")) {
		  this.value = this.oldValue;
		  this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
		} else {
		  this.value = "";
		}
	  });
	});
  }

(function($){
	$(document).ready(function(){

		$.each($('.numberonly'),function(key,value){
			setInputFilter(value, function(val) {
				return /^\d*\.?\d*$/.test(val); // Allow digits and '.' only, using a RegExp
				});
		});

		$('.contract_start_date').on('change',function(){
			if($(this).val()!="" && $(this).val()!=undefined){
				let old_date=new Date($(this).val());

				let new_date=new Date(old_date.getFullYear()+1,old_date.getMonth(),old_date.getDate());

				let new_date_string=new_date.getFullYear()+"-"+("0"+(new_date.getMonth()+1)).slice(-2)+"-"+("0"+new_date.getDate()).slice(-2);
				console.log(new_date_string);
				
				$('.contract_end_date').val(new_date_string);
			} 
		})

	})
})(jQuery); 

//jQuery(document).ready(function(){
        // jQuery("#mega-menu-header").hide();
        //jQuery("#mega-toggle-block-1").click(function(){
            // jQuery("#mega-menu-header").animate({
               // width: "toggle"
            //});
        //}); 
   // });

   
  
  
      


 
	




		
	
