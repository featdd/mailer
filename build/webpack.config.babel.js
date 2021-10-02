import path from 'path';
import MiniCssExtractPlugin from 'mini-css-extract-plugin'

module.exports = {
  mode: process.env.NODE_ENV,
  entry: [
    path.resolve(__dirname, './../Resources/Private/Assets/JavaScript/Mailer.js'),
    path.resolve(__dirname, './../Resources/Private/Assets/Scss/Mailer.scss')
  ],
  output: {
    path: path.resolve(__dirname, './../Resources/Public'),
    filename: './JavaScript/Mailer.js',
    chunkFilename: './JavaScript/Chunks/[name].js'
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: './Css/Mailer.css'
    })
  ],
  module: {
    rules: [
      {
        test: /\.s[ac]ss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              sourceMap: true,
              importLoaders: 2
            }
          },
          {
            loader: "sass-loader",
            options: {
              sourceMap: true
            }
          },
        ],
      }
    ]
  }
};

if ('development' === process.env.NODE_ENV) {
  module.exports['devtool'] = 'source-map';
  module.exports['watch'] = true;
}
