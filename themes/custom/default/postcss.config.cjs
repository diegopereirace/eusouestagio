/** PostCSS — cssnano only in production builds (Vite sets NODE_ENV). */
module.exports = ({ env }) => ({
  plugins: {
    ...(env === 'production' ? { cssnano: { preset: 'default' } } : {}),
  },
});
