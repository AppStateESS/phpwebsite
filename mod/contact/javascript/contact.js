var contact_tab = new ContactTab;
var contact_map = new ContactMap;
var contact_social = new ContactSocial;
$(window).load(function() {
    contact_tab.start();
    contact_map.start();
    contact_social.start();
});

function ContactTab() {
    var $this = this;
    this.active = active_tab;
    this.google_url = null;

    this.start = function() {
        this.changeTab();
        $('li.contact-info-tab').click(function() {
            $this.setActive('contact-info');
        });
        $('li.map-tab').click(function() {
            $this.setActive('map');
        });
        $('li.social-tab').click(function() {
            $this.setActive('social');
        });
    };

    this.resetSections = function() {
        $('.contact-section').hide();
        $('.contact-tab').removeClass('active');
    };

    this.setActive = function(now_active) {
        $this.active = now_active;
        $this.changeTab();
    };

    this.changeTab = function() {
        $('.contact-section').removeClass('active');
        var class_tab_section = '.' + this.active + '-section';
        var class_tab = '.' + this.active + '-tab';

        this.resetSections();
        $(class_tab_section).show();
        $(class_tab).addClass('active');
    };
}

function ContactMap() {
    var $this = this;
    this.start = function() {
        $('button.grab-thumbnail').click(this.getGoogleImage);
        $('button.save-thumbnail').click(this.saveImage);
        if (thumbnail_map.length > 0) {
            $('button.save-thumbnail').hide();
        }
    };

    this.getGoogleImage = function() {
        $.getJSON('contact/admin/map/locationString')
                .done(function(data) {
                    if (data.error !== undefined) {
                        $('#map-error span').html(data.error);
                        $('#map-error').show();
                    } else {
                        $this.makeGoogleMap(data.address);
                    }
                });
    };

    this.saveImage = function() {
        $.getJSON('contact/admin/map/saveThumbnail',
                {
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val()
                }).done(function(data) {
            if (data.result === undefined) {
                alert('Failed to save Google thumbnail');
            } else {
                $this.imageSuccessMessage();
            }
        }).fail(function() {
            alert('Failed to save Google thumbnail');
        });
    };

    this.imageSuccessMessage = function() {
        $('.map-section').prepend('<div class="alert alert-success alert-dismissible" role="alert">\
            <button type = "button" class = "close" data-dismiss="alert" aria-label="Close">\
            <span aria-hidden="true">&times;</span></button>\
            Map image saved.</div>');
    };

    this.imageFailureMessage = function() {
        $('.map-section').prepend('<div class="alert alert-danger alert-dismissible" role="alert">\
            <button type = "button" class = "close" data-dismiss="alert" aria-label="Close">\
            <span aria-hidden="true">&times;</span></button>\
            <strong>Error:</strong> Map image could not be saved successfully.</div>');
    };

    this.makeGoogleMap = function(address) {
        var geocoder;
        var latitude;
        var longitude;

        // get latitude and longitude
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                latitude = results[0].geometry.location.lat();
                longitude = results[0].geometry.location.lng();
                $this.createGoogleLink(latitude, longitude);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    };

    this.createGoogleLink = function(latitude, longitude) {
        $.getJSON('contact/admin/map/getGoogleLink', {
            'latitude': latitude,
            'longitude': longitude
        }).done(function(data) {
            if (data.error !== undefined) {
                // check this
                $('#map-error').show();
            } else {
                $('button.save-thumbnail').show();
                $this.pushImageToPage(data.url);
                $this.fillHiddenVars(latitude, longitude);
            }
        });
    };

    this.fillHiddenVars = function(latitude, longitude) {
        $('#latitude').val(latitude);
        $('#longitude').val(longitude);
    };

    this.pushImageToPage = function(url)
    {
        var image_tag;
        image_tag = '<img id="google-map-image" src="' + url + '" />';
        $('.map-image').html(image_tag);
    }
}

function ContactSocial() {
    var $this = this;
    var all_tabs;
    var current_tab;

    this.start = function() {
        var first_tab;
        this.all_tabs = $('.social-pick-tab');
        first_tab = $(this.all_tabs[0]);
        first_tab.addClass('active');
        this.current_tab = first_tab.data('icon');
        this.readyTabs();
        this.populateForm();
    };
    
    this.populateForm = function() {
        $('#social-icon').html('<i class="fa fa-5x fa-' + this.current_tab + '"></i>');
    };
    
    this.readyTabs = function() {
        $('.social-pick-tab').click(function() {
            console.log($(this).data('icon'));
            $this.current_tab = $(this).data('icon');
            $this.setActiveTab(this);
            $this.populateForm();
            
        });
    };
    
    this.setActiveTab = function(selected) {
        $('.social-pick-tab').removeClass('active');
        $(selected).addClass('active');
    };
    
}