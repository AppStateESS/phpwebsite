var contact_tab = new ContactTab;
var contact_map = new ContactMap;
$(window).load(function() {
    contact_tab.start();
    contact_map.start();
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
    this.google_url = null;
    this.lat_long_string = null;

    var $this = this;
    this.start = function() {
        $('button.grab-thumbnail').click(this.getGoogleImage);
        $('button.save-thumbnail').click(this.saveImage);
    };

    this.saveImage = function() {
        var map_image = document.getElementById('google-map-image');
        console.log(map_image);
        var img_data = JSON.stringify(getBase64Image(map_image));
        console.log(img_data);
    };

    this.getGoogleImage = function() {
        $.getJSON('contact/admin/locationString')
                .done(function(data) {
                    if (data.error !== undefined) {
                        $('#map-error').show();
                    } else {
                        $this.makeGoogleMap(data.address);
                    }
                });
    };

    this.makeGoogleMap = function(address) {
        var geocoder;
        var google_string;
        var lat_long;
        
        // get latitude and longitude
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                lat_long = [results[0].geometry.location.A, results[0].geometry.location.F];
                $this.lat_long_string = lat_long[0] + ',' + lat_long[1];
                $this.google_url = $this.createGoogleLink(lat_long);
                $this.pushImageToPage();
                $this.fillHiddenVars();
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    };

    this.createGoogleLink = function(lat_long) {
        return 'https://maps.googleapis.com/maps/api/staticmap?center=' + this.lat_long_string + '&size=300x300&maptype=roadmap&zoom=17&markers=color:red%7Clabel:A%7C' + this.lat_long_string;
    };

    this.fillHiddenVars = function() {
        $('#google-lat-long').val(this.lat_long_string);
    };

    this.pushImageToPage = function()
    {
        var image_tag;
        image_tag = '<img id="google-map-image" src="' + this.google_url + '" />';
        $('.map-image').html(image_tag);
    }
}