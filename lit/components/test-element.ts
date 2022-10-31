import {LitElement, html} from 'lit';
import {customElement} from 'lit/decorators.js';

@customElement('test-element')
class TestElement extends LitElement {
  render() {
    return html`
      <div>Test</div>



`;
  }
}