function acceptChange(id, songUri) {
  _handleChange("acceptSong.php", id, songUri);
}

function declineChange(id, songUri) {
  _handleChange("declineSong.php", id, songUri);
}

function _handleChange(filename, id, songUri) {
  var rootId = "#change-" + songUri.replaceAll(":", "\\:");
  var rootElem = $(rootId);
  var buttons = $(rootId + " .colButtons *");
  buttons.prop('disabled',true);

  var url = filename + "?suppressRedirect=true&id=" + id + "&songUri=" + songUri;
  var nChanges = $(".addedSong:not(.hidden)").length;

  // current song is not hidden
  if(nChanges <= 1) {
    window.location.href = url;
    return;
  }

  _playNextSong(rootElem);

  rootElem.addClass("hidden");


  fetch(url)
    .then(response => {
      rootElem.remove();
    })
    .catch(response => {
      console.log(response);

      rootElem.removeClass("hidden");
    });
}

function _playNextSong(rootElem) {
  var playBtnWrapper = rootElem.find(".colPlay");
  var isPlaying = playBtnWrapper.hasClass("isPlaying");

  if(isPlaying) {
    var nextPlayBtn = rootElem.nextAll().find(".start-playing:not(:disabled)");
    if(nextPlayBtn.length > 0) {
      pause();
      onPlayButton(nextPlayBtn[0]);
    } else {
      onPauseButton(playBtnWrapper);
    }
  }
}