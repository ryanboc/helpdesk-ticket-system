import preset from './vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset], // <--- THIS IS THE MAGIC LINE YOU WERE MISSING
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        {
            pattern: /(bg|text|border)-(gray|blue|green|purple|red|orange|yellow)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
    ],
}