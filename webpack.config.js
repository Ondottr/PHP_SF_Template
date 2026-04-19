"use strict";

const path = require( "path" );
const Dotenv = require( "dotenv-webpack" );
const TerserPlugin = require( "terser-webpack-plugin" );
const JavaScriptObfuscator = require( "webpack-obfuscator" );
const { CleanWebpackPlugin } = require( "clean-webpack-plugin" );
const { WebpackManifestPlugin } = require( "webpack-manifest-plugin" );
const MiniCssExtractPlugin = require( "mini-css-extract-plugin" );
const { PurgeCSSPlugin } = require( "purgecss-webpack-plugin" );
const glob = require( "glob" );
const dotenv = require( "dotenv" );

module.exports = ( env ) => {
  dotenv.config();
  const isProduction = env.mode === "production";

  return {
    mode: env.mode,
    entry: { app: "./src/index.js" },
    cache: Boolean( env.cache ),
    devtool: isProduction ? false : "inline-source-map",
    context: path.resolve( __dirname ),

    performance: {
      hints: isProduction ? "warning" : false,
    },

    optimization: {
      minimize: isProduction,
      splitChunks: {
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/]/,
            name: "vendor",
            chunks: "all",
          },
        },
      },
      minimizer: [
        isProduction &&
        new TerserPlugin( {
          parallel: true,
          terserOptions: {
            format: {
              comments: false,
            },
            compress: {
              drop_console: true,
            },
          },
          extractComments: false,
        } ),
      ].filter( Boolean ),
    },

    output: {
      filename: isProduction ? `[name].[contenthash].js` : "[name].js",
      path: path.resolve( __dirname, "public/build" ),
      library: "app",
    },

    watchOptions: {
      aggregateTimeout: 100,
      ignored: [ "**/node_modules/**", "**/.git/**", "**/public/build/**" ],
    },

    plugins: [
      new MiniCssExtractPlugin( {
        filename: isProduction ? "[name].[contenthash].css" : "[name].css",
      } ),
      new CleanWebpackPlugin( {
        cleanStaleWebpackAssets: false,
        cleanOnceBeforeBuildPatterns: [
          "**/*",
          "!bootstrap.min.js",
          "!jquery-3.6.0.min.js",
        ],
      } ),
      isProduction && new PurgeCSSPlugin( {
        paths: glob.sync(
          [
            `${__dirname}/templates/**/*.php`,
            `${__dirname}/templates_twig/**/*.twig`,
            `${__dirname}/src/**/*.js`,
          ],
          { nodir: true }
        ),
        safelist: {
          standard: [ "show", "fade", "collapsing", "modal-backdrop", "offcanvas-backdrop" ],
          greedy: [ /^bs-/, /^data-bs-/ ],
        },
      } ),
      isProduction && new WebpackManifestPlugin( { publicPath: "/build/" } ),
      isProduction && new JavaScriptObfuscator( {
        domainLock: [
          "localhost",
          "127.0.0.1:7000",
          "nations-original.com",
        ],
        debugProtection: true,
        debugProtectionInterval: 4000,
        disableConsoleOutput: true,
        compact: true,
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.75,
        deadCodeInjection: true,
        deadCodeInjectionThreshold: 0.4,
        identifierNamesGenerator: "hexadecimal",
        log: false,
        numbersToExpressions: true,
        renameGlobals: false,
        selfDefending: true,
        simplify: true,
        splitStrings: true,
        splitStringsChunkLength: 5,
        stringArray: true,
        stringArrayCallsTransform: true,
        stringArrayCallsTransformThreshold: 0.75,
        stringArrayEncoding: [ "rc4" ],
        stringArrayIndexShift: true,
        stringArrayRotate: true,
        stringArrayShuffle: true,
        stringArrayWrappersCount: 2,
        stringArrayWrappersChainedCalls: true,
        stringArrayWrappersParametersMaxCount: 4,
        stringArrayWrappersType: "function",
        stringArrayThreshold: 0.75,
        unicodeEscapeSequence: false,
      }, [ "vendor.*.js" ] ),
    ].filter( Boolean ),

    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            "css-loader",
            {
              loader: "sass-loader",
              options: {
                sassOptions: {
                  includePaths: [ path.resolve( __dirname, "node_modules" ) ],
                },
              },
            },
          ],
        },
      ],
    },
    resolve: {
      extensions: [ ".js" ],
    },
  };
};
