function validateForm() {
  var isValid = true;
  var source = $("#sourcePlaylist");
  var dest = $("#destPlaylist");
  source.removeClass("invalid");
  dest.removeClass("invalid");

  if(! validatePlaylistLink(source.val())) {
    source.addClass("invalid");
    isValid = false;
  }

  if(! validatePlaylistLink(dest.val())) {
    dest.addClass("invalid");
    isValid = false;
  }

  return isValid;

}

function validatePlaylistLink(input) {
  const isValidLink = /^(https:\/\/open\.spotify\.com\/playlist\/.*\?si=.*)$/.test(input);
  const isValidUri = /^(spotify:playlist:.*)$/.test(input);
  return isValidLink || isValidUri;
}
