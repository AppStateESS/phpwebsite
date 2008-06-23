<script type="text/javascript" src="javascript/jquery/jcarousellite.js"></script>
<script type="text/javascript">

var vertical_set = {vertical};
var visible_set = {visible};

$(document).ready(function() {
initSlides(vertical_set, visible_set);
});


function initSlides(vert, vis)
{

$(".carousel-slides").jCarouselLite({
       btnNext: ".carousel .carousel-next",
       btnPrev: ".carousel .carousel-prev",
       vertical: vert,
       visible : vis,
       speed : 500
     });
}
</script>
