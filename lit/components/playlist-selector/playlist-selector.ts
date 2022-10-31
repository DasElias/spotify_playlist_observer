import {LitElement, html} from 'lit';
import {customElement, property, state} from 'lit/decorators.js';
import { styleMap } from 'lit-html/directives/style-map';

@customElement('playlist-selector')
class PlaylistSelector extends LitElement {
  @property({attribute: 'playlist-data', type: Array})
  playlistData = []

  @property({attribute: 'form-name', type: String})
  formName = null;
  
  @state()
  protected _isButtonToggled = false;

  @state()
  protected _selectedElem = null;

  @state()
  protected _handleWindowClickRef = this._handleWindowClick.bind(this);

  @state()
  protected _handleWindowKeydownRef = this._handleWindowKeydown.bind(this);

  createRenderRoot() {
    return this; // turn off shadow dom to access external styles
  }

  disconnectedCallback() {
    this._removeWindowHandlers();
    super.disconnectedCallback();
  }
  
  render() {
      console.log(this.playlistData)

      return html`
        <div>
          <div class="relative inline-block w-full text-black">
            <div class="selector-picker relative">
              <button type="button" class="w-full rounded border shadow bg-white" value="" @click="${e => this._handleButtonClick(e)}" @keypress="${e => this._handleButtonClick(e)}">
                ${this._renderButtonElement()}
                <i class="bi bi-caret-down-fill absolute top-1/2 right-2" style="transform: translate(0, -50%)"></i>
              </button>
              <div style=${styleMap({display: this._isButtonToggled ? 'block' : 'none'})}>
                <ul class="absolute w-full z-10 bg-white rounded shadow h-32 overflow-auto">
                  ${this.playlistData.map((playlist) => { 
                    return html`
                      <li @keypress="${(e) => this._selectNewElement(playlist, e)}" @click="${(e) => this._selectNewElement(playlist, e)}" class="cursor-pointer" tabindex="0">
                        <playlist-element .playlist=${playlist}></playlist-element>
                      </li>
                    `
                  })}
                </ul>
              </div>
            </div>
          </div>
          <input class="hidden" type="hidden" id="${this.formName}" name="${this.formName}" value="${this._selectedElem?.external_urls?.spotify}" />
          <p class="validation-error">Kein gültiger Playlistlink!</p>
        </div>        
      `;
    }

    _renderButtonElement() {
      if(this._selectedElem) {
        return html`<playlist-element .playlist=${this._selectedElem}></playlist-element>`
      } else {
       return html`<playlist-element .playlist=${{name: "Kein Element vorhanden"}}></playlist-element>`

      }
    }

    _addWindowHandlers() {
      window.addEventListener('click', this._handleWindowClickRef);
      window.addEventListener('keydown', this._handleWindowKeydownRef);
    }

    _removeWindowHandlers() {
      window.removeEventListener('click', this._handleWindowClickRef);
      window.removeEventListener('keydown', this._handleWindowKeydownRef);
    }

    _handleWindowClick() {
      if(this._isButtonToggled) {
        this._isButtonToggled = false;
        this._removeWindowHandlers();
      }
    }

    _handleWindowKeydown(event) {
      if(this._isButtonToggled) {
        var code = event.charCode || event.keyCode;
        if(code === 32 || code === 13 || code === 27){
          this._isButtonToggled = false;
          this._removeWindowHandlers();
        }
      }
    }

    _handleButtonClick(event) {
      if(! this._isButtonToggled && this._isClickEvent(event)) {
        this._isButtonToggled = true;
        setTimeout(() => this._addWindowHandlers(), 0);
      }
    }

    _selectNewElement(playlist, event) {
      console.log("select new elem");
      if(this._isClickEvent(event)) {
        this._selectedElem = playlist;
      }
    }

    _isClickEvent(event) {
      if(event.type === 'click'){
        return true;
      }
      else if(event.type === 'keypress'){
          var code = event.charCode || event.keyCode;
          if((code === 32)|| (code === 13)){
              return true;
          }
      }
      else{
          return false;
      }
    }
}