var activeAudio = null;

function onPlayButton(button) {
  $(".colPlay").removeClass("isPlaying");
  $(button).closest(".colPlay").addClass("isPlaying");

  var url = $(button).data("source");
  play(url);
}

function onPauseButton(button) {
  $(button).closest(".colPlay").removeClass("isPlaying");
  pause();
}

function play(url) {
  if(activeAudio == null) {
    activeAudio = new Audio();
    activeAudio.onended = function() {
      $(".colPlay").removeClass("isPlaying");
    }
  }

  activeAudio.src = url;
  activeAudio.load();
  activeAudio.play();
}

function pause() {
  activeAudio.pause();
}