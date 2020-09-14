jQuery(function($){
    // Mobile Nav Toggling
    $('.nav-toggle').on('click', function(){
        $('html').toggleClass('nav-open');
    });

    // Mobile Subnav Toggling
    $('.subnav-toggle').on('click', function(){
        $(this).parent().toggleClass('open');
    });

    $('.signup-wrap .trigger').hover(function(){
        $(this).parent().toggleClass('hover');
    }).on('click', function(){
        $('html').toggleClass('header-signup-open');
    });



    // Header Scroll
    // ====================================================
    var headerScrollClass = function(){
        if($(window).scrollTop() > 49) {
            $('body').addClass('scrolled').removeClass('not-scrolled');
        } else {
            $('body').removeClass('scrolled').addClass('not-scrolled');
        }
    }
    $(window).on('scroll',function(){
        headerScrollClass();
    });
    headerScrollClass();

    // Show Popup
    // ===================================================

    if ($('#popup-global-page').length > 0) {

        function checkPopup() {
            var popupShown=getCookie('popup');
             if (popupShown == '') {
                $('#popup-global-page').addClass('open');
            }
        }

        checkPopup();
       
    };

    // Tree Filter
    var treesID = '#trees-wrap',
        trees = $(treesID);
    if (trees.length > 0) {

        var initialstate = { trees: $('#trees').html(), filter: $('#tree-filter').html() };
        history.pushState(initialstate, null, null);

        // Change checkbox classes
        $('#tree-filter input[type="checkbox"]').on('change', function(){

            var $this = $(this);

            if ($this.is(':checked')) {
                $this.parent().removeClass('checked');
            } else {
                $this.parent().addClass('checked');
            }
        });

        // Reset Button
        trees.on('click', '.reset-form', function(e){
            e.preventDefault();
            var $form = $(this).parents('form');

            $form.find('.checked').each(function(){
                $(this).removeClass('checked').find('input[type="checkbox"]').removeAttr('checked');
            });

            $form.find('select').each(function(){
                $(this).find('option[selected="selected"]').removeAttr('selected');
            });

            $('.submit-on-change').first().trigger('change');
        });


        // Show/Hide Filter on mobile
        trees.on('click', '.show-hide-filter', function(){
            $('#trees-wrap').toggleClass('filter-open');
        });

        // Magic
        trees.on('change', '.submit-on-change', function(e){

            e.preventDefault();
            var $this = $(this);

            // Fake Checkbox
            if ($this[0].type == 'checkbox') {
                $this.parent().toggleClass('checked');
            }

            // get url of new results
            var filterID = '#tree-filter',
                form = $(filterID),
                url = form.attr('action') + '?' + form.serialize();

            trees.addClass('loading');

            // Grab trees and filter
            $.ajax({
                url: url,
                success: function(data){

                    var newTrees = $(data).find(treesID).html(),
                        savestate = newTrees;

                    // Replace Trees
                    trees.removeClass('loading').removeClass('filter-open').html(newTrees);

                    // Add Push State
                    history.pushState(savestate, null, url);
                }
            });
        });        

        // Forward/Back for Tree Filter
        $(window).bind('popstate', function(e){
            if (e.originalEvent.state) {
                trees.html(e.originalEvent.state);
            }
        });
    } // end tree filter


    // Tree image gallery
    $('.swipe').each(function(){
        var $this = $(this);
        $this.find('.swipe-wrap .slide').eq(0).addClass('active');
        // $this.find('.direct a').eq(0).addClass('active');
        var auto = $this.data('auto') ? $this.data('auto') : 8000;
        
        var swipe = new Swipe($this[0],{
            startSlide: 0,
            speed: 400,
            auto: auto,
            continuous: true,
            disableScroll: false,
            stopPropagation: false,
            callback: function(index, elem) {
                $this.find('.active').addClass('outgoing').removeClass('active');
                $this.find('.slide').eq(index).addClass('incoming');
                // $this.find('.direct a').eq(index).addClass('incoming');
            },
            transitionEnd: function(index, elem) {
                $this.find('.incoming').addClass('active').removeClass('incoming');
                $this.find('.outgoing').removeClass('outgoing');
            }
        });
        $this.data('swipe',swipe);

        $('.image-gallery').find('.next').click(function() { swipe.next(); });
        $('.image-gallery').find('.prev').click(function() { swipe.prev(); });
        // $this.find('.direct a').click(function() { swipe.slide($(this).data('slide') - 1); });
    });


    // Post filter
    var postFilter = $('.post-filter form');
    postFilter.find('.submit-on-change').on('change', function(){
        postFilter.submit();
    });


    // Resources filter
    var resourcesFilter = $('.resources-filter'),
        wrap = resourcesFilter.siblings('.post-list');

    if (resourcesFilter.length > 0) {
        
        resourcesFilter.find('.submit-on-change').on('change', function(){
            resourcesFilter.submit();
        });

        var initialstate = wrap.html();
        history.pushState(initialstate, null, null);
        
        resourcesFilter.on('submit', function(e){
            e.preventDefault();

            var url = resourcesFilter.attr('action') + '?' + resourcesFilter.serialize();

            wrap.addClass('loading');

            $.ajax({
                url: url,
                success: function(data){

                    var results = $(data).find('.post-list').html(),
                        savestate = results;

                    // Replace results
                    wrap.removeClass('loading').html(results);

                    // Add Push State
                    history.pushState(savestate, null, url);
                }
            });
        }); 

        // Forward/Back for Tree Filter
        $(window).bind('popstate', function(e){
            if (e.originalEvent.state) {
                wrap.html(e.originalEvent.state);
            }
        });
    }


    // Go To Tree
    $('body').on('change', '.go-to-tree select', function(){
        window.location.href = $(this).find('option:selected').val();
    });


    // Tweet Linker
    $('.tweet').twlinkify();

    $('.stat-value .value').each(function(){
        
        var $this = $(this),
            checkVisible = function(){
                var id = $this.attr('id'),
                    start = 0,
                    end = $this.text();
                
                if ($this.isOnScreen() && !$this.hasClass('count-complete')) {
                    $this.addClass('count-complete');
                    var numAnim = new CountUp(id, start, end);
                    numAnim.start();
                }
            };
 
        checkVisible();
        $(window).on('scroll', checkVisible);
    });

    $('.sticky-blog-sidebar').stick_in_parent({
        'offset_top': 160
    });




    // Resources Filter
    $('.resources-filter input[type="radio"]').on('change', function(){
        var $this = $(this);
        $('.category-choose .selected').removeClass('selected');
        $this.closest('label').addClass('selected');
        $this.parents('form').submit();
    });


    // Get Updates Form Validation

    var rules = [{
            name: 'cons_first_name',
            display: 'required',
            rules: 'callback_alpha_only_dash'
        },
        {
            name: 'cons_last_name',
            display: 'required',
            rules: 'callback_alpha_only_dash'
        },
        {
            name: 'cons_email',
            display: 'required',
            rules: 'valid_email'
        }];

    function form_errors(errors, $this) {

        for(var i=0; i<errors.length; i++) {
            // console.log(errors[i]);
            var form_thing = $($this[0].form).find('input[name=' + errors[i].name + ']');
            form_thing.parent().addClass('has-error').find('.error').text(errors[i].message);
        }
    }

    function custom_rules(validator) {
        var alphaOnlyDashRegex = /^[a-z\-]+$/i;
        validator.registerCallback('alpha_only_dash', function(value) {
            return (alphaOnlyDashRegex.test(value));
        }).setMessage('alpha_only_dash', 'This field must only contain alphabetical characters and dashes.');
    }
        
    var validator1 = new FormValidator('get_updates_header', rules, function(errors, event) {
        if (errors.length > 0) {
            form_errors(errors, $(this));
        }
    });

    custom_rules(validator1);
        
    var validator2 = new FormValidator('get_updates_footer', rules, function(errors, event) {
        custom_rules(this);
        if (errors.length > 0) {
            form_errors(errors, $(this));
        }
    });

    custom_rules(validator2);

    $('form').on('click', '.field.has-error', function(){
        $(this).removeClass('has-error');
    });


});