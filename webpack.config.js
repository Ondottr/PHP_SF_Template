'use strict';

const path = require( 'path' );
const Dotenv = require( 'dotenv-webpack' );
const TerserPlugin = require("terser-webpack-plugin");

module.exports = ( env ) => {
  return {
    mode: env.mode,
    entry: './src/index.js',
    cache: Boolean( env.cache ),
    devtool: ( env.mode === 'development' ) ? 'inline-source-map' : "source-map",

    performance: {
      hints: ( env.mode === 'development' ) ? "warning" : false
    },

    optimization: {
      minimize: true,
      minimizer: [new TerserPlugin({
        parallel: true,
        terserOptions: {
          format: {
            comments: false,
          },
        },
        extractComments: false,
      })],
    },

    output: {
      filename: 'app.js',
      path: path.resolve( __dirname, 'public/js' ),
      library: 'app',
    },

    watchOptions: {
      aggregateTimeout: 100,
    },

    plugins: [
      new Dotenv( {
        // load this now instead of the ones in '.env'
        path: './.env',

        // load '.env.example' to verify the '.env' variables are all set. Can also be a string to a different file.
        safe: true,

        // allow empty variables (e.g. `FOO=`) (treat it as empty string, rather than missing)
        allowEmptyValues: true,

        // load all the predefined 'process.env' variables which will trump anything local per dotenv specs.
        systemvars: true,
      } ),
    ],

    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            // Creates `style` nodes from JS strings
            'style-loader',
            // Translates CSS into CommonJS
            'css-loader',
            // Compiles Sass to CSS
            'sass-loader',
          ],
        },
      ],
    },
  };
};
