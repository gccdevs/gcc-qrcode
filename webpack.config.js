const path = require( 'path' );
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const CleanObsoleteChunks = require( 'webpack-clean-obsolete-chunks' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );

module.exports = async( env, argv ) => {
  const devMode = ( 'production' !== argv.mode );

  //COMMON

  let config = {
    entry: {
      "qr-code-main-script": './js/main.js',
      "qr-code-setting-script": './js/setting.js',
      "qr-code-main-style": './sass/main.scss',
    },
    output: {
      path:             path.resolve( __dirname, 'dist' ),
      hashDigestLength: 12,
    },
    module: {
      rules: [
        {
          test: /\.scss$/,
          use:  [
            MiniCssExtractPlugin.loader,
            {
              loader:  'css-loader',
              options: {
                url:       false,
                sourceMap: true,
              },
            },
            {
              loader:  'postcss-loader',
              options: { sourceMap: true },
            },
            {
              loader:  'sass-loader',
              options: { sourceMap: true },
            },
          ],
        },
        {
          test:    /\.js$/,
          exclude: /(node_modules|bower_components)/,
          use:     {
            loader:  'babel-loader',
            options: {
              presets: [ '@babel/preset-env' ]
            }
          }
        }
      ]
    },
    plugins: [
      new FixStyleOnlyEntriesPlugin(),
      new CleanWebpackPlugin([ 'dist/**/*' ]),
      new CleanObsoleteChunks(),
    ]
  };

  //DEVELOPMENT

  if ( devMode ) {
    config.devtool = 'source-map';
    config.output.filename = 'js/[name].h.[contenthash].js';

    const devPlugins = [ new MiniCssExtractPlugin({
      filename: 'css/[name].h.[contenthash].css'
    }) ];

    config.plugins = [
      ...config.plugins,
      ...devPlugins,
    ];

    //PRODUCTION

  } else {
    config.output.filename = 'js/[name].h.[contenthash].min.js';

    //Plugins
    const prodPlugins = [ new MiniCssExtractPlugin({
      filename: 'css/[name].h.[contenthash].min.css'
    }) ];

    config.plugins = [
      ...config.plugins,
      ...prodPlugins
    ];

    //Optimize
    config.optimization = {
      minimizer: [
        new UglifyJsPlugin(),
        new OptimizeCSSAssetsPlugin({}),
      ]
    };
  }

  return config;
};
