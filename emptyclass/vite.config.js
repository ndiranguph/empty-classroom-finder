import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:80',     // your PHP server
        changeOrigin: true,             // ensures correct request headers
        rewrite: (path) => path.replace(/^\/api/, 'php-backend/emptyclassback'), 
        secure: false                   // helps avoid HTTPS issues if any
      },
    },
  },
})
