import {LitElement, html} from 'lit';
import {customElement, property} from 'lit/decorators.js';

@customElement('playlist-image')
class PlaylistImage extends LitElement {
  @property({type: Object})
  images = null;

  @property({type: Number})
  targetSize = 0;

  createRenderRoot() {
    return this; // turn off shadow dom to access external styles
  }

  getUrl() {
    var imagesWithDistance = this.images ? [
        ...this.images
    ] : [];

    for(var i of imagesWithDistance) {
        // delta will be negative, when the actual image is smaller than the desired size
        i.delta = i.width - this.targetSize;
    }


    // helper function to retrieve the weighted delta of an image
    var getDelta = (img) => {
        var delta = img.delta;
        
        // we prefer too big images over too small images
        const smallerPenalty = 1000;

        return delta + (delta < 0 ? smallerPenalty : 0);
    }

    imagesWithDistance.sort((a, b) => getDelta(a) - getDelta(b))
    
    if(imagesWithDistance.length) return imagesWithDistance[0].url;
    return null;
  }


  render() {
    let url = this.getUrl();
    if(url) {
        return html`
            <img aria-hidden="false" draggable="false" loading="eager" src="${url}" class="w-full h-full rounded-sm">
        `
    } else {
        return html`
            <div class="bg-grey flex items-center justify-center w-full h-full text-darkgrey-100 text-xl rounded-sm">
                <i class="bi bi-music-note-beamed"></i>
            </div>
        `
    }
  }
}