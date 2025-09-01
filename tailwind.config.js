/** @type {import('tailwindcss').Config} */

// Impor tema default dari tailwind
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  content: [
    "./app/views/**/*.php"
  ],
  theme: {
    extend: {
      // Kita akan menimpa font-family default
      fontFamily: {
        // Ganti 'Roboto' menjadi 'Poppins'
        sans: ['Poppins', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [],
}