jQuery(function($){



	// Add Embed button
//	$("body").append("<a href=\"#embed-pnl\" id=\"embed-btn\">Embed</a>"); 
	
	/* 
		[Share this animation]
	---------------------------------------------------------------------------*/
	var $shareTab = $("#share-tb-wrp").removeClass("no-js"),
		$embedBtn = $("#embed-btn"),
		$embedPnl = $("#embed-pnl").hide();
	
	
	
	$("#share-btn").toggle(function(e){
		$shareTab.stop().animate({left : 0},100);
		e.preventDefault();
	},function(e){
		$shareTab.stop().animate({left : -155},100);
		e.preventDefault();
	});
	
	
	//Show the embed panel
	$embedBtn.click(function(e){
		
		$embedPnl.show();
		$.colorbox( {title : "Libya Crisis Map", inline : true, href : "#embed-pnl" , onClosed : function(){$embedPnl.hide()} } );
		e.preventDefault();
	});	
	
	$embedPnl.find("input").click(function(){$(this).select()});
	
	
});
