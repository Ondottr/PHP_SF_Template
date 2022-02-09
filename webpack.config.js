const path = require('path');
const Dotenv = require('dotenv-webpack');

module.exports = {
  entry: './src/index.js',
  output: {
    filename: 'app.js',
    path: path.resolve(__dirname, 'public/js'),
    library: 'app',
  },
  watch: true,
  watchOptions: {
    aggregateTimeout: 100,
  },
  plugins: [
    new Dotenv({
      // load this now instead of the ones in '.env'
      path: './.env',

      // load '.env.example' to verify the '.env' variables are all set. Can also be a string to a different file.
      safe: true,

      // allow empty variables (e.g. `FOO=`) (treat it as empty string, rather than missing)
      allowEmptyValues: true,

      // load all the predefined 'process.env' variables which will trump anything local per dotenv specs.
      systemvars: true,
    }),
  ],
};
