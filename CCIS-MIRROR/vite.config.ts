import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),
    vue(),
  ],
  server: {
    host: '0.0.0.0', 
    port: 5173,
    hmr: {
      // Update this to match your current network IPasdasda
      host: '192.168.200.112', 
      port: 5173,
    },
  },
})