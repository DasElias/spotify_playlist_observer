const colors = require('tailwindcss/colors')

module.exports = {
  theme: {
    colors: {
        'green': '#1ED760',
        'black': '#121212',
        'white': '#ffffff',
        'grey': '#b3b3b3',
        'darkgreen': '#3E6472',
        'magenta': '#A82690'
    },
    fontFamily: {
      sans: ['Lato', 'Arial', 'sans-serif']
    },
    fontSize: {
      sm: ['14px', '20px'],
      base: ['16px', '24px'],
      lg: ['20px', '28px'],
      xl: ['24px', '32px'],
      '2xl': ['28px', '32px']
    },
    extend: {
      
    },
  },
  variants: {},
  plugins: [
    function ({ addComponents }) {
      addComponents({
        '.smallcontainer': {
          maxWidth: 'calc(100% - 50px)',
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
    }
  ],
}
