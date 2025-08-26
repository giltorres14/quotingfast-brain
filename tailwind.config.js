/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './brain/resources/views/**/*.blade.php',
    './brain/resources/views/**/*.php',
    './brain/public/**/*.php',
    './resources/views/**/*.blade.php'
  ],
  theme: {
    extend: {}
  },
  plugins: [
    require('@tailwindcss/forms')
  ]
};






