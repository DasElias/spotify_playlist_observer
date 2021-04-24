$(function() {
  // check if dropdown exists and is touch
  var isTouch = "ontouchstart" in window || navigator.msMaxTouchPoints;
  if($(".dropdown").length) {
    $(".dropdown").each(function() {
      var dd = $(this);
      var btn = dd.find(".dropdown-toggle");
      btn.on('click touch', function() {
        dd.addClass("dropdown-visible");
      });
    });

    $(document).on('click touch', function(event) {
      var target = $(event.target);
      if(! target.parents(".dropdown").length) {
        $(".dropdown").removeClass("dropdown-visible");
      }
    });
  }

  
}); 