import { defineConfig } from 'vite';
import { readdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Collect multi-entry map: src/css|js/** → keys without extension (stable dist names).
 */
function walk(dir, acc = []) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(full, acc);
    }
    else {
      acc.push(full);
    }
  }
  return acc;
}

function buildInput() {
  const input = {};
  const srcRoot = path.join(__dirname, 'src');
  for (const file of walk(srcRoot)) {
    if (!/\.(css|scss|js)$/i.test(file)) {
      continue;
    }
    const rel = path.relative(srcRoot, file).replace(/\\/g, '/');
    const name = rel.replace(/\.(css|scss|js)$/i, '');
    input[name] = file;
  }
  return input;
}

/**
 * Drop empty JS stubs Vite emits for CSS-only entries.
 */
function removeCssEntryStubs() {
  return {
    name: 'remove-css-entry-stubs',
    generateBundle(_options, bundle) {
      for (const [fileName, chunk] of Object.entries(bundle)) {
        if (chunk.type !== 'chunk' || !chunk.isEntry) {
          continue;
        }
        const id = chunk.facadeModuleId || '';
        if (/\.(css|scss)$/i.test(id)) {
          delete bundle[fileName];
        }
      }
    },
  };
}

export default defineConfig(({ mode }) => {
  const isProd = mode === 'production';

  return {
    root: __dirname,
    publicDir: false,
    plugins: [removeCssEntryStubs()],
    build: {
      outDir: 'dist',
      emptyOutDir: true,
      manifest: false,
      sourcemap: !isProd,
      minify: isProd ? 'esbuild' : false,
      cssMinify: isProd,
      cssCodeSplit: true,
      rollupOptions: {
        input: buildInput(),
        output: {
          // Preserve src-relative names: js/global.js, css/components/hero-search.css
          entryFileNames: '[name].js',
          chunkFileNames: 'chunks/[name]-[hash].js',
          assetFileNames: (assetInfo) => {
            const originals = assetInfo.originalFileNames || [];
            for (const original of originals) {
              const norm = original.replace(/\\/g, '/');
              const match = norm.match(/src\/((?:css|js)\/.+)\.(css|scss)$/i);
              if (match) {
                return `${match[1]}.css`;
              }
            }
            const name = (assetInfo.names && assetInfo.names[0]) || assetInfo.name || 'asset';
            if (String(name).endsWith('.css')) {
              return `css/${name}`;
            }
            return `assets/${name}`;
          },
        },
      },
    },
  };
});
