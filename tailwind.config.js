const colors = require('tailwindcss/colors')
const postcss = require('postcss')
const selectorParser = require('postcss-selector-parser')
const fractionWidths = require("tailwindcss-fraction-widths");

module.exports = {
  theme: {
    colors: {
      'transparent': 'transparent',
      'lightgreen': '#1ED760',
      'green': '#1DB954',
      'black': '#121212',
      'white': '#ffffff',
      'grey': '#b3b3b3',
      'darkgrey': '#2d3748',
      'darkgreen': '#3E6472',
      'magenta': '#A82690',
      'red': '#DF0000',
      'lightred': '#ff0000',
      'dropdown-grey': '#282828',
      'dropdown-grey-hover': '#3E3E3E',
      'dropdown-white': '#EAEAEA',
      'music-grey': '#404040'
    },
    fontFamily: {
      sans: ['Lato', 'Arial', 'sans-serif']
    },
    fontSize: {
      xs: ['12px', '16px'],
      sm: ['14px', '16px'],
      base: ['16px', '24px'],
      lg: ['20px', '28px'],
      xl: ['24px', '32px'],
      '2xl': ['28px', '32px'],
    },
    boxShadow: {
      '3xl': '0 35px 60px -15px rgba(0, 0, 0, 0.3)',
    },
    extend: {
      spacing: {
        '120': '30rem',
      }
    },
  },
  variants: {

    extend: {
      backgroundColor: ["hover-hover", "group-hover-hover"],
      textColor: ["hover-hover", "group-hover-hover"]
    }
  },
  plugins: [
    function({ addComponents }) {
      addComponents({
        '.smallcontainer': {
          maxWidth: 'calc(100% - 40px)',
          '@screen sm': {
            maxWidth: '600px',
          },
          '@screen md': {
            maxWidth: '700px',
          },
          '@screen lg': {
            maxWidth: '800px',
          },
          '@screen xl': {
            maxWidth: '900px',
          },
        }
      })
    },
    function({ addVariant, e }) {
      // hover is enabled and element is hovered
      addVariant('hover-hover', ({ container, separator }) => {
        const hoverHover = postcss.atRule({ name: 'media', params: '(hover: hover)' })
        hoverHover.append(container.nodes)
        container.append(hoverHover)
        hoverHover.walkRules(rule => {
          rule.selector = `.${e(`hover-hover${separator}${rule.selector.slice(1)}`)}:hover`
        })

        
      })
      addVariant('group-hover-hover', ({ container, separator }) => {
        const hoverHover = postcss.atRule({ name: 'media', params: '(hover: hover)' })
        hoverHover.append(container.nodes)
        container.append(hoverHover)
        hoverHover.walkRules(rule => {
          rule.selector = `.group:hover .${e(`group-hover-hover${separator}${rule.selector.slice(1)}`)}`
        })

        
      })
    },
    fractionWidths(8, 10)
  ],
}
