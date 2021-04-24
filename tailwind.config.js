const colors = require('tailwindcss/colors')
const postcss = require('postcss')
const selectorParser = require('postcss-selector-parser')
const fractionWidths = require("tailwindcss-fraction-widths");

module.exports = {
  theme: {
    colors: {
      'transparent': 'transparent',
      'black': '#121212',
      'white': {
        'DEFAULT': '#ffffff',
        'beige': '#EAEAEA',
      },
      'grey': {
        'DEFAULT': '#B3B3B3',
      },
      'darkgrey': {
        '100': '#404040',
        '200': '#3E3E3E',
        '300': '#333333',
        '400': '#282828'
      },
      'darkblue': {
        'DEFAULT': '#2d3748',
      },
      'green': {
        'light': '#1ED760',
        'DEFAULT': '#1DB954',
      },
      'red': {
        'light': 'ff0000',
        'DEFAULT': '#DF0000',
      },
      'magenta': {
        'DEFAULT': '#A82690'
      }
    },
    fontFamily: {
      sans: ['Lato', 'Arial', 'sans-serif']
    },
    fontSize: {
      xs: ['12px', '16px'],
      sm: ['14px', '16px'],
      base: ['16px', '24px'],
      md: ['18px', '26px'],
      lg: ['20px', '28px'],
      xl: ['24px', '32px'],
      '2xl': ['28px', '32px'],
    },
    boxShadow: {
      '3xl': '0 35px 60px -15px rgba(0, 0, 0, 0.3)',
    },
    extend: {
      minWidth: {
        '0.5': '0.125rem',
        '1': '0.25rem',
        '1.5': '0.375rem',
        '2': '0.5rem',
        '2.5': '0.625rem',
        '3': '0.75rem',
        '3.5': '0.875rem',
        '4': '1rem',
        '5': '1.25rem',
        '6': '1.5rem',
        '8': '2rem',
        '10': '2.5rem',
        '12': '3rem',
        '13': '3.25rem',
        '14': '3.5rem',
        '15': '3.75rem',
        '16': '4rem',
        '20': '5rem',
        '24': '6rem',
        '32': '8rem',
        '40': '10rem',
        '48': '12rem',
        '56': '14rem',
        '64': '16rem',
      },
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
