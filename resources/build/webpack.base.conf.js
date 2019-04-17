const { resolve } = require('path')
const webpack = require('webpack')
const ExtractTextPlugin = require('extract-text-webpack-plugin')

const appEntry = './resources/src/main.js'
const distPath = resolve(__dirname, '../../webroot/dist')

module.exports = {
  devtool: '#eval-source-map',
  entry: {
    app: appEntry,
    vendor: ['vue']
  },
  output: {
    path: distPath,
    filename: '[name].js'
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          extractCSS: true
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]?[hash]'
        }
      },
      {
        test: /\.sass$/,
        use: [
          'vue-style-loader',
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              indentedSyntax: true
            }
          }
        ]
      },
      {
       test: /\.css$/,
       loader: ExtractTextPlugin.extract({
         use: 'css-loader',
         fallback: 'vue-style-loader'
       })
     },
    ]
  },
  plugins: [
     new ExtractTextPlugin("style.css")
  ],
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@': resolve('./resources/src/'),
    }
  }
}
