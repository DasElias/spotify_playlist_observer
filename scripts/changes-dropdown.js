$(function() {
  // check if dropdown exists and is touch
  var isTouch = "ontouchstart" in window || navigator.msMaxTouchPoints;
  if($(".dropdown").length && isTouch) {
    $(".dropdown").each(function() {
      var dd = $(this);
      var btn = dd.find(".dropdown-toggle");
      btn.click(function() {
        dd.addClass("dropdown-visible");
      });
    });

    $(window).click(function(event) {
      var target = $(event.target);
      if(! target.parents(".dropdown").length) {
        $(".dropdown").removeClass("dropdown-visible");
      }
    });
  }

  
}); 