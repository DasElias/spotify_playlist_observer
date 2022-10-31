import {LitElement, html} from 'lit';
import {customElement, property} from 'lit/decorators.js';

@customElement('playlist-element')
class PlaylistElement extends LitElement {
  @property({attribute: 'playlist', type: Object})
  playlist = null;

  createRenderRoot() {
    return this; // turn off shadow dom to access external styles
  }


  render() {
    return html`
      <div class="flex p-1 h-10">
        <div class="h-full">
          <div class="h-full" style="aspect-ratio: 1">
            <playlist-image .images=${this.playlist.images} targetSize="80"></playlist-image>  
          </div>
        </div>
        <div class="px-2 sm:px-3 pr-6 sm:pr-6 min-w-0 self-center">
          <div class="block truncate-ellipsis">${this.playlist.name}</div>
        </div>
      </div>



`;
  }
}