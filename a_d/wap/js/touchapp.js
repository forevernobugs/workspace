var inputfocusblur = function(a, b, c) {
	a.focus(function() {
		b.addClass(c)
	}).blur(function() {
		0 == a.val().length && b.removeClass(c)
	})
},
slideEllipsis = function(a) {
	var b = a.find(">.ui-swipeslide-reel>li>a>p>span"),
	a = a.find(">.ui-swipeslide-bullets").width(),
	c = $(".ui-container").width() - 5 - a;
	b.each(function() {
		$(this).width(c)
	})
};
function limitMaxWidth(a, b) {
	b = parseInt(b, 10);
	$(window).width() > b && a.css({
		maxWidth: b,
		position: "relative",
		left: "50%",
		marginLeft: -b / 2
	})
}
$(function() {
	var a;
	setTimeout(function() {
		window.scrollTo(0, 1)
	},
	100);
	if ($(".js-index-slider")[0]) {
		var b = $(".js-index-slider");
		b.swipeSlide({
			beforeChange: function(a) {
				$.each(a.slides,
				function(a, b) {
					$(b).find("p").css("opacity", 0)
				})
			},
			afterChange: function(a, b) {
				$(a.slides[b]).find("p").animate({
					opacity: 1
				},
				200)
			},
			autoPlay: 5
		});
		limitMaxWidth(b, 640);
		slideEllipsis($(".js-index-slider"));
		$(window).resize(function() {
			slideEllipsis($(".js-index-slider"))
		})
	}
	$(".js-category-slider")[0] && ($(".js-category-slider").swipeSlide({
		beforeChange: function(a) {
			$.each(a.slides,
			function(a, b) {
				$(b).find("p").css("opacity", 0)
			})
		},
		afterChange: function(a, b) {
			$(a.slides[b]).find("p").animate({
				opacity: 1
			},
			200)
		},
		autoPlay: 5
	}), slideEllipsis($(".js-category-slider")), $(window).resize(function() {
		slideEllipsis($(".js-category-slider"))
	}));
	if ($(".js-picture-show")[0]) {
		var c = $(".js-picture-show"),
		d = $(".ui-picture-extra-info"),
		e = $(".js-picture-page");
		c.css("opacity", 0);
		var f = $(".js-swipegohome");
		d.data("fullscreen", !1);
		c.find(".ui-picture-single").bind("click",
		function() { ! 1 == d.data("fullscreen") ? (d.animate({
				opacity: 0,
				bottom: -45
			},
			300), e.animate({
				opacity: 0,
				bottom: 40
			},
			300), f.animate({
				opacity: 0
			}), d.data("fullscreen", !0)) : (d.animate({
				opacity: 1,
				bottom: 0
			},
			300), e.animate({
				opacity: 1,
				bottom: 70
			},
			300), f.animate({
				opacity: 1
			}), d.data("fullscreen", !1))
		});
		c.find(".ui-picture-single").on("load",
		function() {
			var a = $(this),
			b = a.height();
			setTimeout(function() {
				var c = $(window).height();
				a.css({
					marginTop: (c - b) / 2
				})
			},
			200);
			setTimeout(function() {
				a.css({
					visibility: "visible"
				})
			},
			200)
		});
		setTimeout(function() {
			$(window).width();
			a = $(window).height();
			c.animate({
				opacity: 1
			},
			500);
			c.css("height", a);
			var b = new SwipeSlide(c, {
				bulletNavigation: !1,
				beforeChange: function(a, b, c) {
					setTimeout(function() {
						window.scrollTo(0, 1)
					},
					300);
					$.os.android && e.find("span:first-child").html(c + 1)
				},
				afterChange: function(a, b) {
					$.os.ios && !$.os.android && e.find("span:first-child").html(b + 1);
					var c = a.slides[b],
					f = $(c).find(".ui-picture-title").html(),
					c = $(c).find(".ui-single-summary").html();
					d.find(".ui-picture-title").html(f);
					d.find(".ui-single-summary").html(c)
				}
			});
			e.find("span:first-child").html(b.options.first + 1);
			e.find("span:last-child").html(b.numPages)
		},
		1E3)
	}
	$(document.body).height() < $(window).height() && $(".ui-container").css({
		minHeight: $(window).height() - 40
	})
});
var saveToScreen = {
	el: null,
	setIcon: function(a) {
		this.tpl = '<div class="ui-ios-savescreen js-hidden"><p>\u5148\u70b9\u51fb<img src="' + a + '" width="16" height="12" alt=""><br />\u518d"\u6dfb\u52a0\u81f3\u4e3b\u5c4f\u5e55"</p><div class="ios-icon"></div><div></div></div>'
	},
	init: function(a) {
		var b = this,
		c = document;
		if (!0 !== navigator.standalone && $.os.iphone && "true" != localStorage.getItem("savetoscreen")) {
			this.setIcon(a);
			var d = this.el = $(this.tpl);
			$(c.body).append(d);
			setTimeout(function() {
				setTimeout(function() {
					b.hide()
				},
				15E3);
				b.show();
				d.data("height", d.height() + 10);
				b.scroll();
				d.find(":last-child").click(function() {
					b.hide()
				})
			},
			3E3)
		}
	},
	show: function() {
		this.el.removeClass("js-hidden")
	},
	hide: function() {
		var a = this.el;
		a.addClass("js-hidden");
		a.data("close", !0);
		localStorage.setItem("savetoscreen", "true")
	},
	scroll: function() {
		var a = this.el;
		if (!0 !== a.data("close")) {
			var a = this.el,
			b = a.data("height");
			a.css("top", window.innerHeight - b)
		}
	}
};