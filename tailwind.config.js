/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        'empire-primary': '#1a2a6c',
        'empire-secondary': '#c9a84c',
        'empire-accent': '#e8d5a3',
        'empire-background': '#f8f7f4',
        'empire-dark': '#0f1a3a',
      },
    },
  },
  plugins: [
    require('flowbite/plugin'),
  ],
}
