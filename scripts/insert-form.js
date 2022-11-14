function onToggleSource() {
  var source = getSelectedSource();
  setVisibility($("#container_source_playlist"), source == "playlist");
  setVisibility($("#container_source_user"), source == "user");
}

function setVisibility(elem, isVisible) {
  if(isVisible) elem.show();
  else elem.hide();
}

function validateInsertionForm() {
  var isValid = true;
  var source = getSelectedSource();
  var sourcePlaylist = $("#sourcePlaylist");
  var sourceUser = $("#sourceUser");
  var dest = $("#destPlaylist");
  sourcePlaylist.removeClass("invalid");
  sourceUser.removeClass("invalid");
  dest.removeClass("invalid");

  if(source == "playlist") {
    if(! validatePlaylistLink(sourcePlaylist.val())) {
      sourcePlaylist.addClass("invalid");
      isValid = false;
    }
  } else if (source == "user") {
    if(! validateUserLink(sourceUser.val())) {
      sourceUser.addClass("invalid");
      isValid = false;
    }
  }

  if(! validatePlaylistLink(dest.val())) {
    dest.addClass("invalid");
    isValid = false;
  }

  return isValid;

}

function validatePlaylistLink(input) {
  const isValidLink = /^(https:\/\/open\.spotify\.com\/playlist\/.*)$/.test(input);
  return isValidLink;
}

function validateUserLink(input) {
  const isValidLink = /^(https:\/\/open\.spotify\.com\/user\/.*)$/.test(input);
  return isValidLink;
}

function getSelectedSource() {
  var selector = document.querySelector('input[name="source"]:checked'); 
  if(selector) return selector.value;
  else return null;
}