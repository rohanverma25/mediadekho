import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  // The site is deployed under a subfolder on shared hosting
  // (https://www.codebasecoders.com/mediadekho/, per VITE_API_BASE_URL in
  // .env), not the domain root. Without this, built asset paths are
  // absolute from the domain root (/assets/xxx.js instead of
  // /mediadekho/assets/xxx.js), the browser requests the wrong URL, and
  // Hostinger's fallback returns an HTML page where a JS module was
  // expected — the exact "MIME type text/html" error.
  base: '/mediadekho/',
  plugins: [react(), tailwindcss()],
})
