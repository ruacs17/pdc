jQuery.fn.centerView = function () {
	$('html,body').animate({ scrollTop: $(this).offset().top - ( $(window).height() - 100 ) / 2  },400);
}