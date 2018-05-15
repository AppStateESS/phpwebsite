var webpack = require('webpack')
var Promise = require('es6-promise').polyfill()
const path = require('path');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const sourceDir = path.resolve(__dirname, 'js')
const destDir = path.resolve(__dirname, 'dist')
const sourceSassDir = path.resolve(__dirname, 'scss')

module.exports = {
  entry: {
    'js/bundle.js': sourceDir + '/index.js',
    'css/custom.css': sourceSassDir + '/main.scss',
  },
  output: {
    path: destDir,
    filename: "[name]",
  },
  resolve: {
    extensions: ['.js', '.jsx',]
  },
  plugins: [
    new ExtractTextPlugin('css/custom.css', {allChunks: true}),
    new webpack.ProvidePlugin({$: "jquery", jQuery: "jquery"}),
  ],
  externals: {
    $: 'jQuery',
    EntryForm: 'EntryForm',
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: [
            {
              loader: 'css-loader',
              options: {
                sourceMap: true
              }
            }, {
              loader: 'sass-loader',
              options: {
                sourceMap: true
              }
            },
          ],
        }),
      }, {
        test: /\.(png|woff|woff2|eot|ttf|svg)$/,
        exclude: '/node_modules/',
        loader: 'url-loader?limit=100000',
      }, {
        test: /\.jsx?$/,
        enforce: 'pre',
        loader: 'jshint-loader',
        exclude: '/node_modules/',
        include: destDir,
      }, {
        test: /\.jsx?/,
        include: sourceDir,
        loader: 'babel-loader',
        query: {
          presets: ['env', 'react',]
        },
      },
    ]
  },
  devtool: 'source-map'
}
