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

  var url = filename + "?id=" + id + "&songUri=" + songUri;
  fetch(url)
    .then(response => {
      // start playing next song if trailer is played
      var playBtn = rootElem.find(".colPlay");
      var isPlaying = playBtn.hasClass("isPlaying");

      if(isPlaying) {
        var nextPlayBtn = rootElem.next().find(".start-playing");
        if(nextPlayBtn.length != 0) {
          onPlayButton(nextPlayBtn);
        } else {
          onPauseButton(playBtn);
        }
      }

      var nChanges = $(".addedSong").length;
      if(nChanges <= 1) {
        // non-standard parameter to bypass the cache
        window.location.reload(true);
      } else {
        rootElem.remove();  
      } 
    })
    .catch(response => {
      console.log(response);

      // non-standard parameter to bypass the cache
      window.location.reload(true);
    });
}