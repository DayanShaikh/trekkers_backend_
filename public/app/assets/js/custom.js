$(document).on('ready', function () {
    $(".Dismiss").click(function (e) { e.preventDefault(); $this = $(this); $.get($this.attr("href")); $this.parent().fadeOut(function () { $this.parent().remove(); }); }); $("a.favorites").click(function (e) { e.preventDefault(); $this = $(this); $.get($this.attr("href") + "&ajax"); $this.parents(".data_item").toggleClass("favorites"); }); var regular = $(".regular").slick({ dots: false, infinite: false, slidesToShow: 3, slidesToScroll: 3, autoplay: false, autoplaySpeed: 3000, responsive: [{ breakpoint: 900, settings: { slidesToShow: 2, slidesToScroll: 1, } }, { breakpoint: 800, settings: { slidesToShow: 2, slidesToScroll: 2, } }, { breakpoint: 600, settings: { slidesToShow: 1, slidesToScroll: 1, centerMode: true, } }] }); $('.regular .slick-prev').hide(); regular.on('afterChange', function (event, slick, currentSlide) {
        console.log(currentSlide); if (currentSlide === 0) { $('.regular .slick-prev').hide(); $('.regular .slick-next').show(); }
        else { $('.regular .slick-prev').show(); }
        if (slick.slideCount === currentSlide + 1) { $('.regular .slick-next').hide(); }
    });
    var regular1 = $(".regular1").slick({ dots: false, infinite: true, slidesToShow: 3, slidesToScroll: 3, autoplay: false, autoplaySpeed: 2000, responsive: [{ breakpoint: 900, settings: { slidesToShow: 2, slidesToScroll: 2, } }, { breakpoint: 800, settings: { slidesToShow: 2, slidesToScroll: 2, } }, { breakpoint: 600, settings: { slidesToShow: 1, slidesToScroll: 1, centerMode: true, } }] }); $('.regular1 .slick-prev').hide(); regular1.on('afterChange', function (event, slick, currentSlide) {
        console.log(currentSlide); if (currentSlide === 0) { $('.regular1 .slick-prev').hide(); $('.regular1 .slick-next').show(); }
        else { $('.regular1 .slick-prev').show(); }
        if (slick.slideCount === currentSlide + 1) { $('.regular1 .slick-next').hide(); }
    });
    var regular2 = $(".regular2").slick({ dots: false, infinite: false, slidesToShow: 4, slidesToScroll: 4, autoplay: false, autoplaySpeed: 2000, responsive: [{ breakpoint: 900, settings: { slidesToShow: 2, slidesToScroll: 2, } }, { breakpoint: 800, settings: { slidesToShow: 2, slidesToScroll: 2, } }, { breakpoint: 600, settings: { slidesToShow: 1, slidesToScroll: 1, centerMode: true, } }] }); $('.regular2 .slick-prev').hide(); regular2.on('afterChange', function (event, slick, currentSlide) {
        console.log(currentSlide); if (currentSlide === 0) { $('.regular2 .slick-prev').hide(); $('.regular2 .slick-next').show(); }
        else { $('.regular2 .slick-prev').show(); }
        if (slick.slideCount === currentSlide + 1) { $('.regular2 .slick-next').hide(); }
    }); 
    $(".trip_icon a").click(function () { $(".photo_gallery").addClass("photo_blog"); }); $(".header_prev").click(function () { $(".photo_gallery").removeClass("photo_blog"); }); $(".bek").click(function () { $(".departure_box_main").addClass("photo_blog"); }); $(".departure_head > em").click(function () { $(".departure_box_main").removeClass("photo_blog"); }); $(".inb > a").click(function () { $(".departure_checkbox").addClass("photo_blog"); }); 

}); 
$(window).on("load", function () { $('.slider-for').slick({ slidesToShow: 1, slidesToScroll: 1, arrows: true, fade: true, asNavFor: '.slider-nav' }); $('.slider-nav').slick({ slidesToShow: 9, slidesToScroll: 1, asNavFor: '.slider-for', dots: false, arrows: false, centerMode: false, focusOnSelect: true, responsive: [{ breakpoint: 900, settings: { slidesToShow: 8, slidesToScroll: 1, } }, { breakpoint: 800, settings: { slidesToShow: 6, slidesToScroll: 2, } }, { breakpoint: 500, settings: { slidesToShow: 3, slidesToScroll: 1, } }] }); }); $(document).ready(function () {
    $(".responsive_menu").click(function () { $(".profile_change").toggleClass("res"); $(".res_nav").toggleClass("res_menu"); }); $(".tabs-icon").click(function () { $(".tab_nav ul.tabs").slideToggle(); }); $("li.submenu").click(function () { $(".submenu ul").toggleClass("sub"); $(this).toggleClass("sub1"); }); $(".group-travel-box").click(function () { window.location.href = $(this).find("a").attr("href") }); $('.trip-toggle li a').click(function (e) { e.preventDefault(); $('.trip-toggle li a').removeClass("active"); $(this).addClass("active"); $('.targetdiv').slideUp(); $('.targetdiv').hide(); $('#trips-' + $(this).data('id')).slideToggle(); }); $('.targetdiv').hide(); $('.targetdiv:first').show(); $('.trip-toggle li:first-child a').click(); $(".tab-section").hide(); $(".tab_nav a").click(function (e) {
        e.preventDefault(); window.location.hash = $(this).attr("href"); $(".tab_nav a").removeClass('active'); $(this).addClass("active"); $(".tab-section").hide(); $targetTab = $($(this).attr("href")); $targetTab.show(); $(".tabs-icon span").text($(".tab_nav .active").text())
        setTimeout(function () { t = parseInt($targetTab.offset().top); if ($(".profile_box_inn").is(":visible")) { $(".tab_nav ul.tabs").hide(); t -= $(".responsive_menu").height() }; console.log(t); $("html,body").animate({ scrollTop: t }); }, 300);
    }); if ($(".tab_nav a").length > 0) {
        if (window.location.hash) { hash = window.location.hash; }
        else { hash = $(".tab_nav li:first-child a").attr("href"); }
        $("a[href='" + hash + "']").addClass('active'); $(".tab-section").hide(); $(hash).show();
    }
    $(".data-prijzen-btn").click(function (e) { e.preventDefault(); $("#trip_list").trigger("click"); }); $(".trip_book_btn").click(function () {
        if ($("input[name=trip_id]:checked").length > 0) { window.location.href = $("input[name=trip_id]:checked").data("url"); }
        else { alert("Selecteer eerst een vertrekdatum"); }
    });
}); 
$('.footer_col').on('click', 'h3', function () { $('.footer_col h3').removeClass('active'); $(this).addClass('active'); }); $(function () {
    var svg_images_url = []; jQuery('.build_icon li img').each(function () {
        var imgURL = $(this).attr('src'); if (svg_images_url.indexOf(imgURL) === -1) {
            svg_images_url.push(imgURL); jQuery.get(imgURL, function (data) {
                var $svg = jQuery(data).find('svg'); $svg = $svg.removeAttr('xmlns:a'); if (!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) { $svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width')) }
                $svg.replaceAll('.build_icon li img[src="' + imgURL + '"]');
            }, 'xml');
        }
    });
}); var trip_page = 1; var all_loaded = false; var are_trip_loading = false; function init_image_slider() { $(".images_slider.not-init").removeClass('not-init').slick({ dots: true, infinite: true, slidesToShow: 1, slidesToScroll: 1, prevArrow: false, nextArrow: false, autoplay: false, draggable: true, }); }
function get_trips(curr_page) {
    if (!are_trip_loading) {
        $(".trips-loading").show(); are_trip_loading = true; trip_page = curr_page + 1; if (trip_page == 1) { all_loaded = false; $(".trips-boxes > .container > .row").html(""); }
        else if (all_loaded) { are_trip_loading = false; $(".trips-loading").hide(); $(".more-trips-container").hide(); return; }
        var data = { "page": trip_page, "age_group_id": $("input[name=age_group_id]:checked").val(), "date_filter": $("input[name=date_filter]").val(), "plusminus_3": $("input[name=plusminus_3]").is(":checked"), "only_europe": $(".filter_only_europe").is(":checked"), "all_other": $(".filter_all_other").is(":checked"), "trip_types": new Array() }
        $(".filter_trip_type:checked").each(function () { data.trip_types.push($(this).val()); }); $.post("vakanties.html", data, function (response) {
            $(".trips-loading").hide(); are_trip_loading = false; $(".more-trips-container").show(); response = JSON.parse(response); $(".total_trips").html("(" + response.total + ")"); if (trip_page * 12 >= response.total) { all_loaded = true; $(".more-trips-container").hide(); }
            $(".trips-boxes > .container > .row").append(response.content); setTimeout(function () { init_image_slider() }, 500);
        });
    }
}
$(document).ready(function () {
    $(".check_bx_active_n").click(function () { $(".check_box_new").toggleClass("ac_check"); }); $(".close_icon").click(function () { $(".others_filter").removeClass("activebox"); }); $(".check_bx_active_x").click(function () { $(".radio_box_new_n").toggleClass("ac_check"); }); $(".close_icon").click(function () { $(".age_group_filter").removeClass("activebox"); }); $('.vakanties_button a').click(function (e) { e.preventDefault(); $(this).parent().toggleClass('activebox'); }); $('input[name="date_filter"]').daterangepicker({
        autoUpdateInput: false, locale: { cancelLabel: 'Wissen' }, isCustomDate: function (date) {
            if (availableDates.indexOf(date.format('YYYY-MM-DD')) !== -1) { return ['highlight']; }
            return [];
        }
    }); $(".dates_filter a").click(function () { $('input[name="date_filter"]').trigger('click'); }); $('input[name="date_filter"]').on('hide.daterangepicker', function () { $(".dates_filter").removeClass("active_buttton"); }); $('input[name="date_filter"]').on('apply.daterangepicker', function (ev, picker) { $(this).val(picker.startDate.format('DD/MM/YY') + ' - ' + picker.endDate.format('DD/MM/YY')); $(".dates_filter > a").html(picker.startDate.format('DD/MM/YY') + ' - ' + picker.endDate.format('DD/MM/YY')); $('.dates_filter').addClass("active_buttton"); get_trips(0); }); $('input[name="date_filter"]').on('cancel.daterangepicker', function (ev, picker) { $(".dates_filter > a").html('Vertrekdatum'); $(this).val('Vertrekdatum'); $('.dates_filter').removeClass("active_buttton"); get_trips(0); }); $(".clear_age_group").click(function () { $("input[name=age_group_id]:checked").prop("checked", false); $(".radio_box_new_n").removeClass("ac_check"); $('.age_group_filter a').html('Alle vakanties'); $('.age_group_filter').removeClass("active_buttton"); $('.age_group_filter').removeClass("activebox"); setTimeout(function () { get_trips(0); }, 100); }); $(".search_age_group").click(function () { $(".radio_box_new_n").removeClass("ac_check"); $('.age_group_filter a').html($("input[name=age_group_id]:checked").data('title')); $('.age_group_filter').addClass("active_buttton"); $('.age_group_filter').removeClass("activebox"); setTimeout(function () { get_trips(0); }, 100); }); $(".radio_box_destop label").click(function () {
        if ($("input[name=age_group_id]:checked").val() == "0") { $(this).parents('.age_group_filter').removeClass('active_buttton'); }
        else { $(this).parents('.age_group_filter').addClass('active_buttton'); }
        $('.age_group_filter').removeClass("activebox"); $(this).parents('.age_group_filter').find('> a').html($("input[name=age_group_id]:checked").data('title')); setTimeout(function () { get_trips(0); }, 100);
    }); $(".filter_clear_btn").click(function () { $(".others_filter input[type=checked]").prop("checked", false); $('.others_filter').removeClass("active_buttton"); $('.others_filter').removeClass("activebox"); setTimeout(function () { get_trips(0); }, 100); }); $(".filter_search_btn").click(function () {
        if ($(".others_filter input[type=checkbox]:checked").length > 0) { $('.others_filter').addClass("active_buttton"); }
        else { $('.others_filter').removeClass("active_buttton"); }
        $('.others_filter').removeClass("activebox"); setTimeout(function () { get_trips(0); }, 100);
    }); if ($(".more-trips").length > 0) { $(".more-trips").click(function () { get_trips(trip_page); }); $(window).scroll(function () { if ($(window).scrollTop() + $(window).height() >= $(".more-trips").offset().top) { $(".more-trips").trigger('click'); } }); }
    init_image_slider(); $("#support-search-form").submit(function (e) { e.preventDefault(); window.location.href = $(this).data('url') + $(this).find("input[type=text]").val(); });
});
 $(function () { 
    $('.select_location').on('change', function () { 
        window.location = $(this).val(); }); $(".drp-selectbox").append($("#drp-selectbox")); $(window).scroll(function () { var scrollDistance = $(window).scrollTop(); $('.page-section').each(function (i) { if ($(this).position().top <= scrollDistance) { $('.navigation a.pactive').removeClass('pactive'); $('.navigation a').eq(i).addClass('pactive'); } }); }).scroll(); $('.radio_box_inn input[type="radio"]').click(function () { if ($(this).is(":checked")) { $(".check_bx_active_x").addClass("active_buttton"); } else { $(".check_bx_active_x").removeClass("active_buttton"); } }); 
    }); 
 $(".slu_button > a").click(function () { $(".departure_checkbox").removeClass("photo_blog"); }); 
 $(document).ready(function () {
    if ($('.tabsnav').length > 0) { var stickyNavTop = $('.tabsnav').offset().top; $(window).resize(function () { stickyNavTop = $('.tabsnav').offset().top; if (!$(".tabs-icon").is(":visible")) { $(".tab_nav ul.tabs").show(); } }); var stickyNav = function () { var scrollTop = $(window).scrollTop(); if (scrollTop > stickyNavTop) { $('.tabsnav').addClass('sticky1'); if (!$(".tabs-icon").is(":visible")) { $('.tab_nav > div').addClass('container'); } } else { $('.tabsnav').removeClass('sticky1'); $('.tab_nav > div').removeClass('container'); } }; stickyNav(); $(window).scroll(function () { stickyNav(); }); }
    var $selectValue = $('#select_value').find('strong'); $selectValue.text($('#get_value').val()); $('#get_value').selectric().on('change', function () { $selectValue.text($(this).val()); }); $(".tooltip_btn").click(function (e) { e.preventDefault(); }); $(".tooltip_btn").hover(function () { $this = $(this); $parent = $this.parent(); $parent.css("position", "relative"); $div_id = $(this).attr("href"); $($div_id).appendTo($parent); $($div_id).css("position", "absolute").css("top", $this.position().top + 35).css("left", $this.position().left - 116).show(); }, function () { $div_id = $(this).attr("href"); setTimeout(function () { $($div_id).hide(); }, 500); });
}); 
$(".main_inn ul li a").click(function () { $('html, body').animate({ scrollTop: parseInt($(".tab_detail_bg").offset().top - 100) }, 100); }); $(document).ready(function () { $(".nav_inn ul li.has-sub-menu > i").click(function () { $(this).parent().find('> ul').slideToggle(); }); $(".sidebar_nav ul li span.toggle_cat_menu").click(function () { $(this).parent().find('> ul').slideToggle(); }); }); $(document).ready(function () { $(".whatsapp_number_a").click(function (e) { e.preventDefault(); $(this).hide(); $(this).parent().find(".whatsapp_number_c").hide(); $(this).parent().find("form").show(); }) }); $(document).on("click", function () { $(".average_box .info_icon").click(function () { $(".average_box .info_box").show(); }); $(".average_box .tip_close").click(function () { $(".average_box .info_box").hide(); }); $(".triptable>ul>li>div.col6 .info_icon").click(function () { $(".triptable>ul>li>div.col6 .info_box").show(); }); $(".triptable>ul>li>div.col6 .tip_close").click(function () { $(".triptable>ul>li>div.col6 .info_box").hide(); }); $(".triptable>ul>li>div.col5 .info_icon").click(function () { $(".triptable>ul>li>div.col5 .info_box").show(); }); $(".triptable>ul>li>div.col5 .tip_close").click(function () { $(".triptable>ul>li>div.col5 .info_box").hide(); }); })
    $(document).ready(function () {
    var selected_ids = []; $(".keuzehulp-filter").hide(); $(".keuzehulp-filter").first().show(); $(".keuzehulp-filter a").click(function (e) {
        e.preventDefault(); selected_ids.push($(this).data("id")); $container = $(this).parents(".keuzehulp-filter"); $container.find(".col-md-4").removeClass("active"); $(this).parents(".col-md-4").addClass("active"); $container.find(".col-md-4").not(".active").fadeOut(function () {
            $container.find(".col-md-4.active").removeClass("offset-md-2").addClass("offset-md-4"); $next = $(".keuzehulp-filter:visible").next(); $(".keuzehulp-filter:visible").fadeOut(function () {
                if ($next.length > 0) { $next.show(); }
                else { window.location.href = $(".keuzehulp-filter a").attr("href") + "?ids=" + JSON.stringify(selected_ids); }
            });
        });
    });
});