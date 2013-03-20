<?php
/*
    Template Name: Multimedia
*/
?>
<?php
    get_header();
?>
<script>
$(document).ready(function() {
    $.ajax({
	    type: "GET",
    	data: "",
    	url: "http://yaledailynews.staging.wpengine.com/?json=get_recent_posts",
        success: function(data) {
            console.log(data);
        }
    }).done(function ( data ) {
        console.log(data);
        helper_helper("HELLO");
    }); 
});
</script>
<a src="" id="Latest" class="mult-menu">Latest</a>
<a src="" id="University" class="mult-menu">University</a>
<a src="" id="Culture" class="mult-menu">Culture</a>
<a src="" id="City" class="mult-menu">City</a>
<a src="" id="Sports" class="mult-menu">Sports</a>
<div class="span24">
    <div class="row">
        <div id="main-theater" class="span24">
            <iframe id="video-player" src="" frameborder="0">
            </iframe>
        </div>
    </div>
    <div class="row">
        <div id="slider" class="span24">
            <div class="carousel slide">
                <div class="carousel-inner">
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    get_footer();
?>
