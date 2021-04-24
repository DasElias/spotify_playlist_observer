$(function() {
  var footer = $("footer.content-footer");
  if(! footer) return;

  footer.addClass("content-footer-absolute");

  var main = $("main");
  setMainMargin(main, footer);

  $( window ).resize(function() {
    setMainMargin(main, footer);
  });

  
}); 

function setMainMargin(main, footer) {
  var mainOffset = main.offset();
  var mainTop = mainOffset.top;
  var mainBottom = mainTop + main.height();
  var height = footer.innerHeight();

  if($(window).height() > (mainBottom + height)) {
    footer.addClass("content-footer-absolute");
    main.css("padding-bottom", height + "px");
  } else {
    footer.removeClass("content-footer-absolute");
    main.css("padding-bottom", "0px");
  }
}