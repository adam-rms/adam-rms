const defaultTheme = require('tailwindcss/defaultTheme')
const windmill = require('@windmill/react-ui/config')

module.exports = windmill({
  purge: [
    './src/**/*.html',
    './src/**/*.{js,jsx,ts,tsx}'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
      },
      boxShadow: {
        bottom: '0 5px 6px -7px rgba(0, 0, 0, 0.6), 0 2px 4px -5px rgba(0, 0, 0, 0.06)',
      },
    },
    screens: {
      'sm': '576px',
      'md': '768px',
      'lg': '992px',
      'xl': '1200px',
    },
    colors: {
      primary: {
        default: 'var(--ion-color-primary)',
        contrast: 'var(--ion-color-primary-contrast)',
        shade: 'var(--ion-color-primary-shade)',
        tint: 'var(--ion-color-primary-tint)',
      },
      secondary: {
        default: 'var(--ion-color-secondary)',
        contrast: 'var(--ion-color-secondary-contrast)',
        shade: 'var(--ion-color-secondary-shade)',
        tint: 'var(--ion-color-secondary-tint)',
      },
      tertiary: {
        default: 'var(--ion-color-tertiary)',
        contrast: 'var(--ion-color-tertiary-contrast)',
        shade: 'var(--ion-color-tertiary-shade)',
        tint: 'var(--ion-color-tertiary-tint)',
      },
      light: {
        default: 'var(--ion-color-light)',
        contrast: 'var(--ion-color-light-contrast)',
        shade: 'var(--ion-color-light-shade)',
        tint: 'var(--ion-color-light-tint)',
      },
      medium: {
        default: 'var(--ion-color-medium)',
        contrast: 'var(--ion-color-medium-contrast)',
        shade: 'var(--ion-color-medium-shade)',
        tint: 'var(--ion-color-medium-tint)',
      },
      dark: {
        default: 'var(--ion-color-dark)',
        contrast: 'var(--ion-color-dark-contrast)',
        shade: 'var(--ion-color-dark-shade)',
        tint: 'var(--ion-color-dark-tint)',
      },
      success: {
        default: 'var(--ion-color-success)',
        contrast: 'var(--ion-color-success-contrast)',
        shade: 'var(--ion-color-success-shade)',
        tint: 'var(--ion-color-success-tint)',
      },
      warning: {
        default: 'var(--ion-color-warning)',
        contrast: 'var(--ion-color-warning-contrast)',
        shade: 'var(--ion-color-warning-shade)',
        tint: 'var(--ion-color-warning-tint)',
      },
      danger: {
        default: 'var(--ion-color-danger)',
        contrast: 'var(--ion-color-danger-contrast)',
        shade: 'var(--ion-color-danger-shade)',
        tint: 'var(--ion-color-danger-tint)',
      },
      step: {
        '50': 'var(--ion-color-step-50)',
        '100': 'var(--ion-color-step-100)',
        '150': 'var(--ion-color-step-150)',
        '200': 'var(--ion-color-step-200)',
        '250': 'var(--ion-color-step-250)',
        '300': 'var(--ion-color-step-300)',
        '350': 'var(--ion-color-step-350)',
        '400': 'var(--ion-color-step-400)',
        '450': 'var(--ion-color-step-450)',
        '500': 'var(--ion-color-step-500)',
        '550': 'var(--ion-color-step-550)',
        '600': 'var(--ion-color-step-600)',
        '650': 'var(--ion-color-step-650)',
        '700': 'var(--ion-color-step-700)',
        '750': 'var(--ion-color-step-750)',
        '800': 'var(--ion-color-step-800)',
        '850': 'var(--ion-color-step-850)',
        '900': 'var(--ion-color-step-900)',
        '950': 'var(--ion-color-step-950)',
      },
    },
  }
})